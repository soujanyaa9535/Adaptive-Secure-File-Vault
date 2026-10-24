<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Security - Adaptive Secure Vault</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #f8fafc; }
        .sidebar { position: fixed; left: 0; top: 0; width: 250px; height: 100vh; background: #090d16; color: white; padding: 25px 18px; border-right: 1px solid #1e293b; }
        .brand { font-size: 20px; font-weight: bold; margin-bottom: 35px; padding-left: 10px; }
        .brand span { color: #38bdf8; }
        .nav-title { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; padding: 0 10px; margin-bottom: 10px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 12px 14px; margin-bottom: 5px; border-radius: 8px; color: #94a3b8; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background: #1e293b; color: #38bdf8; }
        .logout { position: absolute; bottom: 25px; left: 18px; right: 18px; color: #f87171; }

        .main { margin-left: 250px; min-height: 100vh; background: #0f172a; }
        .topbar { height: 70px; background: #1e293b; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; }
        .content { padding: 35px; max-width: 900px; }
        .panel { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        .panel h2 { margin-top: 0; font-size: 18px; color: #f8fafc; margin-bottom: 18px; }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #27354a; font-size: 14px; }
        .info-label { color: #94a3b8; }
        .info-value { color: #38bdf8; font-family: monospace; font-weight: 600; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">🔐 Adaptive <span>Vault</span></div>
    <div class="nav-title">Main Menu</div>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/dashboard">🏠 Dashboard</a>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/upload">📤 Upload File</a>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/files">📁 My Files</a>
    <div class="nav-title" style="margin-top:25px;">Account</div>
    <a class="nav-item active" href="/adaptive-secure-file-vault/public/account-security">👤 Account Security</a>
    <a class="nav-item logout" href="/adaptive-secure-file-vault/public/logout">↪ Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <div style="color:#94a3b8; font-size:14px; font-weight:500;">Account Security Profile</div>
    </div>
    <div class="content">
        <h1 style="font-size:26px; margin-bottom:8px;">Identity & Security Credentials</h1>
        <p style="color:#94a3b8; margin-bottom:25px; font-size:14px;">Review your cryptographic session parameters and vault permissions.</p>

        <div class="panel">
            <h2>User Identity</h2>
            <div class="info-row">
                <span class="info-label">Full Name</span>
                <span class="info-value" style="font-family:inherit;"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email Account</span>
                <span class="info-value"><?= htmlspecialchars($_SESSION['email'] ?? 'Registered Account') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Access Level</span>
                <span class="badge"><?= ((int)($_SESSION['role_id'] ?? 2) === 1) ? 'Administrator' : 'Standard User' ?></span>
            </div>
        </div>

        <div class="panel">
            <h2>Active Session Telemetry</h2>
            <div class="info-row">
                <span class="info-label">Client IP Address</span>
                <span class="info-value"><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">User Agent</span>
                <span class="info-value" style="font-size:12px; max-width:60%; text-align:right;"><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Browser') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Session Protection</span>
                <span class="badge">HTTPOnly + SameSite Cookie</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
