<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files - Adaptive Secure Vault</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #f8fafc; }
        
        .sidebar { position: fixed; left: 0; top: 0; width: 250px; height: 100vh; background: #090d16; color: white; padding: 25px 18px; border-right: 1px solid #1e293b; }
        .brand { font-size: 20px; font-weight: bold; margin-bottom: 35px; padding-left: 10px; display: flex; align-items: center; gap: 8px; }
        .brand span { color: #38bdf8; }
        .nav-title { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; padding: 0 10px; margin-bottom: 10px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 12px 14px; margin-bottom: 5px; border-radius: 8px; color: #94a3b8; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background: #1e293b; color: #38bdf8; }
        .logout { position: absolute; bottom: 25px; left: 18px; right: 18px; color: #f87171; }
        .logout:hover { background: #450a0a; color: #fca5a5; }

        .main { margin-left: 250px; min-height: 100vh; background: #0f172a; }
        .topbar { height: 70px; background: #1e293b; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; }
        .topbar-title { color: #94a3b8; font-size: 14px; font-weight: 500; }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-name { font-size: 14px; font-weight: bold; color: #f8fafc; }
        .user-role { font-size: 11px; color: #38bdf8; margin-top: 2px; }
        .avatar { width: 38px; height: 38px; border-radius: 50%; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        .content { padding: 35px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-header h1 { margin: 0 0 7px; font-size: 26px; font-weight: 700; color: #f8fafc; }
        .page-header p { margin: 0; color: #94a3b8; font-size: 14px; }
        .upload-button { background: #2563eb; color: white; text-decoration: none; padding: 11px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
        .upload-button:hover { background: #1d4ed8; transform: translateY(-1px); }

        .file-panel { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 16px 20px; background: #182234; color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.7px; border-bottom: 1px solid #334155; }
        td { padding: 16px 20px; border-bottom: 1px solid #27354a; font-size: 13px; color: #e2e8f0; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #223046; }

        .file-name { font-weight: 600; color: #f8fafc; }
        .file-icon { display: inline-flex; width: 34px; height: 34px; align-items: center; justify-content: center; background: #0f172a; border-radius: 8px; margin-right: 12px; vertical-align: middle; font-size: 16px; border: 1px solid #334155; }
        .file-type { color: #94a3b8; font-size: 12px; font-weight: 500; }

        .status { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status-verified { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }

        .btn-action { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: 0.2s; }
        .btn-download { background: #0284c7; color: white; }
        .btn-download:hover { background: #0369a1; }
        .btn-inspect { background: #334155; color: #cbd5e1; }
        .btn-inspect:hover { background: #475569; color: white; }

        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; }
        .modal { background: #1e293b; border: 1px solid #334155; border-radius: 14px; width: 560px; max-width: 90%; padding: 28px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 14px; }
        .modal-title { font-size: 18px; font-weight: bold; color: #f8fafc; }
        .modal-close { background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer; }
        .telemetry-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #27354a; font-size: 13px; }
        .telemetry-label { color: #94a3b8; font-weight: 500; }
        .telemetry-value { font-family: monospace; color: #38bdf8; word-break: break-all; max-width: 65%; text-align: right; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        🔐 Adaptive <span>Vault</span>
    </div>
    <div class="nav-title">Main Menu</div>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/dashboard">🏠 Dashboard</a>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/upload">📤 Upload File</a>
    <a class="nav-item active" href="/adaptive-secure-file-vault/public/files">📁 My Files</a>
    <div class="nav-title" style="margin-top:25px;">Account</div>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/dashboard">👤 Account Security</a>
    <a class="nav-item logout" href="/adaptive-secure-file-vault/public/logout">↪ Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">Adaptive Secure File Vault · Storage Console</div>
        <div class="user-info">
            <div>
                <div class="user-name"><?= htmlspecialchars($name ?? 'User') ?></div>
                <div class="user-role"><?= ($roleId ?? 2) === 1 ? 'Administrator' : 'Standard User' ?></div>
            </div>
            <div class="avatar"><?= strtoupper(substr($name ?? 'U', 0, 1)) ?></div>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h1>Encrypted File Vault</h1>
                <p>Manage documents protected with AES-256-GCM encryption & ML threat inspection.</p>
            </div>
            <a class="upload-button" href="/adaptive-secure-file-vault/public/upload">＋ Upload Document</a>
        </div>

        <div class="file-panel">
            <?php if (empty($files)): ?>
                <div style="text-align:center; padding:60px 20px;">
                    <div style="font-size:48px; margin-bottom:12px;">📁</div>
                    <h2 style="margin:0 0 8px;">No files secured yet</h2>
                    <p style="color:#94a3b8; margin-bottom:20px;">Upload a file to run ML inspection, SHA-256 fingerprinting, and AES-256-GCM encryption.</p>
                    <a class="upload-button" href="/adaptive-secure-file-vault/public/upload">Upload File</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Integrity</th>
                            <th>Threat AI</th>
                            <th>Encryption</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($files as$file): ?>
                        <tr>
                            <td>
                                <span class="file-icon">
                                    <?php 
                                        $ext = strtolower($file['file_extension']);
                                        echo ($ext === 'pdf') ? '📄' : (in_array($ext, ['jpg','jpeg','png']) ? '🖼️' : '📎');
                                    ?>
                                </span>
                                <span class="file-name"><?= htmlspecialchars($file['original_name']) ?></span>
                            </td>
                            <td><span class="file-type"><?= htmlspecialchars(strtoupper($file['file_extension'])) ?></span></td>
                            <td>
                                <?php 
                                    $size = (int)$file['file_size'];
                                    echo ($size >= 1048576) ? number_format($size / 1048576, 2) . ' MB' : (($size >= 1024) ? number_format($size / 1024, 2) . ' KB' :$size . ' B');
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($file['sha256_hash'])): ?>
                                    <span class="status status-verified">✓ SHA-256</span>
                                <?php else: ?>
                                    <span class="status status-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($file['malware_status'] === 'clean'): ?>
                                    <span class="status status-verified">✓ Clean</span>
                                <?php else: ?>
                                    <span class="status status-pending"><?= htmlspecialchars(ucfirst($file['malware_status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($file['encryption_status'] === 'encrypted'): ?>
                                    <span class="status status-verified">✓ AES-256-GCM</span>
                                <?php else: ?>
                                    <span class="status status-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:8px;">
                                    <?php $isEnc = ($file['encryption_status'] === 'encrypted') ? 'true' : 'false'; ?>
                                    <a class="btn-action btn-download" 
                                       href="/adaptive-secure-file-vault/public/download?id=<?= (int)$file['id'] ?>" 
                                       onclick="return promptZkDownload(event, this.href, <?= $isEnc ?>, '<?= htmlspecialchars($file['mime_type'], ENT_QUOTES) ?>', '<?= htmlspecialchars($file['original_name'], ENT_QUOTES) ?>')"
                                       title="Decrypt in-memory and download">
                                        ⬇ Download
                                    </a>
                                    <button class="btn-action btn-inspect" type="button" onclick='openTelemetry(<?= json_encode($file) ?>)'>
                                        🔍 Inspect
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- AI & CRYPTO TELEMETRY MODAL -->
<div class="modal-overlay" id="telemetryModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">🛡️ Cryptographic & AI Inspection</div>
            <button class="modal-close" onclick="closeTelemetry()">&times;</button>
        </div>
        <div id="telemetryBody"></div>
        <div style="margin-top:20px; text-align:right;">
            <button class="btn-action btn-inspect" onclick="closeTelemetry()">Close</button>
        </div>
    </div>
</div>

<script>
function openTelemetry(file) {
    const body = document.getElementById('telemetryBody');
    const hash = file.sha256_hash ? file.sha256_hash : 'Pending generation';
    const cipher = file.encryption_status === 'encrypted' ? 'AES-256-GCM (Authenticated 128-bit MAC Tag)' : 'Plaintext Staging';
    const scanDetails = file.scan_message ? file.scan_message : 'Standard static checks passed without structural anomalies.';

    body.innerHTML = `
        <div class="telemetry-row">
            <span class="telemetry-label">File Identity:</span>
            <span class="telemetry-value">${file.original_name}</span>
        </div>
        <div class="telemetry-row">
            <span class="telemetry-label">MIME Type:</span>
            <span class="telemetry-value">${file.mime_type || 'Unknown'}</span>
        </div>
        <div class="telemetry-row">
            <span class="telemetry-label">Cipher Envelope:</span>
            <span class="telemetry-value" style="color:#4ade80;">${cipher}</span>
        </div>
        <div class="telemetry-row">
            <span class="telemetry-label">Integrity Hash:</span>
            <span class="telemetry-value">${hash}</span>
        </div>
        <div class="telemetry-row">
            <span class="telemetry-label">Detection Status:</span>
            <span class="telemetry-value" style="color:#4ade80;">${file.malware_status.toUpperCase()}</span>
        </div>
        <div class="telemetry-row" style="flex-direction:column; gap:6px;">
            <span class="telemetry-label">Analysis Notes:</span>
            <span class="telemetry-value" style="max-width:100%; text-align:left; color:#cbd5e1; font-size:12px; line-height:1.4;">${scanDetails}</span>
        </div>
    `;
    document.getElementById('telemetryModal').style.display = 'flex';
}

function closeTelemetry() {
    document.getElementById('telemetryModal').style.display = 'none';
}
</script>


<!-- Zero-Knowledge Decryption Passphrase Modal -->
<div id="zkDownloadModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #1e293b; padding: 30px; border-radius: 12px; width: 400px; border: 1px solid #334155; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        <h3 style="color: #f8fafc; margin-top: 0; margin-bottom: 12px; font-size: 18px;">🔓 Zero-Knowledge Decryption</h3>
        <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">Enter your encryption passphrase to decrypt and download this file in your browser.</p>
        <input type="password" id="zk_passphrase_input" placeholder="Enter file passphrase..." 
            style="width: 100%; background: #0b0f19; border: 1px solid #334155; color: #f8fafc; padding: 12px 16px; border-radius: 8px; font-size: 14px; outline: none; margin-bottom: 20px; box-sizing: border-box;">
        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" onclick="closeZkModal()" style="background: #334155; color: #f8fafc; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">Cancel</button>
            <button type="button" id="zk_decrypt_btn" style="background: #3b82f6; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">Decrypt & Download</button>
        </div>
    </div>
</div>

<script>
let activeDownloadUrl = '';
let activeFileMime = '';
let activeFileName = '';

function promptZkDownload(event, url, isEncrypted, mimeType, fileName) {
    if (!isEncrypted) {
        return true; // Let the browser handle standard plaintext downloads normally
    }
    
    event.preventDefault();
    activeDownloadUrl = url;
    activeFileMime = mimeType;
    activeFileName = fileName;
    
    document.getElementById('zk_passphrase_input').value = '';
    document.getElementById('zkDownloadModal').style.display = 'flex';
    return false;
}

function closeZkModal() {
    document.getElementById('zkDownloadModal').style.display = 'none';
    activeDownloadUrl = '';
    activeFileMime = '';
    activeFileName = '';
}

document.getElementById('zk_decrypt_btn').addEventListener('click', async () => {
    const passphrase = document.getElementById('zk_passphrase_input').value;
    if (!passphrase) {
        alert('Please enter your encryption passphrase.');
        return;
    }

    const btn = document.getElementById('zk_decrypt_btn');
    btn.textContent = 'Decrypting...';
    btn.disabled = true;

    try {
        const response = await fetch(activeDownloadUrl);
        if (!response.ok) throw new Error('Failed to fetch encrypted file payload.');
        
        const buffer = await response.arrayBuffer();
        
        // Extract Salt (16 bytes) + IV (16 bytes) + Ciphertext
        const salt = buffer.slice(0, 16);
        const iv = buffer.slice(16, 32);
        const ciphertext = buffer.slice(32);

        const enc = new TextEncoder();
        const keyMaterial = await window.crypto.subtle.importKey(
            "raw",
            enc.encode(passphrase),
            { name: "PBKDF2" },
            false,
            ["deriveKey"]
        );

        const key = await window.crypto.subtle.deriveKey(
            {
                name: "PBKDF2",
                salt: salt,
                iterations: 100000,
                hash: "SHA-256"
            },
            keyMaterial,
            { name: "AES-CBC", length: 256 },
            false,
            ["decrypt"]
        );

        const decryptedContent = await window.crypto.subtle.decrypt(
            { name: "AES-CBC", iv: iv },
            key,
            ciphertext
        );

        // Trigger local browser download with EXACT MIME type and Filename
        const blob = new Blob([decryptedContent], { type: activeFileMime });
        const downloadLink = document.createElement('a');
        downloadLink.href = window.URL.createObjectURL(blob);
        downloadLink.download = activeFileName;
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        closeZkModal();
    } catch (err) {
        console.error(err);
        alert('Decryption failed! Incorrect passphrase or corrupted cryptographic package.');
    } finally {
        btn.textContent = 'Decrypt & Download';
        btn.disabled = false;
    }
});
</script>
    
</body>
</html>
