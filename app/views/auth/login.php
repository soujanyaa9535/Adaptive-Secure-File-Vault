<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Authentication Gateway - Adaptive Vault</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 80%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-container { width: 100%; max-width: 440px; }
        .auth-card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 16px;
            padding: 38px 32px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 30px rgba(2, 132, 199, 0.15);
        }
        .brand-header { text-align: center; margin-bottom: 26px; }
        .brand-icon { font-size: 38px; margin-bottom: 10px; display: inline-block; filter: drop-shadow(0 0 12px rgba(56, 189, 248, 0.4)); }
        .brand-title { font-size: 22px; font-weight: 700; margin: 0 0 6px; color: #f8fafc; }
        .brand-title span { color: #38bdf8; }
        .brand-subtitle { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; margin: 0; }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 13px 15px;
            border-radius: 10px;
            font-size: 13px;
            line-height: 1.45;
            margin-bottom: 22px;
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .alert-error { background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; }
        .alert-success { background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.4); color: #86efac; }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 11px; font-weight: 600; color: #94a3b8; margin-bottom: 7px; text-transform: uppercase; letter-spacing: 0.5px; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            background: #090d16;
            border: 1px solid #334155;
            border-radius: 9px;
            color: #f8fafc;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }
        input:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18); background: #0f172a; }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            border-radius: 9px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 6px;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        }
        .btn-submit:hover { background: linear-gradient(135deg, #0369a1 0%, #1d4ed8 100%); transform: translateY(-1px); }

        .auth-footer { margin-top: 26px; padding-top: 18px; border-top: 1px solid #1e293b; text-align: center; font-size: 13px; color: #94a3b8; }
        .auth-footer a { color: #38bdf8; text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; color: #7dd3fc; }
        .security-badge { margin-top: 22px; text-align: center; font-size: 11px; color: #64748b; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="brand-header">
            <div class="brand-icon">🔐</div>
            <h1 class="brand-title">Adaptive <span>Vault</span></h1>
            <p class="brand-subtitle">Zero-Trust Ingestion Gateway</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <span style="font-size:16px;">⚠️</span>
                <div>
                    <strong>Authentication Denied</strong><br>
                    Invalid email or password. Verify your credentials or create a new account below.
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
            <div class="alert alert-success">
                <span style="font-size:16px;">✓</span>
                <div>
                    <strong>Registration Complete</strong><br>
                    Your account has been created. Please sign in below.
                </div>
            </div>
        <?php endif; ?>

        <form action="/adaptive-secure-file-vault/public/login" method="POST" autocomplete="on">
            <div class="form-group">
                <label for="email">Identity / Email</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? ($_GET['email'] ?? '')) ?>">
            </div>

            <div class="form-group">
                <label for="password">Cryptographic Passphrase</label>
                <input type="password" id="password" name="password" required placeholder="••••••••••••" autocomplete="current-password">
            </div>

            <button type="submit" class="btn-submit">Authenticate & Enter Vault</button>
        </form>

        <div class="auth-footer">
            Don't have an identity profile? 
            <a href="/adaptive-secure-file-vault/public/register">Create Account</a>
        </div>
    </div>

    <div class="security-badge">
        🛡️ Protected by Bcrypt/Argon2 Hashing & AES-256-GCM
    </div>
</div>

</body>
</html>