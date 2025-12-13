<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Absensi Realtime | Face Recognition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0f172a;
            --card: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.1);
            --text: #e2e8f0;
            --muted: #94a3b8;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --primary: #3b82f6;
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: var(--muted);
            margin-bottom: 20px;
        }

        .config-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 16px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .config-bar label {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: var(--muted);
        }

        .config-bar input,
        .config-bar select {
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            width: 120px;
        }

        .main-area {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .main-area {
                grid-template-columns: 1fr;
            }
        }

        .camera-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .video-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4/3;
            background: #000;
        }

        #camera {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .camera-controls {
            padding: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
        }

        button:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .status-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
        }

        .status-title {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .status-box {
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 16px;
            transition: all 0.3s;
        }

        .status-box.waiting {
            background: rgba(59, 130, 246, 0.2);
            border: 2px solid var(--primary);
        }

        .status-box.success {
            background: rgba(34, 197, 94, 0.2);
            border: 2px solid var(--success);
        }

        .status-box.error {
            background: rgba(239, 68, 68, 0.2);
            border: 2px solid var(--danger);
        }

        .status-box.detecting {
            background: rgba(245, 158, 11, 0.2);
            border: 2px solid var(--warning);
        }

        .status-icon {
            font-size: 48px;
            margin-bottom: 8px;
        }

        .status-text {
            font-size: 18px;
            font-weight: 600;
        }

        .status-sub {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
        }

        .info-list {
            list-style: none;
        }

        .info-list li {
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .info-list li:last-child {
            border-bottom: none;
        }

        .info-list .label {
            color: var(--muted);
        }

        .info-list .value {
            font-weight: 600;
        }

        .log-section {
            margin-top: 20px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .log-title {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        #log {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 12px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        .mode-toggle {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .mode-btn {
            flex: 1;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            transition: all 0.2s;
        }

        .mode-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🎯 Absensi Wajah Realtime</h1>
        <p class="subtitle">Simulasi absensi dengan verifikasi wajah otomatis</p>

        <!-- Config Bar -->
        <div class="config-bar">
            <label>
                API URL
                <input type="text" id="apiUrl" value="http://localhost:8000" />
            </label>
            <label>
                Tenant ID
                <input type="number" id="tenantId" value="1" min="1" />
            </label>
            <label>
                User ID
                <input type="number" id="userId" value="1" min="1" />
            </label>
            <label>
                Threshold
                <input type="number" id="threshold" value="0.35" step="0.01" min="0.1" max="1" />
            </label>
            <label>
                Interval (ms)
                <input type="number" id="interval" value="2000" step="500" min="500" />
            </label>
        </div>

        <div class="main-area">
            <!-- Camera Section -->
            <div class="camera-section">
                <div class="video-container">
                    <video id="camera" autoplay playsinline muted></video>
                    <canvas id="overlay"></canvas>
                </div>
                <div class="camera-controls">
                    <button id="btnStart" class="btn-primary">▶️ Mulai Kamera</button>
                    <button id="btnAuto" class="btn-success" disabled>🔄 Auto Verifikasi</button>
                    <button id="btnManual" class="btn-ghost" disabled>📸 Verifikasi Sekali</button>
                    <button id="btnStop" class="btn-danger" disabled>⏹️ Stop</button>
                </div>
            </div>

            <!-- Status Panel -->
            <div class="status-panel">
                <div class="status-title">MODE</div>
                <div class="mode-toggle">
                    <div class="mode-btn active" data-mode="manual">Manual</div>
                    <div class="mode-btn" data-mode="auto">Auto</div>
                </div>

                <div class="status-title">STATUS VERIFIKASI</div>
                <div id="statusBox" class="status-box waiting">
                    <div class="status-icon">⏳</div>
                    <div class="status-text">Menunggu</div>
                    <div class="status-sub">Nyalakan kamera untuk mulai</div>
                </div>

                <div class="status-title">DETAIL</div>
                <ul class="info-list">
                    <li><span class="label">User Name</span><span class="value" id="infoName">-</span></li>
                    <li><span class="label">Distance</span><span class="value" id="infoDistance">-</span></li>
                    <li><span class="label">Threshold</span><span class="value" id="infoThreshold">-</span></li>
                    <li><span class="label">Waktu</span><span class="value" id="infoTime">-</span></li>
                </ul>
            </div>
        </div>

        <!-- Log Section -->
        <div class="log-section">
            <div class="log-title">📋 Activity Log</div>
            <div id="log">Siap menerima request...</div>
        </div>
    </div>

    <script>
        // Elements
        const video = document.getElementById('camera');
        const overlay = document.getElementById('overlay');
        const ctx = overlay.getContext('2d');
        const statusBox = document.getElementById('statusBox');
        const logEl = document.getElementById('log');

        // Buttons
        const btnStart = document.getElementById('btnStart');
        const btnAuto = document.getElementById('btnAuto');
        const btnManual = document.getElementById('btnManual');
        const btnStop = document.getElementById('btnStop');

        // State
        let stream = null;
        let autoInterval = null;
        let isAutoMode = false;

        // Helpers
        const getConfig = () => ({
            apiUrl: document.getElementById('apiUrl').value.replace(/\/$/, ''),
            tenantId: parseInt(document.getElementById('tenantId').value) || 1,
            userId: parseInt(document.getElementById('userId').value) || 1,
            threshold: parseFloat(document.getElementById('threshold').value) || 0.35,
            interval: parseInt(document.getElementById('interval').value) || 2000,
        });

        const log = (msg) => {
            const time = new Date().toLocaleTimeString();
            logEl.textContent = `[${time}] ${msg}\n` + logEl.textContent;
        };

        const setStatus = (type, icon, text, sub) => {
            statusBox.className = `status-box ${type}`;
            statusBox.innerHTML = `
        <div class="status-icon">${icon}</div>
        <div class="status-text">${text}</div>
        <div class="status-sub">${sub}</div>
      `;
        };

        const updateInfo = (data) => {
            document.getElementById('infoName').textContent = data.user_name || '-';
            document.getElementById('infoDistance').textContent = data.distance?.toFixed(4) || '-';
            document.getElementById('infoThreshold').textContent = data.threshold || '-';
            document.getElementById('infoTime').textContent = new Date().toLocaleTimeString();
        };

        const syncOverlay = () => {
            overlay.width = video.clientWidth;
            overlay.height = video.clientHeight;
        };

        const drawBbox = (bbox, verified) => {
            if (!bbox) return;
            syncOverlay();

            const scaleX = video.clientWidth / video.videoWidth;
            const scaleY = video.clientHeight / video.videoHeight;

            const x = bbox.left * scaleX;
            const y = bbox.top * scaleY;
            const w = (bbox.right - bbox.left) * scaleX;
            const h = (bbox.bottom - bbox.top) * scaleY;

            ctx.clearRect(0, 0, overlay.width, overlay.height);
            ctx.strokeStyle = verified ? '#22c55e' : '#ef4444';
            ctx.lineWidth = 4;
            ctx.strokeRect(x, y, w, h);

            // Label
            ctx.fillStyle = verified ? '#22c55e' : '#ef4444';
            ctx.font = 'bold 16px Space Grotesk';
            ctx.fillText(verified ? '✓ MATCH' : '✗ NOT MATCH', x, y - 10);
        };

        // Camera functions
        const startCamera = async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                video.srcObject = stream;
                await video.play();

                btnStart.disabled = true;
                btnAuto.disabled = false;
                btnManual.disabled = false;
                btnStop.disabled = false;

                log('Kamera aktif');
                setStatus('waiting', '📷', 'Kamera Aktif', 'Pilih mode verifikasi');
            } catch (err) {
                log('Error: ' + err.message);
                setStatus('error', '❌', 'Kamera Error', err.message);
            }
        };

        const stopCamera = () => {
            if (autoInterval) {
                clearInterval(autoInterval);
                autoInterval = null;
            }
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
                video.srcObject = null;
            }
            ctx.clearRect(0, 0, overlay.width, overlay.height);

            btnStart.disabled = false;
            btnAuto.disabled = true;
            btnManual.disabled = true;
            btnStop.disabled = true;

            log('Kamera dimatikan');
            setStatus('waiting', '⏳', 'Menunggu', 'Nyalakan kamera untuk mulai');
        };

        // Verification
        const captureFrame = () => {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            return new Promise(resolve => {
                canvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.9);
            });
        };

        const verify = async () => {
            const config = getConfig();
            const blob = await captureFrame();

            if (!blob) {
                log('Error: Gagal capture frame');
                return;
            }

            setStatus('detecting', '🔍', 'Memverifikasi...', 'Mengirim ke server');
            log(`Verifikasi: tenant=${config.tenantId}, user=${config.userId}`);

            const formData = new FormData();
            formData.append('tenant_id', config.tenantId);
            formData.append('user_id', config.userId);
            formData.append('threshold', config.threshold);
            formData.append('file', blob, 'capture.jpg');

            try {
                const response = await fetch(`${config.apiUrl}/verify`, {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();

                if (data.verified) {
                    setStatus('success', '✅', 'BERHASIL', `Halo, ${data.user_name}!`);
                    log(`✓ Verified: ${data.user_name} (distance: ${data.distance?.toFixed(4)})`);
                } else if (data.success === false) {
                    setStatus('error', '⚠️', 'GAGAL', data.message);
                    log(`✗ Failed: ${data.message}`);
                } else {
                    setStatus('error', '❌', 'BUKAN ORANGNYA', data.message);
                    log(`✗ Not match: distance=${data.distance?.toFixed(4)}`);
                }

                updateInfo(data);
                drawBbox(data.bbox, data.verified);

            } catch (err) {
                setStatus('error', '❌', 'Error', err.message);
                log('Error: ' + err.message);
            }
        };

        const startAuto = () => {
            if (autoInterval) return;
            const config = getConfig();

            log(`Auto mode: interval ${config.interval}ms`);
            verify(); // First run immediately
            autoInterval = setInterval(verify, config.interval);

            btnAuto.textContent = '⏸️ Pause Auto';
            btnAuto.className = 'btn-danger';
        };

        const stopAuto = () => {
            if (autoInterval) {
                clearInterval(autoInterval);
                autoInterval = null;
            }
            btnAuto.textContent = '🔄 Auto Verifikasi';
            btnAuto.className = 'btn-success';
            log('Auto mode stopped');
        };

        // Event listeners
        btnStart.onclick = startCamera;
        btnStop.onclick = stopCamera;
        btnManual.onclick = verify;

        btnAuto.onclick = () => {
            if (autoInterval) {
                stopAuto();
            } else {
                startAuto();
            }
        };

        // Mode toggle
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.onclick = () => {
                document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const mode = btn.dataset.mode;
                if (mode === 'auto' && stream) {
                    startAuto();
                } else {
                    stopAuto();
                }
            };
        });

        window.onresize = syncOverlay;
    </script>
</body>

</html>
