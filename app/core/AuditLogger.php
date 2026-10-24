<?php

namespace App\Core;

class AuditLogger
{
    /**
     * Record an event into the immutable audit_logs table.
     */
    public static function log(
        string $action,
        ?string $resourceType = null,
        ?int $resourceId = null,
        string $result = 'success',
        ?string $details = null,
        ?int $userId = null
    ): void {
        try {
            $db = Database::connect();

            if ($userId === null) {
                $userId = Session::get('user_id');
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

            $stmt = $db->prepare(
                "INSERT INTO audit_logs 
                (user_id, action, resource_type, resource_id, ip_address, user_agent, result, details)
                VALUES 
                (:user_id, :action, :resource_type, :resource_id, :ip_address, :user_agent, :result, :details)"
            );

            $stmt->execute([
                ':user_id'       => $userId,
                ':action'        => $action,
                ':resource_type' => $resourceType,
                ':resource_id'   => $resourceId,
                ':ip_address'    => $ipAddress,
                ':user_agent'    => $userAgent,
                ':result'        => $result,
                ':details'       => $details
            ]);
        } catch (\Throwable $e) {
            error_log('[AuditLogger Error] ' . $e->getMessage());
        }
    }

    /**
     * Record a threat event into security_events and threat_alerts.
     */
    public static function logSecurityThreat(
        string $threatType,
        string $severity,
        int $riskScore,
        string $description,
        array $metadata = [],
        ?int $userId = null
    ): void {
        try {
            $db = Database::connect();

            if ($userId === null) {
                $userId = Session::get('user_id');
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            // 1. Insert into security_events
            $eventStmt = $db->prepare(
                "INSERT INTO security_events 
                (user_id, event_type, severity, description, ip_address, metadata)
                VALUES 
                (:user_id, :event_type, :severity, :description, :ip_address, :metadata)"
            );

            $eventStmt->execute([
                ':user_id'     => $userId,
                ':event_type'  => $threatType,
                ':severity'    => $severity,
                ':description' => $description,
                ':ip_address'  => $ipAddress,
                ':metadata'    => json_encode($metadata)
            ]);

            $securityEventId = (int) $db->lastInsertId();

            // 2. Insert into threat_alerts
            $alertStmt = $db->prepare(
                "INSERT INTO threat_alerts 
                (user_id, security_event_id, threat_type, risk_score, status, description)
                VALUES 
                (:user_id, :security_event_id, :threat_type, :risk_score, 'open', :description)"
            );

            $alertStmt->execute([
                ':user_id'           => $userId,
                ':security_event_id' => $securityEventId,
                ':threat_type'       => $threatType,
                ':risk_score'        => min(255, max(0, $riskScore)),
                ':description'       => $description
            ]);

            // 3. Mirror into audit_logs
            self::log(
                action: 'SECURITY_THREAT_DETECTED',
                resourceType: 'threat_alert',
                resourceId: (int) $db->lastInsertId(),
                result: 'blocked',
                details: $description,
                userId: $userId
            );
        } catch (\Throwable $e) {
            error_log('[AuditLogger Threat Error] ' . $e->getMessage());
        }
    }
}
