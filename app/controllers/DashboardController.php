<?php

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;
use App\Core\Session;

class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::requireLogin();

        $name = Session::get('full_name');
        $roleId = Session::get('role_id');
        $userId = (int) Session::get('user_id');

        try {
            $db = Database::connect();

            // 1. Total clean encrypted files
            $stmt = $db->prepare("SELECT COUNT(*) FROM files WHERE user_id = :user_id AND malware_status = 'clean'");
            $stmt->execute([':user_id' => $userId]);
            $totalSecuredFiles = (int) $stmt->fetchColumn();

            // 2. Total quarantined/blocked files
            $stmt = $db->prepare("SELECT COUNT(*) FROM files WHERE user_id = :user_id AND malware_status != 'clean'");
            $stmt->execute([':user_id' => $userId]);
            $totalQuarantined = (int) $stmt->fetchColumn();

            // 3. Total storage volume formatted cleanly
            $stmt = $db->prepare("SELECT COALESCE(SUM(file_size), 0) FROM files WHERE user_id = :user_id AND encryption_status = 'encrypted'");
            $stmt->execute([':user_id' => $userId]);
            $totalBytes = (int) $stmt->fetchColumn();

            if ($totalBytes >= 1048576) {
                $storageFormatted = number_format($totalBytes / 1048576, 2) . ' MB';
            } elseif ($totalBytes >= 1024) {
                $storageFormatted = number_format($totalBytes / 1024, 2) . ' KB';
            } else {
                $storageFormatted = $totalBytes . ' B';
            }

            // 4. Five most recent audit log entries
            $stmt = $db->prepare(
                "SELECT action, result, details, created_at 
                 FROM audit_logs 
                 WHERE user_id = :user_id 
                 ORDER BY id DESC 
                 LIMIT 5"
            );
            $stmt->execute([':user_id' => $userId]);
            $recentAudits = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            $totalSecuredFiles = 0;
            $totalQuarantined = 0;
            $storageFormatted = '0 B';
            $recentAudits = [];
        }

        require __DIR__ . '/../views/dashboard/index.php';
    }

    public function adminPanel(): void
    {
        AuthMiddleware::requireAdmin();

        $name = Session::get('full_name');

        require __DIR__ . '/../views/dashboard/admin.php';
    }
}
