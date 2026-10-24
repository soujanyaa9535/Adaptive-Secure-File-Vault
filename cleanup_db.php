<?php
require_once __DIR__ . '/vendor/autoload.php';
try {
    $db = App\Core\Database::connect();
    $delStmt = $db->prepare("DELETE FROM files WHERE LOWER(threat_status) = 'quarantined' OR LOWER(status) = 'quarantined' OR is_quarantined = 1");
    $delStmt->execute();
    $purged = $delStmt->rowCount();
    echo "PURGED|" . $purged;
} catch (Exception $e) {
    echo "ERROR|" . $e->getMessage();
}
