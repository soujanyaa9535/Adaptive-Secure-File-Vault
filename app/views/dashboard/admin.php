<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Security Panel - Adaptive Vault</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        .navbar {
            background: #111827;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-size: 18px;
            font-weight: bold;
        }

        .back {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .container {
            padding: 40px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            margin-bottom: 8px;
        }

        .header p {
            color: #6b7280;
        }

        .security-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .security-card h2 {
            margin-top: 0;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
            font-weight: bold;
        }

        .controls {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .control {
            padding: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fafafa;
        }

        .control strong {
            display: block;
            margin-bottom: 6px;
        }

        .control span {
            color: #6b7280;
            font-size: 13px;
        }

        @media (max-width: 700px) {

            .controls {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<div class="navbar">

    <div class="brand">
        🔐 Adaptive Vault — Admin
    </div>

    <a
        class="back"
        href="/adaptive-secure-file-vault/public/dashboard"
    >
        ← Dashboard
    </a>

</div>


<div class="container">

    <div class="header">

        <h1>
            Administrator Security Panel
        </h1>

        <p>
            Restricted area for authorized administrators.
        </p>

    </div>


    <div class="security-card">

        <h2>
            Access Control Status
        </h2>

        <span class="status">
            ✓ Administrator Access Verified
        </span>

        <p>
            Welcome,
            <strong>
                <?= htmlspecialchars($name) ?>
            </strong>.
        </p>

        <p style="color:#6b7280;font-size:13px;">
            This page is protected by server-side
            role-based access control.
        </p>

    </div>


    <div class="security-card">

        <h2>
            Administrative Security Controls
        </h2>

        <div class="controls">

            <div class="control">

                <strong>
                    👥 User Management
                </strong>

                <span>
                    Manage authorized system users.
                </span>

            </div>


            <div class="control">

                <strong>
                    📋 Audit Logs
                </strong>

                <span>
                    Review security-related activities.
                </span>

            </div>


            <div class="control">

                <strong>
                    🛡 Security Monitoring
                </strong>

                <span>
                    Monitor security events and threats.
                </span>

            </div>


            <div class="control">

                <strong>
                    ⚙ Security Configuration
                </strong>

                <span>
                    Manage system security settings.
                </span>

            </div>

        </div>

    </div>

</div>

</body>

</html>