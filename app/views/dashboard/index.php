<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Adaptive Secure Vault</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #f8fafc; }
        
        .sidebar { position: fixed; left: 0; top: 0; width: 250px; height: 100vh; background: #090d16; color: white; padding: 25px 18px; border-right: 1px solid #1e293b; z-index: 10; }
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
        .welcome { margin-bottom: 30px; }
        .welcome h1 { margin: 0 0 8px; font-size: 26px; font-weight: 700; color: #f8fafc; }
        .welcome p { margin: 0; color: #94a3b8; font-size: 14px; }

        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 25px; }
        .stat-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 22px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); }
        .stat-label { color: #94a3b8; font-size: 13px; margin-bottom: 8px; font-weight: 500; }
        .stat-value { font-size: 28px; font-weight: 700; color: #f8fafc; }
        .stat-description { font-size: 12px; margin-top: 6px; }

        .grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; }
        .panel { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 25px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); }
        .panel h2 { font-size: 18px; margin-top: 0; margin-bottom: 15px; color: #f8fafc; }

        .audit-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .audit-table th { text-align: left; padding: 10px; font-size: 11px; color: #94a3b8; text-transform: uppercase; border-bottom: 1px solid #334155; }
        .audit-table td { padding: 12px 10px; font-size: 12px; border-bottom: 1px solid #27354a; color: #cbd5e1; vertical-align: middle; }
        .audit-table tr:last-child td { border-bottom: none; }

        .badge-success { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .badge-blocked { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        .security-status { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.25); border-radius: 10px; padding: 14px; margin-bottom: 18px; }
        .status-title { font-weight: bold; font-size: 13px; color: #4ade80; }
        .status-text { color: #94a3b8; font-size: 11px; margin-top: 4px; }

        .security-list { list-style: none; padding: 0; margin: 0; }
        .security-list li { padding: 10px 0; border-bottom: 1px solid #27354a; font-size: 13px; color: #cbd5e1; display: flex; align-items: center; gap: 8px; }
        .security-list li:last-child { border-bottom: none; }
        .check { color: #4ade80; font-weight: bold; }

        .action-btn { display: inline-block; width: 100%; padding: 11px; background: #0284c7; color: white; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 13px; transition: 0.2s; }
        .action-btn:hover { background: #0369a1; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        🔐 Adaptive <span>Vault</span>
    </div>
    <div class="nav-title">Main Menu</div>
    <a class="nav-item active" href="/adaptive-secure-file-vault/public/dashboard">🏠 Dashboard</a>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/upload">📤 Upload File</a>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/files">📁 My Files</a>
    <div class="nav-title" style="margin-top:25px;">Account</div>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/account-security">👤 Account Security</a>
    <a class="nav-item logout" href="/adaptive-secure-file-vault/public/logout">↪ Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">Adaptive Secure File Vault · Security Operations</div>
        <div class="user-info">
            <div>
                <div class="user-name"><?= htmlspecialchars($name ?? 'User') ?></div>
                <div class="user-role"><?= ((int)($roleId ?? 2) === 1) ? 'Administrator' : 'Standard User' ?></div>
            </div>
            <div class="avatar"><?= strtoupper(substr($name ?? 'U', 0, 1)) ?></div>
        </div>
    </div>

    <div class="content">
        <div class="welcome">
            <h1>Welcome back, <?= htmlspecialchars($name ?? 'User') ?> 👋</h1>
            <p>Cryptographic envelope storage & threat inspection pipelines operational.</p>
        </div>

        <!-- DYNAMIC STAT METRICS -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Protected Files</div>
                <div class="stat-value" style="color:#38bdf8;"><?= (int)($totalSecuredFiles ?? 0) ?></div>
                <div class="stat-description" style="color:#94a3b8;">AES-256-GCM sealed envelopes</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Isolated Threats</div>
                <div class="stat-value" style="color:<?= ($totalQuarantined ?? 0) > 0 ? '#f87171' : '#4ade80' ?>;"><?= (int)($totalQuarantined ?? 0) ?></div>
                <div class="stat-description" style="color:#94a3b8;">Neutralized by ML threat engine</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Encrypted Storage Footprint</div>
                <div class="stat-value" style="color:#a855f7;"><?= htmlspecialchars($storageFormatted ?? '0 B') ?></div>
                <div class="stat-description" style="color:#94a3b8;">Zero plaintext exposure on disk</div>
            </div>
        </div>

        <div class="grid">
            <!-- LIVE AUDIT TRAIL -->
            <div class="panel">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h2 style="margin:0;">Live Security Audit Trail</h2>
                    <span style="font-size:11px; color:#38bdf8; font-family:monospace;">● Continuous Logging Active</span>
                </div>
                <p style="color:#94a3b8; font-size:13px; margin-top:0;">Immutable chronological ledger of authentication, inspection, and decryption requests.</p>
                
                <?php if (empty($recentAudits)): ?>
                    <p style="color:#64748b; font-size:13px; margin-top:20px;">No audit events recorded yet.</p>
                <?php else: ?>
                    <table class="audit-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Result</th>
                                <th>Context Details</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAudits as $log): ?>
                                <tr>
                                    <td style="font-family:monospace; font-weight:600; color:#38bdf8;"><?= htmlspecialchars($log['action'] ?? '') ?></td>
                                    <td>
                                        <?php if (strtolower($log['result'] ?? '') === 'success'): ?>
                                            <span class="badge-success">SUCCESS</span>
                                        <?php else: ?>
                                            <span class="badge-blocked"><?= htmlspecialchars(strtoupper($log['result'] ?? 'INFO')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width:240px; word-break:break-word; color:#cbd5e1;"><?= htmlspecialchars($log['details'] ?? '') ?></td>
                                    <td style="color:#94a3b8; font-size:11px;"><?= htmlspecialchars(date('M d, H:i', strtotime($log['created_at'] ?? 'now'))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- DEFENSIVE CONTROLS & SHORTCUT -->
            <div class="panel">
                <h2>Defensive Architecture</h2>
                <div class="security-status">
                    <div class="status-title">✓ Threat Ingestion Pipeline Verified</div>
                    <div class="status-text">Multi-layer defensive parsing runs before files commit to disk.</div>
                </div>

                <ul class="security-list">
                    <li><span class="check">✓</span> <strong>MIME Guard:</strong> Magic-byte allowlisting</li>
                    <li><span class="check">✓</span> <strong>Shannon Entropy:</strong> Byte packing analysis ($H \le 7.5$)</li>
                    <li><span class="check">✓</span> <strong>AST Classifier:</strong> Machine Learning (/Launch, /JS)</li>
                    <li><span class="check">✓</span> <strong>Integrity Engine:</strong> SHA-256 pre-encryption digest</li>
                    <li><span class="check">✓</span> <strong>Envelope Cipher:</strong> AES-256-GCM authenticated cipher</li>
                </ul>

                <div style="margin-top:24px; display:flex; flex-direction:column; gap:10px;">
                    <a href="/adaptive-secure-file-vault/public/upload" class="action-btn">
                        ＋ Ingest New Document
                    </a>
                    <a href="/adaptive-secure-file-vault/public/files" style="text-align:center; color:#38bdf8; text-decoration:none; font-size:13px; font-weight:600;">
                        📁 View Secured Files →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
