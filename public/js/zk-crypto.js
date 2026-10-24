/**
 * Zero-Knowledge Client-Side Crypto Engine
 * Powered by Web Crypto API (AES-GCM 256-bit)
 */
class ZKCrypto {
    // Derive a secure encryption key from user passphrase and salt
    static async deriveKey(passphrase, salt) {
        const enc = new TextEncoder();
        const keyMaterial = await window.crypto.subtle.importKey(
            "raw",
            enc.encode(passphrase),
            { name: "PBKDF2" },
            false,
            ["deriveKey"]
        );

        return await window.crypto.subtle.deriveKey(
            {
                name: "PBKDF2",
                salt: salt,
                iterations: 100000,
                hash: "SHA-256"
            },
            keyMaterial,
            { name: "AES-GCM", length: 256 },
            false,
            ["encrypt", "decrypt"]
        );
    }

    // Encrypt a file (ArrayBuffer) into ciphertext with an initialization vector (IV)
    static async encryptFile(fileBuffer, passphrase) {
        const salt = window.crypto.getRandomValues(new Uint8Array(16));
        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        const key = await this.deriveKey(passphrase, salt);

        const encryptedContent = await window.crypto.subtle.encrypt(
            { name: "AES-GCM", iv: iv },
            key,
            fileBuffer
        );

        // Package salt, iv, and encrypted ciphertext together into a single payload buffer
        const payload = new Uint8Array(salt.byteLength + iv.byteLength + encryptedContent.byteLength);
        payload.set(salt, 0);
        payload.set(iv, salt.byteLength);
        payload.set(new Uint8Array(encryptedContent), salt.byteLength + iv.byteLength);

        return payload.buffer;
    }

    // Decrypt ciphertext back into original file ArrayBuffer using the passphrase
    static async decryptFile(encryptedBuffer, passphrase) {
        const data = new Uint8Array(encryptedBuffer);
        const salt = data.slice(0, 16);
        const iv = data.slice(16, 28);
        const ciphertext = data.slice(28);

        const key = await this.deriveKey(passphrase, salt);

        return await window.crypto.subtle.decrypt(
            { name: "AES-GCM", iv: iv },
            key,
            ciphertext
        );
    }
}
window.ZKCrypto = ZKCrypto;
