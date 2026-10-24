<?php

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\AuditLogger;
use App\Core\Database;
use App\Core\Session;
use App\Services\ThreatDetectionService;
use App\Services\EncryptionService;

class FileController
{
    public function upload(): void
    {
        AuthMiddleware::requireLogin();
        $userId = (int) Session::get('user_id');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require __DIR__ . '/../views/files/upload.php';
            return;
        }

        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            AuditLogger::log('UPLOAD_ATTEMPT', 'file', null, 'failure', 'File missing or PHP upload error code', $userId);
            header('Location: /adaptive-secure-file-vault/public/upload?error=upload_failed');
            exit;
        }

        $file = $_FILES['document'];

        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            AuditLogger::log('UPLOAD_ATTEMPT', 'file', null, 'blocked', 'File exceeded 10MB limit', $userId);
            header('Location: /adaptive-secure-file-vault/public/upload?error=size_limit');
            exit;
        }

        $allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            AuditLogger::log('UPLOAD_ATTEMPT', 'file', null, 'blocked', "Disallowed extension: .{$extension}", $userId);
            header('Location: /adaptive-secure-file-vault/public/upload?error=invalid_extension');
            exit;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'image/jpeg',
            'image/png'
        ];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            AuditLogger::log('UPLOAD_ATTEMPT', 'file', null, 'blocked', "MIME mismatch or disallowed: {$mimeType}", $userId);
            header('Location: /adaptive-secure-file-vault/public/upload?error=invalid_mime');
            exit;
        }

        $tempDir = dirname(__DIR__, 2) . '/storage/temp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $stagedPath = $tempDir . '/' . bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $stagedPath)) {
            AuditLogger::log('UPLOAD_ATTEMPT', 'file', null, 'failure', 'Failed moving uploaded file to staging', $userId);
            header('Location: /adaptive-secure-file-vault/public/upload?error=server_error');
            exit;
        }

        $threatService = new ThreatDetectionService();
        $inspection = $threatService->inspect($stagedPath, $userId);

        if (!$inspection['is_safe']) {
            $quarantineDir = dirname(__DIR__, 2) . '/storage/quarantine';
            if (!is_dir($quarantineDir)) {
                mkdir($quarantineDir, 0755, true);
            }

            $quarantineName = 'quarantine_' . bin2hex(random_bytes(16)) . '.' . $extension;
            rename($stagedPath, $quarantineDir . '/' . $quarantineName);

            if (class_exists('App\Services\AuditLogger')) {
                \App\Services\AuditLogger::log(
                    'SECURITY_THREAT_DETECTED', 
                    'file', 
                    null, 
                    'blocked', 
                    "Ingestion Rejected: " . ($inspection['details'] ?? 'Malware/Script detected')
                );
            }
            header('Location: /adaptive-secure-file-vault/public/upload?threat=isolated');
            exit;
        }

        $storageDir = dirname(__DIR__, 2) . '/storage/uploads';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $storedName = 'file_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $finalPath = $storageDir . '/' . $storedName;

        $passphrase = $_POST['encryption_passphrase'] ?? '';
        try {
            if (!empty($passphrase)) {
                $fileData = file_get_contents($stagedPath);
                $salt = random_bytes(16);
                $iv = random_bytes(16);
                $key = hash_pbkdf2('sha256', $passphrase, $salt, 100000, 32, true);
                $ciphertext = openssl_encrypt($fileData, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
                $encryptedPackage = $salt . $iv . $ciphertext;
                file_put_contents($finalPath, $encryptedPackage);
                @unlink($stagedPath);
                $encryptionStatus = 'encrypted';
            } else {
                rename($stagedPath, $finalPath);
                $encryptionStatus = 'plain';
            }

            $db = Database::connect();
            
            // 1. Insert into files table
            $stmt = $db->prepare(
                "INSERT INTO files 
                (user_id, original_name, stored_name, file_path, mime_type, file_extension, file_size, encryption_status, malware_status)
                VALUES 
                (:user_id, :original_name, :stored_name, :file_path, :mime_type, :file_extension, :file_size, :encryption_status, :malware_status)"
            );

            $stmt->execute([
                ':user_id'           => $userId,
                ':original_name'     => $originalName,
                ':stored_name'       => $storedName,
                ':file_path'         => $finalPath,
                ':mime_type'         => $mimeType,
                ':file_extension'    => $extension,
                ':file_size'         => $file['size'],
                ':encryption_status' => $encryptionStatus,
                ':malware_status'    => 'clean'
            ]);

            $fileId = (int) $db->lastInsertId();

            // 2. Generate and Insert SHA-256 Integrity Hash into file_integrity table
            $sha256Hash = hash_file('sha256', $finalPath);
            $integrityStmt = $db->prepare(
                "INSERT INTO file_integrity (file_id, sha256_hash) VALUES (:file_id, :sha256_hash)"
            );
            $integrityStmt->execute([
                ':file_id'     => $fileId,
                ':sha256_hash' => $sha256Hash
            ]);

            $threatService->recordScanResult($fileId, $inspection);

        } catch (\Throwable $e) {
            error_log('[Upload Error] ' . $e->getMessage());
            header('Location: /adaptive-secure-file-vault/public/upload?error=server_error');
            exit;
        }

        header('Location: /adaptive-secure-file-vault/public/files?success=uploaded');
        exit;
    }

    public function download(): void
    {
        AuthMiddleware::requireLogin();
        $userId = (int) Session::get('user_id');
        $fileId = (int) ($_GET['id'] ?? 0);

        if ($fileId <= 0) {
            http_response_code(400);
            die('Invalid file request.');
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare(
                "SELECT id, user_id, original_name, stored_name, file_path, mime_type, file_size, encryption_status 
                 FROM files 
                 WHERE id = :id AND user_id = :user_id"
            );
            $stmt->execute([':id' => $fileId, ':user_id' => $userId]);
            $file = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$file || !file_exists($file['file_path'])) {
                AuditLogger::log('FILE_DOWNLOAD', 'file', $fileId, 'failure', 'File missing or unauthorized access attempt', $userId);
                http_response_code(404);
                die('File not found or access denied.');
            }

            $outputData = file_get_contents($file['file_path']);
            $outputLength = filesize($file['file_path']);

            AuditLogger::log('FILE_DOWNLOAD', 'file', $fileId, 'success', "Downloaded encrypted package '{$file['original_name']}'", $userId);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . rawurlencode($file['original_name']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $outputLength);

            echo $outputData;
            exit;
        } catch (\Throwable $e) {
            error_log('[Download Error] ' . $e->getMessage());
            http_response_code(500);
            die('Error downloading file.');
        }
    }

    public function myFiles(): void
    {
        AuthMiddleware::requireLogin();
        $userId = (int) Session::get('user_id');

        try {
            $db = Database::connect();
            $stmt = $db->prepare(
                "SELECT
                    f.id,
                    f.original_name,
                    f.stored_name,
                    f.mime_type,
                    f.file_extension,
                    f.file_size,
                    f.encryption_status,
                    f.malware_status,
                    f.created_at,
                    fi.sha256_hash,
                    ms.scan_status,
                    ms.detection_name,
                    ms.scan_message
                FROM files f
                LEFT JOIN file_integrity fi ON f.id = fi.file_id
                LEFT JOIN malware_scans ms ON f.id = ms.file_id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC"
            );

            $stmt->execute([':user_id' => $userId]);
            $files = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $name = Session::get('full_name');
            $roleId = Session::get('role_id');

            require __DIR__ . '/../views/files/index.php';
        } catch (\PDOException $e) {
            AuditLogger::log('MY_FILES_ACCESS', 'files', null, 'failure', $e->getMessage(), $userId);
            die('Unable to load your files.');
        }
    }
}
