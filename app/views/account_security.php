<?php
use App\Core\Session;
$fullName = htmlspecialchars($userData['full_name'] ?? 'Operator');
$email = htmlspecialchars($userData['email'] ?? 'operator@adaptive-vault.local');
$role = ((int)($userData['role_id'] ?? 2) === 1) ? 'System Administrator' : 'Standard Operator';
$initial = strtoupper(substr($fullName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Security - Adaptive Vault</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #090d16;
            color: #f8fafc;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #0f172a;
            border-right: 1px solid #1e293b;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .brand {
            font-size: 19px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #f8fafc;
            margin-bottom: 32px;
        }
        .brand span { color: #38bdf8; }
        .nav-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            transition: 0.2s;
        }
        .nav-links a:hover, .nav-links a.active {
            background: #1e293b;
            color: #38bdf8;
        }
        .logout-link {
            color: #ef4444 !important;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 14px;
        }
        .main-content {
            flex: 1;
            padding: 32px 40px;
            overflow-y: auto;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 24px;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 32px;
        }
        .topbar-subtitle {
            font-size: 13px;
            color: #64748b;
        }
        .user-badge {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #ffffff;
        }
        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 14px;
            padding: 28px;
        }
        .card-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-desc {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 22px;
            line-height: 1.5;
        }
        .identity-row {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #1f2937;
            font-size: 14px;
        }
        .identity-row:last-child { border-bottom: none; }
        .row-label { color: #64748b; font-weight: 500; }
        .row-val { color: #f8fafc; font-weight: 600; }
        .badge {
            display: inline-block;
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 7px; text-transform: uppercase; }
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            background: #090d16;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #f8fafc;
            font-size: 14px;
            outline: none;
        }
        input[type="password"]:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
        }
        .btn-update {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 8px;
        }
        .btn-update:hover { background: linear-gradient(135deg, #0369a1 0%, #1d4ed8 100%); }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-success { background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #86efac; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div>
        <div class="brand">
            <span>🛡️</span> Adaptive Vault
        </div>
        <div class="nav-label">Main Menu</div>
        <nav class="nav-links">
            <a href="/adaptive-secure-file-vault/public/dashboard">📊 Dashboard</a>
            <a href="/adaptive-secure-file-vault/public/upload">📤 Upload File</a>
            <a href="/adaptive-secure-file-vault/public/files">📁 My Files</a>
            <a href="/adaptive-secure-file-vault/public/account-security" class="active">👤 Account Security</a>
        </nav>
    </div>
    <a href="/adaptive-secure-file-vault/public/logout" class="logout-link">↪ Logout</a>
</aside>

<main class="main-content">
    <div class="topbar">
        <div>
            <h1 style="font-size: 22px; font-weight: 700;">Account Security Operations</h1>
            <p class="topbar-subtitle">Cryptographic identity lifecycle and access management</p>
        </div>
        <div class="user-badge">
            <div style="text-align: right;">
                <div style="font-weight: 600; font-size: 14px;"><?= $fullName ?></div>
                <div style="font-size: 12px; color: #64748b;"><?= $role ?></div>
            </div>
            <div class="avatar"><?= $initial ?></div>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ Cryptographic passphrase updated successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <?php
                $err = $_GET['error'];
                if ($err === 'invalid_current') echo '⚠️ Verification failed: Current passphrase does not match.';
                elseif ($err === 'mismatch') echo '⚠️ Verification failed: New passphrases do not match.';
                elseif ($err === 'weak') echo '⚠️ Security policy rejected: Passphrase must be at least 6 characters.';
                else echo '⚠️ Security update failed. Please verify entries and retry.';
            ?>
        </div>
    <?php endif; ?>

    <div class="grid-layout">
        <!-- Identity Summary Card -->
        <div class="card">
            <div class="card-title">🔐 Identity Envelope</div>
            <p class="card-desc">Active session authorization parameters established via Bcrypt/Argon2 integrity tokens.</p>

            <div class="identity-row">
                <span class="row-label">Operator Name</span>
                <span class="row-val"><?= $fullName ?></span>
            </div>
            <div class="identity-row">
                <span class="row-label">Primary Email</span>
                <span class="row-val"><?= $email ?></span>
            </div>
            <div class="identity-row">
                <span class="row-label">Role Classification</span>
                <span class="badge"><?= $role ?></span>
            </div>
            <div class="identity-row">
                <span class="row-label">Operator ID</span>
                <span class="row-val">UID-<?= str_pad((string)$userData['id'], 5, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="identity-row">
                <span class="row-label">Session Protection</span>
                <span class="row-val" style="color: #38bdf8;">In-Memory AES Cipher</span>
            </div>
        </div>

        <!-- Update Passphrase Card -->
        <div class="card">
            <div class="card-title">🔑 Rotate Passphrase</div>
            <p class="card-desc">Update your master credential. All active encryption and file integrity workflows will synchronize immediately.</p>

            <form action="/adaptive-secure-file-vault/public/account-security" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Passphrase</label>
                    <input type="password" id="current_password" name="current_password" required placeholder="••••••••••••">
                </div>
                <div class="form-group">
                    <label for="new_password">New Passphrase</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Min. 6 characters">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Passphrase</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Re-enter new passphrase">
                </div>
                <button type="submit" class="btn-update">Update Master Passphrase</button>
            </form>
        </div>
    </div>
</main>

</body>
</html>