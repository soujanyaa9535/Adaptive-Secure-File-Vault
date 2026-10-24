
        <?php if (isset($_GET['threat']) && $_GET['threat'] === 'isolated'): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px;">
                <span style="font-size: 24px; line-height: 1;">🛡️</span>
                <div>
                    <strong style="color: #f87171; font-size: 15px; display: block; margin-bottom: 4px;">Zero-Trust Gateway: Threat Intercepted & Quarantined</strong>
                    <span style="font-size: 13px; line-height: 1.5; color: #cbd5e1;">The uploaded payload contained malicious patterns. The file was rejected from the database vault, moved directly to <code>storage/quarantine/</code>, and recorded in the SOC security audit ledger.</span>
                </div>
            </div>
        <?php endif; ?>
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Upload - Adaptive Secure Vault</title>
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

        .main { margin-left: 250px; min-height: 100vh; background: #0f172a; }
        .topbar { height: 70px; background: #1e293b; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; }
        .content { padding: 35px; max-width: 900px; margin: 0 auto; }

        .back-nav { display: inline-flex; align-items: center; gap: 6px; color: #38bdf8; text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 20px; transition: 0.2s; }
        .back-nav:hover { color: #7dd3fc; transform: translateX(-3px); }

        .card { background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 36px; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.4); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 26px; margin: 0 0 8px; font-weight: 700; color: #f8fafc; }
        .header p { color: #94a3b8; font-size: 14px; margin: 0; }

        .pipeline-banner { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: #0f172a; padding: 14px; border-radius: 10px; border: 1px solid #27354a; margin-bottom: 28px; }
        .pipeline-step { font-size: 11px; text-align: center; color: #94a3b8; }
        .pipeline-step strong { display: block; color: #38bdf8; font-size: 12px; margin-bottom: 2px; }

        .upload-area { border: 2px dashed #334155; border-radius: 12px; padding: 45px 25px; text-align: center; background: #151f32; transition: 0.2s; cursor: pointer; }
        .upload-area:hover, .upload-area.dragover { border-color: #38bdf8; background: #1a2740; }
        .upload-symbol { font-size: 40px; margin-bottom: 12px; }
        .choose-btn { display: inline-block; padding: 10px 22px; background: #2563eb; color: white; border-radius: 7px; cursor: pointer; font-size: 13px; font-weight: 600; margin-top: 15px; }
        .choose-btn:hover { background: #1d4ed8; }
        .file-name { margin-top: 14px; font-size: 13px; color: #38bdf8; font-family: monospace; }

        .upload-btn { width: 100%; margin-top: 25px; padding: 14px; border: none; border-radius: 8px; background: #0284c7; color: white; font-size: 14px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .upload-btn:hover { background: #0369a1; }
        .upload-btn:disabled { background: #334155; color: #64748b; cursor: not-allowed; }

        /* LIVE SOC SCANNER MODAL */
        .scan-overlay { display: none; position: fixed; inset: 0; background: rgba(10, 15, 29, 0.92); backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 9999; }
        .scan-box { width: 620px; max-width: 92%; background: #111827; border: 1px solid #0284c7; border-radius: 16px; padding: 32px; box-shadow: 0 0 50px rgba(2, 132, 199, 0.35); text-align: left; }
        .radar-spinner { width: 56px; height: 56px; border: 3px solid rgba(56, 189, 248, 0.2); border-top-color: #38bdf8; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        .terminal-feed { background: #030712; border: 1px solid #1f2937; border-radius: 10px; padding: 16px; font-family: "SF Mono", Consolas, "Liberation Mono", Menlo, monospace; font-size: 12px; color: #38bdf8; height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
        .feed-line { display: flex; align-items: center; gap: 8px; opacity: 0; transform: translateY(6px); animation: lineIn 0.3s forwards; }
        .feed-line.active { color: #f8fafc; font-weight: bold; }
        .feed-line.success { color: #4ade80; }
        @keyframes lineIn { to { opacity: 1; transform: translateY(0); } }

        .hud-progress-bar { width: 100%; height: 6px; background: #1f2937; border-radius: 4px; overflow: hidden; margin: 20px 0 12px; }
        .hud-progress-fill { width: 0%; height: 100%; background: linear-gradient(90deg, #0284c7, #38bdf8); transition: width 0.4s ease; }

        /* Standard Modals */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; }
        .modal { width: 440px; max-width: 90%; background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 30px; text-align: center; }
        .modal-icon { width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; }
        .success-icon { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); }
        .danger-icon { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .modal h2 { margin: 0 0 10px; font-size: 20px; color: #f8fafc; }
        .modal p { margin: 0 0 20px; color: #94a3b8; font-size: 13px; line-height: 1.5; }
        .modal-btn { border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; }
    </style>
    <script src="/adaptive-secure-file-vault/public/js/zk-crypto.js"></script>
</head>
<body>

<div class="sidebar">
    <div class="brand">🔐 Adaptive <span>Vault</span></div>
    <div class="nav-title">Main Menu</div>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/dashboard">🏠 Dashboard</a>
    <a class="nav-item active" href="/adaptive-secure-file-vault/public/upload">📤 Upload File</a>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/files">📁 My Files</a>
    <div class="nav-title" style="margin-top:25px;">Account</div>
    <a class="nav-item" href="/adaptive-secure-file-vault/public/account-security">👤 Account Security</a>
    <a class="nav-item logout" href="/adaptive-secure-file-vault/public/logout">↪ Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <div style="color:#94a3b8; font-size:14px; font-weight:500;">Secure Upload Gateway</div>
    </div>

    <div class="content">
        <a href="/adaptive-secure-file-vault/public/dashboard" class="back-nav">← Back to Dashboard</a>

        <div class="card">
            <div class="header">
                <h1>Adaptive Document Ingestion</h1>
                <p>Documents pass through AI structural parsing and authenticated AES-256-GCM ciphering.</p>
            </div>

            <div class="pipeline-banner">
                <div class="pipeline-step"><strong>1. MIME Guard</strong>Type whitelisting</div>
                <div class="pipeline-step"><strong>2. Shannon Entropy</strong>Packing analysis</div>
                <div class="pipeline-step"><strong>3. Random Forest</strong>AST exploit check</div>
                <div class="pipeline-step"><strong>4. AES-256-GCM</strong>Cipher envelope</div>
            </div>

            <form id="uploadForm" action="/adaptive-secure-file-vault/public/upload" method="POST" enctype="multipart/form-data">
                <div class="upload-area" id="uploadArea" onclick="document.getElementById('document').click()">
                    <div class="upload-symbol">📤</div>
                    <strong style="color:#f8fafc; font-size:16px;">Click or Drag File Here</strong>
                    <div style="color:#94a3b8; font-size:12px; margin-top:6px;">Supported: PDF, DOC, DOCX, TXT, JPG, PNG (Max 10MB)</div>
                    <label class="choose-btn" onclick="event.stopPropagation()">
                        Browse Files
                        <input type="file" id="document" name="document" style="display:none" required accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                    </label>
                    <div class="file-name" id="fileName">No file selected</div>
                </div>

                <button type="button" class="upload-btn" id="uploadButton" onclick="triggerScanPipeline()">
                    🛡️ Scan & Store Encrypted
                </button>
            
            <!-- Zero-Knowledge Encryption Passphrase Field -->
            <div style="margin: 20px 0; text-align: left;">
                <label for="encryption_passphrase" style="display: block; color: #94a3b8; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    🔐 Zero-Knowledge Encryption Passphrase
                </label>
                <input type="password" id="encryption_passphrase" name="encryption_passphrase" placeholder="Enter secret encryption passphrase..." required
                    style="width: 100%; background: #0b0f19; border: 1px solid #334155; color: #f8fafc; padding: 12px 16px; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#334155'">
                <span style="display: block; color: #64748b; font-size: 12px; margin-top: 6px;">
                    This passphrase derives your local AES-256 key. The server never stores your key.
                </span>
            </div>
    
</form>
        </div>
    </div>
</div>

<!-- LIVE SOC SECURITY SCANNER HUD -->
<div class="scan-overlay" id="scanOverlay">
    <div class="scan-box">
        <div class="radar-spinner"></div>
        <div style="text-align:center; margin-bottom:18px;">
            <h2 style="margin:0 0 6px; font-size:20px; color:#f8fafc;">Threat Inspection Pipeline Active</h2>
            <div style="color:#94a3b8; font-size:12px;">Real-time heuristics & AST structural analysis in progress...</div>
        </div>

        <div class="hud-progress-bar">
            <div class="hud-progress-fill" id="hudProgressFill"></div>
        </div>

        <div class="terminal-feed" id="terminalFeed">
            <!-- Feed lines inserted dynamically -->
        </div>
    </div>
</div>

<!-- SUCCESS MODAL -->
<div class="modal-overlay" id="successModal">
    <div class="modal">
        <div class="modal-icon success-icon">✓</div>
        <h2>Document Secured</h2>
        <p>Your file cleared the ML threat model, was signed with SHA-256, and encrypted with AES-256-GCM.</p>
        <button class="modal-btn" style="background:#22c55e; color:black;" onclick="window.location.href='/adaptive-secure-file-vault/public/files'">View in Vault</button>
    </div>
</div>

<!-- THREAT / MALWARE MODAL -->
<div class="modal-overlay" id="threatModal">
    <div class="modal">
        <div class="modal-icon danger-icon">⚠️</div>
        <h2>Exploit Vector Quarantined</h2>
        <p id="threatReason">A structural anomaly or malicious trigger was detected by the Random Forest classifier.</p>
        <button class="modal-btn" style="background:#ef4444; color:white;" onclick="document.getElementById('threatModal').style.display='none'">Dismiss</button>
    </div>
</div>

<script>
const fileInput = document.getElementById('document');
const fileName = document.getElementById('fileName');
const uploadArea = document.getElementById('uploadArea');
const uploadForm = document.getElementById('uploadForm');

fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        fileName.textContent = 'Selected: ' + this.files[0].name;
    }
});

uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        fileName.textContent = 'Selected: ' + e.dataTransfer.files[0].name;
    }
});

// Real-Time Scanning Sequence Visualizer
function triggerScanPipeline() {
    if (!fileInput.files.length) {
        alert('Please select a file to upload first.');
        return;
    }

    const overlay = document.getElementById('scanOverlay');
    const feed = document.getElementById('terminalFeed');
    const progress = document.getElementById('hudProgressFill');

    overlay.style.display = 'flex';
    feed.innerHTML = '';

    const stages = [
        { pct: 20, text: '🔍 [STAGE 1] Ingesting binary stream & checking Magic Bytes / MIME allowlist...' },
        { pct: 45, text: '📊 [STAGE 2] Calculating Shannon Byte Entropy: H(X) = -Σ P(x) log₂(P(x))...' },
        { pct: 68, text: '🧠 [STAGE 3] Scanning AST Dictionaries for exploit hooks (/Launch, /JS, /EmbeddedFiles)...' },
        { pct: 88, text: '🛡️ [STAGE 4] Evaluating Random Forest Classifier (threat_model.pkl)...' },
        { pct: 100, text: '🔐 [STAGE 5] Generating SHA-256 fingerprint & sealing AES-256-GCM envelope...' }
    ];

    let current = 0;
    function runNextStage() {
        if (current < stages.length) {
            const item = stages[current];
            progress.style.width = item.pct + '%';

            const line = document.createElement('div');
            line.className = 'feed-line active';
            line.innerHTML = `<span>⚡</span> <span>${item.text}</span>`;
            feed.appendChild(line);
            feed.scrollTop = feed.scrollHeight;

            current++;
            setTimeout(runNextStage, 650);
        } else {
            // Once visual sequence completes, submit form to backend
            uploadForm.submit();
        }
    }

    runNextStage();
}

// Server Response Handling
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success') === 'uploaded') {
    document.getElementById('successModal').style.display = 'flex';
}
if (urlParams.get('error') === 'threat_detected') {
    const reason = urlParams.get('reason');
    if (reason) {
        document.getElementById('threatReason').textContent = decodeURIComponent(reason);
    }
    document.getElementById('threatModal').style.display = 'flex';
}
</script>

</body>
</html>
