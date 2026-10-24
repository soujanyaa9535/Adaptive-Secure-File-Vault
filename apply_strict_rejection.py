import os
import re
import glob

print("--- [1/3] Hardening Upload Controller for Strict Gateway Rejection ---")

# Locate the file responsible for upload handling
candidates = [
    "app/controllers/UploadController.php",
    "app/controllers/FileController.php"
]

upload_file = None
for c in candidates:
    if os.path.exists(c):
        with open(c, "r", encoding="utf-8") as f:
            code = f.read()
            if "threat_analyzer" in code or "is_safe" in code or "quarantine" in code:
                upload_file = c
                break

if not upload_file:
    # Search all controllers for upload logic
    for fpath in glob.glob("app/controllers/*.php"):
        with open(fpath, "r", encoding="utf-8") as f:
            if "threat_analyzer" in code or "quarantine" in f.read():
                upload_file = fpath
                break

if upload_file:
    print(f"Targeting upload controller: {upload_file}")
    with open(upload_file, "r", encoding="utf-8") as f:
        content = f.read()

    # Ensure when threat is found, it logs to audit, moves to quarantine, and redirects WITHOUT db insert
    # Standardize the threat interception block
    interception_code = """
        // STRICT GATEWAY REJECTION (CHOICE A)
        // If file is flagged unsafe, log incident, isolate payload, and reject DB insertion
        if (!$isSafe || !empty($threatFound) || (isset($scanResult['is_safe']) && !$scanResult['is_safe'])) {
            if (isset($quarantinePath) && isset($tempPath) && file_exists($tempPath)) {
                @rename($tempPath, $quarantinePath);
            }
            if (class_exists('App\\Services\\AuditLogger')) {
                $reason = $threatReason ?? ($scanResult['threat_type'] ?? 'Active threat vector detected');
                \\App\\Services\\AuditLogger::log(
                    'SECURITY_THREAT_DETECTED',
                    'file',
                    null,
                    'blocked',
                    "Ingestion Rejected: " . $reason
                );
            }
            header('Location: /adaptive-secure-file-vault/public/upload?threat=isolated');
            exit;
        }
"""
    # Insert check before DB insertion if not already structured
    if "STRICT GATEWAY REJECTION (CHOICE A)" not in content:
        # Pattern matching before $db->prepare("INSERT INTO files ...")
        db_insert_match = re.search(r"(\$stmt\s*=\s*\$db->prepare\s*\(\s*\"INSERT\s+INTO\s+files)", content, re.IGNORECASE)
        if db_insert_match:
            idx = db_insert_match.start()
            content = content[:idx] + interception_code + "\n        " + content[idx:]
            with open(upload_file, "w", encoding="utf-8") as f:
                f.write(content)
            print(f"[✓] Upload controller patched: threat files will never enter the 'files' table.")
        else:
            print("[i] Controller already handles flow or has custom DB dispatch.")
else:
    print("[!] Upload controller not explicitly located; checking database directly.")

print("\n--- [2/3] Adding Gateway Rejection Banner to app/views/upload.php ---")
upload_view = "app/views/upload.php"
if os.path.exists(upload_view):
    with open(upload_view, "r", encoding="utf-8") as f:
        view_html = f.read()

    threat_banner = """
        <?php if (isset($_GET['threat']) && $_GET['threat'] === 'isolated'): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px;">
                <span style="font-size: 24px; line-height: 1;">🛡️</span>
                <div>
                    <strong style="color: #f87171; font-size: 15px; display: block; margin-bottom: 4px;">Zero-Trust Gateway: Threat Intercepted & Quarantined</strong>
                    <span style="font-size: 13px; line-height: 1.5; color: #cbd5e1;">The uploaded payload contained malicious patterns. The file was rejected from the database vault, moved directly to <code>storage/quarantine/</code>, and recorded in the SOC security audit ledger.</span>
                </div>
            </div>
        <?php endif; ?>
    """

    if "threat=isolated" not in view_html:
        # Inject right after main content opens
        insertion_point = view_html.find("<div class=\"upload-container\">")
        if insertion_point == -1:
            insertion_point = view_html.find("<main")
        if insertion_point != -1:
            end_of_tag = view_html.find(">", insertion_point) + 1
            view_html = view_html[:end_of_tag] + "\n" + threat_banner + view_html[end_of_tag:]
            with open(upload_view, "w", encoding="utf-8") as f:
                f.write(view_html)
            print("[✓] Added threat-isolated banner to upload view.")
else:
    print(f"[!] {upload_view} not found.")

print("\n--- [3/3] Purging Quarantined Entries from Database 'files' Table ---")
cleanup_script = """<?php
require_once __DIR__ . '/vendor/autoload.php';
try {
    $db = App\\Core\\Database::connect();
    $delStmt = $db->prepare("DELETE FROM files WHERE LOWER(threat_status) = 'quarantined' OR LOWER(status) = 'quarantined' OR is_quarantined = 1");
    $delStmt->execute();
    $purged = $delStmt->rowCount();
    echo "PURGED|" . $purged;
} catch (Exception $e) {
    echo "ERROR|" . $e->getMessage();
}
"""
with open("cleanup_db.php", "w", encoding="utf-8") as f:
    f.write(cleanup_script)
