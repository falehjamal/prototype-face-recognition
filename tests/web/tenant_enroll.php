<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Enrollment | Multi-Tenant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0a0a0f;
            --card: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text: #f1f5f9;
            --muted: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --primary: #6366f1;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--muted);
            font-size: 14px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
        }

        @media (max-width: 900px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
        }

        .card-body {
            padding: 20px;
        }

        /* Mode Tabs */
        .mode-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .mode-tab {
            flex: 1;
            padding: 14px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .mode-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .mode-tab:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Media Section */
        .media-section {
            position: relative;
            aspect-ratio: 4/3;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        #video,
        #overlay,
        #previewImage {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #video {
            transform: scaleX(-1);
        }

        #overlay {
            pointer-events: none;
            transform: scaleX(-1);
        }

        #previewImage {
            display: none;
            object-fit: contain;
            background: #111;
        }

        .face-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 180px;
            height: 250px;
            border: 3px dashed rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            pointer-events: none;
            transition: all 0.3s;
        }

        .face-guide.ok {
            border-color: var(--success);
            border-style: solid;
        }

        .face-guide.warning {
            border-color: var(--warning);
        }

        .placeholder-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--muted);
            text-align: center;
        }

        .placeholder-text .icon {
            font-size: 48px;
            margin-bottom: 8px;
        }

        /* Form */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* Buttons */
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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

        .button-row {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }

        .button-row button {
            flex: 1;
        }

        /* File Input Styled */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-input-wrapper .file-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed var(--border);
            border-radius: 10px;
            color: var(--muted);
            transition: all 0.2s;
        }

        .file-input-wrapper:hover .file-label {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Validation Panel */
        .validation-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .validation-item:last-child {
            border-bottom: none;
        }

        .validation-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .validation-icon.ok {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .validation-icon.warning {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }

        .validation-icon.pending {
            background: rgba(100, 116, 139, 0.2);
        }

        .progress-bar {
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 6px;
        }

        .progress-fill {
            height: 100%;
            transition: width 0.3s, background 0.3s;
        }

        .progress-fill.ok {
            background: var(--success);
        }

        .progress-fill.warning {
            background: var(--warning);
        }

        /* Enrollment List */
        .enrollment-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .enrollment-tag {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            font-size: 13px;
        }

        .enrollment-tag small {
            opacity: 0.6;
        }

        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.ready {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .status-badge.waiting {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }

        .status-badge.error {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        /* Result */
        .result-box {
            padding: 16px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        /* Loading */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            border-radius: 12px;
        }

        .loading-overlay.show {
            display: flex;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .nav-links {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.2s;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>📸 Face Enrollment</h1>
            <p class="subtitle">Tambah wajah baru dengan upload foto atau kamera langsung</p>
        </header>

        <div class="main-grid">
            <!-- Left Column: Media + Form -->
            <div>
                <!-- Mode Selection -->
                <div class="card">
                    <div class="card-body">
                        <div class="mode-tabs">
                            <div class="mode-tab active" data-mode="upload">📁 Upload Foto</div>
                            <div class="mode-tab" data-mode="camera">📷 Kamera</div>
                        </div>

                        <!-- Media Container -->
                        <div class="media-section">
                            <video id="video" autoplay playsinline muted style="display:none"></video>
                            <canvas id="overlay" style="display:none"></canvas>
                            <div id="faceGuide" class="face-guide" style="display:none"></div>
                            <img id="previewImage" alt="Preview">

                            <div id="placeholder" class="placeholder-text">
                                <div class="icon">📁</div>
                                <div>Pilih foto untuk preview</div>
                            </div>

                            <div id="loadingOverlay" class="loading-overlay">
                                <div class="spinner"></div>
                                <span>Menyimpan...</span>
                            </div>
                        </div>

                        <!-- Upload Mode -->
                        <div id="uploadMode">
                            <div class="file-input-wrapper">
                                <input type="file" id="imageFile" accept="image/*">
                                <div class="file-label">
                                    <span>📁</span>
                                    <span id="fileLabel">Pilih gambar...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Camera Mode -->
                        <div id="cameraMode" style="display:none">
                            <div class="button-row">
                                <button id="btnStartCamera" class="btn-primary">▶️ Mulai Kamera</button>
                                <button id="btnCapture" class="btn-success" disabled>📸 Capture</button>
                                <button id="btnStopCamera" class="btn-ghost" disabled>⏹️ Stop</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Form -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Data Enrollment</span>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>API URL</label>
                                <input type="text" id="apiUrl" value="http://localhost:8000">
                            </div>
                            <div class="form-group">
                                <label>Tenant ID</label>
                                <input type="number" id="tenantId" value="1" min="1">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>User ID</label>
                                <input type="number" id="userId" value="1" min="1">
                            </div>
                            <div class="form-group">
                                <label>Nama / Label</label>
                                <input type="text" id="enrollName" placeholder="Contoh: John Doe">
                            </div>
                        </div>

                        <div class="button-row">
                            <button id="btnEnroll" class="btn-success" disabled>💾 Simpan Enrollment</button>
                            <button id="btnRefresh" class="btn-ghost">🔄 Refresh</button>
                        </div>
                    </div>
                </div>

                <!-- Enrollment List -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Daftar Enrollment</span>
                        <span id="listStatus" class="status-badge waiting">Memuat...</span>
                    </div>
                    <div class="card-body">
                        <div id="enrollmentList" class="enrollment-list">
                            <span class="enrollment-tag">Memuat...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Validation -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Validasi MediaPipe</span>
                        <span id="statusBadge" class="status-badge waiting">Menunggu</span>
                    </div>
                    <div class="card-body" id="validationPanel">
                        <div class="validation-item">
                            <div class="validation-icon pending" id="iconFace">👤</div>
                            <div style="flex:1">
                                <div>Deteksi Wajah</div>
                                <div style="font-size:11px;color:var(--muted)" id="valueFace">Menunggu gambar...</div>
                            </div>
                        </div>
                        <div class="validation-item">
                            <div class="validation-icon pending" id="iconDistance">📏</div>
                            <div style="flex:1">
                                <div>Ukuran Wajah</div>
                                <div style="font-size:11px;color:var(--muted)" id="valueDistance">-</div>
                                <div class="progress-bar">
                                    <div id="barDistance" class="progress-fill" style="width:0%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="validation-item">
                            <div class="validation-icon pending" id="iconPosition">🎯</div>
                            <div style="flex:1">
                                <div>Posisi Wajah</div>
                                <div style="font-size:11px;color:var(--muted)" id="valuePosition">-</div>
                            </div>
                        </div>
                        <div class="validation-item">
                            <div class="validation-icon pending" id="iconLight">💡</div>
                            <div style="flex:1">
                                <div>Pencahayaan</div>
                                <div style="font-size:11px;color:var(--muted)" id="valueLight">-</div>
                                <div class="progress-bar">
                                    <div id="barLight" class="progress-fill" style="width:0%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="validation-item" style="margin-top:10px;padding-top:14px;border-top:2px solid var(--border)">
                            <div class="validation-icon pending" id="iconOverall">⏳</div>
                            <div style="flex:1">
                                <div style="font-weight:600">Status Keseluruhan</div>
                                <div style="font-size:11px;color:var(--muted)" id="valueOverall">Menunggu validasi...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Response Log</span>
                    </div>
                    <div class="card-body">
                        <div id="result" class="result-box">{ "status": "menunggu" }</div>
                    </div>
                </div>

                <div class="nav-links">
                    <a href="mediapipe_verify.php">🎯 Verifikasi</a>
                    <a href="tenant_deteksi.php">🔍 Identifikasi</a>
                </div>
            </div>
        </div>
    </div>

    <!-- MediaPipe -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/face_detection.js" crossorigin="anonymous"></script>

    <script>
        // Elements
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const ctx = overlay.getContext('2d');
        const previewImage = document.getElementById('previewImage');
        const faceGuide = document.getElementById('faceGuide');
        const placeholder = document.getElementById('placeholder');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const imageFile = document.getElementById('imageFile');

        // State
        let currentMode = 'upload';
        let stream = null;
        let faceDetection = null;
        let camera = null;
        let lastValidation = {
            allPassed: false
        };
        let capturedBlob = null;

        // Thresholds
        const THRESHOLDS = {
            minFaceRatio: 0.15,
            maxFaceRatio: 0.70,
            maxOffsetX: 0.20,
            maxOffsetY: 0.20,
            minBrightness: 60,
            maxBrightness: 220,
        };

        // =========================================
        // Mode Switching
        // =========================================

        document.querySelectorAll('.mode-tab').forEach(tab => {
            tab.onclick = () => {
                document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentMode = tab.dataset.mode;

                if (currentMode === 'upload') {
                    document.getElementById('uploadMode').style.display = 'block';
                    document.getElementById('cameraMode').style.display = 'none';
                    stopCamera();
                    placeholder.querySelector('.icon').textContent = '📁';
                    placeholder.querySelector('div:last-child').textContent = 'Pilih foto untuk preview';
                } else {
                    document.getElementById('uploadMode').style.display = 'none';
                    document.getElementById('cameraMode').style.display = 'block';
                    previewImage.style.display = 'none';
                    placeholder.querySelector('.icon').textContent = '📷';
                    placeholder.querySelector('div:last-child').textContent = 'Klik "Mulai Kamera"';
                    placeholder.style.display = 'flex';
                }

                resetValidation();
                capturedBlob = null;
                updateEnrollButton();
            };
        });

        // =========================================
        // Upload Mode
        // =========================================

        imageFile.onchange = async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            document.getElementById('fileLabel').textContent = file.name;

            // Show preview
            const reader = new FileReader();
            reader.onload = (ev) => {
                previewImage.src = ev.target.result;
                previewImage.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);

            // Validate with MediaPipe
            await validateImageFile(file);
        };

        async function validateImageFile(file) {
            initMediaPipe();

            const img = new Image();
            img.onload = async () => {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                canvas.getContext('2d').drawImage(img, 0, 0);

                await faceDetection.send({
                    image: canvas
                });
            };
            img.src = URL.createObjectURL(file);
        }

        // =========================================
        // Camera Mode
        // =========================================

        document.getElementById('btnStartCamera').onclick = startCamera;
        document.getElementById('btnStopCamera').onclick = stopCamera;
        document.getElementById('btnCapture').onclick = captureFrame;

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480,
                        facingMode: 'user'
                    }
                });
                video.srcObject = stream;
                await video.play();

                video.style.display = 'block';
                overlay.style.display = 'block';
                faceGuide.style.display = 'block';
                placeholder.style.display = 'none';
                previewImage.style.display = 'none';

                initMediaPipe();

                camera = new Camera(video, {
                    onFrame: async () => {
                        if (faceDetection && video.videoWidth) {
                            await faceDetection.send({
                                image: video
                            });
                        }
                    },
                    width: 640,
                    height: 480
                });
                camera.start();

                document.getElementById('btnStartCamera').disabled = true;
                document.getElementById('btnCapture').disabled = false;
                document.getElementById('btnStopCamera').disabled = false;

            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        function stopCamera() {
            if (camera) {
                camera.stop();
                camera = null;
            }
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }

            video.style.display = 'none';
            overlay.style.display = 'none';
            faceGuide.style.display = 'none';
            placeholder.style.display = 'flex';

            ctx.clearRect(0, 0, overlay.width, overlay.height);

            document.getElementById('btnStartCamera').disabled = false;
            document.getElementById('btnCapture').disabled = true;
            document.getElementById('btnStopCamera').disabled = true;

            resetValidation();
        }

        async function captureFrame() {
            if (!lastValidation.allPassed) {
                alert('Validasi belum terpenuhi! Pastikan semua cek hijau.');
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            capturedBlob = await new Promise(resolve => {
                canvas.toBlob(resolve, 'image/jpeg', 0.9);
            });

            // Show captured frame as preview
            previewImage.src = canvas.toDataURL('image/jpeg');
            previewImage.style.display = 'block';
            video.style.display = 'none';
            overlay.style.display = 'none';
            faceGuide.style.display = 'none';

            stopCamera();
            updateEnrollButton();

            setResult({
                status: 'captured',
                message: 'Foto siap untuk disimpan'
            });
        }

        // =========================================
        // MediaPipe
        // =========================================

        function initMediaPipe() {
            if (faceDetection) return;

            faceDetection = new FaceDetection({
                locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/${file}`
            });

            faceDetection.setOptions({
                model: 'short',
                minDetectionConfidence: 0.5
            });

            faceDetection.onResults(onFaceDetectionResults);
        }

        function onFaceDetectionResults(results) {
            const isCamera = currentMode === 'camera' && video.videoWidth;

            if (isCamera) {
                overlay.width = video.clientWidth;
                overlay.height = video.clientHeight;
                ctx.clearRect(0, 0, overlay.width, overlay.height);
            }

            const detection = results.detections[0] || null;
            const width = isCamera ? video.videoWidth : (results.image?.width || 640);
            const height = isCamera ? video.videoHeight : (results.image?.height || 480);

            // Calculate brightness
            let brightness = 140; // Default for uploaded images
            if (isCamera) {
                brightness = calculateBrightness(video);
            }

            // Validate
            lastValidation = validateFace(detection, width, height, brightness);
            updateValidationUI(lastValidation);

            // Draw bbox on camera
            if (isCamera && detection) {
                const bbox = detection.boundingBox;
                const x = bbox.xCenter * overlay.width - (bbox.width * overlay.width / 2);
                const y = bbox.yCenter * overlay.height - (bbox.height * overlay.height / 2);
                const w = bbox.width * overlay.width;
                const h = bbox.height * overlay.height;

                ctx.strokeStyle = lastValidation.allPassed ? '#10b981' : '#f59e0b';
                ctx.lineWidth = 3;
                ctx.strokeRect(x, y, w, h);
            }

            // Update face guide
            if (isCamera) {
                faceGuide.className = 'face-guide ' + (
                    detection ? (lastValidation.allPassed ? 'ok' : 'warning') : ''
                );
            }

            // Update enroll button for upload mode
            if (currentMode === 'upload') {
                updateEnrollButton();
            }
        }

        function validateFace(detection, frameWidth, frameHeight, brightness) {
            const result = {
                faceDetected: false,
                distance: {
                    ok: false,
                    value: 0,
                    message: ''
                },
                position: {
                    ok: false,
                    message: ''
                },
                lighting: {
                    ok: false,
                    value: brightness,
                    message: ''
                },
                allPassed: false
            };

            if (!detection) {
                result.distance.message = 'Tidak ada wajah';
                result.position.message = 'Tidak ada wajah';
                result.lighting.message = 'Tidak ada wajah';
                return result;
            }

            result.faceDetected = true;
            const bbox = detection.boundingBox;

            // Distance
            const faceRatio = bbox.width;
            result.distance.value = faceRatio;

            if (faceRatio < THRESHOLDS.minFaceRatio) {
                result.distance.message = `Terlalu kecil (${(faceRatio*100).toFixed(0)}%)`;
            } else if (faceRatio > THRESHOLDS.maxFaceRatio) {
                result.distance.message = `Terlalu besar (${(faceRatio*100).toFixed(0)}%)`;
            } else {
                result.distance.ok = true;
                result.distance.message = `OK (${(faceRatio*100).toFixed(0)}%)`;
            }

            // Position
            const offsetX = Math.abs(bbox.xCenter - 0.5);
            const offsetY = Math.abs(bbox.yCenter - 0.5);

            if (offsetX > THRESHOLDS.maxOffsetX || offsetY > THRESHOLDS.maxOffsetY) {
                result.position.message = 'Tidak di tengah';
            } else {
                result.position.ok = true;
                result.position.message = 'Posisi OK';
            }

            // Lighting
            if (brightness < THRESHOLDS.minBrightness) {
                result.lighting.message = `Terlalu gelap (${brightness.toFixed(0)})`;
            } else if (brightness > THRESHOLDS.maxBrightness) {
                result.lighting.message = `Terlalu terang (${brightness.toFixed(0)})`;
            } else {
                result.lighting.ok = true;
                result.lighting.message = `OK (${brightness.toFixed(0)})`;
            }

            result.allPassed = result.distance.ok && result.position.ok && result.lighting.ok;
            return result;
        }

        function calculateBrightness(source) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = 100;
            canvas.height = 75;
            ctx.drawImage(source, 0, 0, 100, 75);

            const data = ctx.getImageData(0, 0, 100, 75).data;
            let sum = 0;
            for (let i = 0; i < data.length; i += 4) {
                sum += (data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
            }
            return sum / (data.length / 4);
        }

        // =========================================
        // UI Updates
        // =========================================

        function updateValidationUI(v) {
            setValidation('Face', v.faceDetected, v.faceDetected ? 'Wajah terdeteksi' : 'Tidak ada wajah');
            setValidation('Distance', v.distance.ok, v.distance.message);
            setValidation('Position', v.position.ok, v.position.message);
            setValidation('Light', v.lighting.ok, v.lighting.message);
            setValidation('Overall', v.allPassed, v.allPassed ? 'Siap untuk enroll!' : 'Perbaiki kondisi');

            // Progress bars
            document.getElementById('barDistance').style.width = `${Math.min(100, v.distance.value * 150)}%`;
            document.getElementById('barDistance').className = `progress-fill ${v.distance.ok ? 'ok' : 'warning'}`;

            document.getElementById('barLight').style.width = `${Math.min(100, (v.lighting.value / 255) * 100)}%`;
            document.getElementById('barLight').className = `progress-fill ${v.lighting.ok ? 'ok' : 'warning'}`;

            // Status badge
            const badge = document.getElementById('statusBadge');
            if (v.allPassed) {
                badge.textContent = 'Siap';
                badge.className = 'status-badge ready';
            } else if (v.faceDetected) {
                badge.textContent = 'Perbaiki';
                badge.className = 'status-badge waiting';
            } else {
                badge.textContent = 'No Face';
                badge.className = 'status-badge error';
            }
        }

        function setValidation(name, ok, text) {
            document.getElementById(`icon${name}`).className = `validation-icon ${ok ? 'ok' : (text.includes('Tidak') ? 'pending' : 'warning')}`;
            document.getElementById(`icon${name}`).textContent = ok ? '✓' : (name === 'Face' ? '👤' : '⚠');
            document.getElementById(`value${name}`).textContent = text;
        }

        function resetValidation() {
            lastValidation = {
                allPassed: false
            };
            ['Face', 'Distance', 'Position', 'Light', 'Overall'].forEach(name => {
                document.getElementById(`icon${name}`).className = 'validation-icon pending';
                document.getElementById(`value${name}`).textContent = 'Menunggu...';
            });
            document.getElementById('barDistance').style.width = '0%';
            document.getElementById('barLight').style.width = '0%';
            document.getElementById('statusBadge').textContent = 'Menunggu';
            document.getElementById('statusBadge').className = 'status-badge waiting';
        }

        function updateEnrollButton() {
            const hasImage = currentMode === 'upload' ? imageFile.files.length > 0 : capturedBlob !== null;
            const hasName = document.getElementById('enrollName').value.trim().length > 0;
            document.getElementById('btnEnroll').disabled = !hasImage || !hasName || !lastValidation.allPassed;
        }

        function setResult(data) {
            document.getElementById('result').textContent = JSON.stringify(data, null, 2);
        }

        // =========================================
        // Enrollment
        // =========================================

        document.getElementById('enrollName').oninput = updateEnrollButton;

        document.getElementById('btnEnroll').onclick = async () => {
            const apiUrl = document.getElementById('apiUrl').value.replace(/\/$/, '');
            const tenantId = document.getElementById('tenantId').value;
            const userId = document.getElementById('userId').value;
            const name = document.getElementById('enrollName').value.trim();

            if (!name) {
                alert('Masukkan nama untuk enrollment');
                return;
            }

            loadingOverlay.classList.add('show');

            const formData = new FormData();
            formData.append('tenant_id', tenantId);
            formData.append('user_id', userId);
            formData.append('name', name);

            if (currentMode === 'upload') {
                formData.append('file', imageFile.files[0]);
            } else {
                formData.append('file', capturedBlob, 'capture.jpg');
            }

            try {
                const response = await fetch(`${apiUrl}/enroll`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                setResult(data);

                if (response.ok) {
                    alert('Enrollment berhasil!');
                    loadEnrollments();
                    // Reset
                    imageFile.value = '';
                    document.getElementById('fileLabel').textContent = 'Pilih gambar...';
                    previewImage.style.display = 'none';
                    placeholder.style.display = 'flex';
                    capturedBlob = null;
                    resetValidation();
                    updateEnrollButton();
                }
            } catch (err) {
                setResult({
                    error: err.message
                });
            }

            loadingOverlay.classList.remove('show');
        };

        // =========================================
        // Enrollment List
        // =========================================

        async function loadEnrollments() {
            const apiUrl = document.getElementById('apiUrl').value.replace(/\/$/, '');
            const tenantId = document.getElementById('tenantId').value;

            document.getElementById('listStatus').textContent = 'Memuat...';

            try {
                const response = await fetch(`${apiUrl}/enrollments/${tenantId}`);
                const data = await response.json();

                const list = data.enrollments || [];
                const html = list.length ?
                    list.map(e => `<span class="enrollment-tag">${e.name} <small>(ID:${e.id})</small></span>`).join('') :
                    '<span class="enrollment-tag">Belum ada enrollment</span>';

                document.getElementById('enrollmentList').innerHTML = html;
                document.getElementById('listStatus').textContent = `${list.length} items`;
                document.getElementById('listStatus').className = 'status-badge ready';

            } catch (err) {
                document.getElementById('enrollmentList').innerHTML = '<span class="enrollment-tag">Gagal memuat</span>';
                document.getElementById('listStatus').textContent = 'Error';
                document.getElementById('listStatus').className = 'status-badge error';
            }
        }

        document.getElementById('btnRefresh').onclick = loadEnrollments;
        document.getElementById('tenantId').onchange = loadEnrollments;

        // Init
        loadEnrollments();
    </script>
</body>

</html>
