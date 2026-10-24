<?php

namespace App\Services;

use App\core\Config;

class EncryptionService
{
    private const CIPHER = 'aes-256-gcm';
    private const IV_LENGTH = 12;   // 96-bit standard for GCM
    private const TAG_LENGTH = 16;  // 128-bit authentication tag

    /**
     * Resolves the 256-bit binary encryption key from Config.
     */
    private static function getKey(): string
    {
        $hexKey = Config::get('VAULT_ENCRYPTION_KEY');
        if (empty($hexKey)) {
            throw new \RuntimeException('Encryption key VAULT_ENCRYPTION_KEY is not defined in config/.env');
        }
        return hex2bin($hexKey);
    }

    /**
     * Encrypts a file on disk in-place using AES-256-GCM.
     */
    public static function encryptFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $plaintext = file_get_contents($filePath);
        if ($plaintext === false) {
            return false;
        }

        $key = self::getKey();
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            return false;
        }

        // Pack: [12-byte IV] + [16-byte GCM Tag] + [Ciphertext]
        $payload = $iv . $tag . $ciphertext;

        return file_put_contents($filePath, $payload, LOCK_EX) !== false;
    }

    /**
     * Decrypts an AES-256-GCM encrypted file and returns the plaintext.
     */
    public static function decryptFile(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $data = file_get_contents($filePath);
        if ($data === false || strlen($data) < (self::IV_LENGTH + self::TAG_LENGTH)) {
            return null;
        }

        $key = self::getKey();
        $iv = substr($data, 0, self::IV_LENGTH);
        $tag = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($data, self::IV_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $ciphertext === false ? null : $plaintext;
    }
}
