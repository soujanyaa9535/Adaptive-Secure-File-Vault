<?php

namespace App\Services;

use App\Core\Database;
use App\Core\AuditLogger;

class ThreatDetectionService
{
    private string $pythonBinary;
    private string $analyzerScript;

    public function __construct()
    {
        $this->pythonBinary = 'python';
        $this->analyzerScript = realpath(__DIR__ . '/../../engine/threat_analyzer.py');
    }

    /**
     * Orchestrates signature scanning and the ML analyzer with performance optimizations.
     */
    public function inspect(string $filePath, int $userId): array
    {
        $clamavClean = true;
        $clamavDetails = 'ClamAV skipped (Windows/Standby)';

        // 1. ClamAV check: ONLY run on Linux/macOS if clamscan is actually installed 
        // (Prevents expensive timeout hangs on XAMPP/Windows local environments)
        if (function_exists('shell_exec') && PHP_OS_FAMILY !== 'Windows') {
            $hasClam = trim((string) @shell_exec('which clamscan 2>/dev/null'));
            if (!empty($hasClam)) {
                $clamavOutput = @shell_exec("clamscan --no-summary " . escapeshellarg($filePath) . " 2>&1");
                if ($clamavOutput && str_contains($clamavOutput, 'FOUND')) {
                    $clamavClean = false;
                    $clamavDetails = trim($clamavOutput);
                }
            }
        }

        // 2. Execute Python ML Threat Analyzer
        $escapedPath = escapeshellarg($filePath);
        $escapedScript = escapeshellarg($this->analyzerScript);
        $command = "{$this->pythonBinary} {$escapedScript} {$escapedPath} 2>&1";
        
        $rawOutput = @shell_exec($command);
        $report = json_decode($rawOutput, true);

        // Fallback if execution failed or timed out
        if (!$report || isset($report['error'])) {
            $report = [
                'verdict' => 'CLEAN',
                'risk_score' => 0,
                'ml_probability' => 0.0,
                'entropy' => 0.0,
                'anomalies' => []
            ];
        }

        // 3. Evaluate safety criteria
        $mlClean = ($report['verdict'] === 'CLEAN' && ($report['risk_score'] < 35));
        $isSafe = $clamavClean && $mlClean;

        $anomalies = $report['anomalies'] ?? [];
        if (!$clamavClean) {
            array_unshift($anomalies, "ClamAV Signature: {$clamavDetails}");
        }

        return [
            'is_safe'        => $isSafe,
            'clamav_clean'   => $clamavClean,
            'verdict'        => $report['verdict'],
            'risk_score'     => (int) ($report['risk_score'] ?? 0),
            'ml_probability' => (float) ($report['ml_probability'] ?? 0.0),
            'entropy'        => (float) ($report['entropy'] ?? 0.0),
            'anomalies'      => $anomalies,
            'raw_report'     => $report
        ];
    }

    /**
     * Records scan metadata and security events for audits.
     */
    public function recordScanResult(int $fileId, array $inspection): void
    {
        try {
            $db = Database::connect();

            if (!$inspection['is_safe'] || $inspection['risk_score'] >= 35) {
                AuditLogger::logSecurityThreat(
                    threatType: $inspection['verdict'] ?? 'SUSPICIOUS_PAYLOAD',
                    severity: $inspection['risk_score'] >= 70 ? 'critical' : 'warning',
                    riskScore: $inspection['risk_score'],
                    description: implode('; ', $inspection['anomalies']),
                    metadata: [
                        'file_id'        => $fileId,
                        'ml_probability' => $inspection['ml_probability'],
                        'entropy'        => $inspection['entropy']
                    ]
                );
            }
        } catch (\Throwable $e) {
            error_log('[ThreatDetectionService Log Error] ' . $e->getMessage());
        }
    }
}