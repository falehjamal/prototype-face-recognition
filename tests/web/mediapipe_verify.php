<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Verification + Blink | MediaPipe</title>
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

        .video-section {
            position: relative;
            aspect-ratio: 4/3;
            background: #000;
        }

        #video,
        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        #video {
            object-fit: cover;
            transform: scaleX(-1);
        }

        #overlay {
            pointer-events: none;
            transform: scaleX(-1);
        }

        .face-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 280px;
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

        .blink-instruction {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            display: none;
            animation: pulse 1s infinite;
        }

        .blink-instruction.show {
            display: block;
        }

        .blink-instruction.success {
            background: var(--success);
            animation: none;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .controls {
            padding: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
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

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .config-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px;
            background: rgba(0, 0, 0, 0.2);
        }

        .config-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .config-item label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
        }

        .config-item input {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 13px;
            width: 100px;
        }

        .validation-panel {
            padding: 20px;
        }

        .validation-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .validation-item:last-child {
            border-bottom: none;
        }

        .validation-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .validation-icon.pending {
            background: rgba(100, 116, 139, 0.2);
        }

        .validation-icon.ok {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .validation-icon.warning {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }

        .validation-icon.error {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        .validation-content {
            flex: 1;
        }

        .validation-label {
            font-weight: 500;
            font-size: 13px;
        }

        .validation-value {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
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

        .result-panel {
            padding: 24px;
            text-align: center;
            display: none;
        }

        .result-panel.show {
            display: block;
        }

        .result-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 16px;
        }

        .result-icon.success {
            background: rgba(16, 185, 129, 0.2);
        }

        .result-icon.error {
            background: rgba(239, 68, 68, 0.2);
        }

        .result-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .result-message {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 20px;
        }

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

        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
        }

        .loading-overlay.show {
            display: flex;
        }

        .spinner {
            width: 48px;
            height: 48px;
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

        /* Eye indicator */
        .eye-status {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 8px;
        }

        .eye-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
        }

        .eye-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--muted);
            transition: all 0.1s;
        }

        .eye-dot.open {
            background: var(--success);
        }

        .eye-dot.closed {
            background: var(--warning);
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>👁️ Face Verification + Blink Detection</h1>
            <p class="subtitle">Verifikasi liveness dengan kedipan mata</p>
        </header>

        <div class="main-grid">
            <!-- Camera Section -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Kamera</span>
                    <span id="statusBadge" class="status-badge waiting">Menunggu</span>
                </div>

                <div class="video-section">
                    <video id="video" autoplay playsinline muted></video>
                    <canvas id="overlay"></canvas>
                    <div id="faceGuide" class="face-guide"></div>

                    <div id="blinkInstruction" class="blink-instruction">
                        👁️ Kedipkan mata Anda
                    </div>

                    <div id="loadingOverlay" class="loading-overlay">
                        <div class="spinner"></div>
                        <span>Memverifikasi...</span>
                    </div>
                </div>

                <div class="config-row">
                    <div class="config-item">
                        <label>API URL</label>
                        <input type="text" id="apiUrl" value="http://localhost:8000" style="width:160px">
                    </div>
                    <div class="config-item">
                        <label>Tenant ID</label>
                        <input type="number" id="tenantId" value="1" min="1">
                    </div>
                    <div class="config-item">
                        <label>User ID</label>
                        <input type="number" id="userId" value="1" min="1">
                    </div>
                    <div class="config-item">
                        <label>Threshold</label>
                        <input type="number" id="threshold" value="0.35" step="0.01">
                    </div>
                </div>

                <div class="controls">
                    <button id="btnStart" class="btn-primary">▶️ Mulai Kamera</button>
                    <button id="btnVerify" class="btn-success" disabled>✓ Verifikasi</button>
                    <button id="btnStop" class="btn-ghost" disabled>⏹️ Stop</button>
                </div>
            </div>

            <!-- Validation Panel -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Validasi Liveness</span>
                </div>

                <div class="validation-panel" id="validationPanel">
                    <!-- Face Detection -->
                    <div class="validation-item">
                        <div class="validation-icon pending" id="iconFace">👤</div>
                        <div class="validation-content">
                            <div class="validation-label">Deteksi Wajah</div>
                            <div class="validation-value" id="valueFace">Menunggu kamera...</div>
                        </div>
                    </div>

                    <!-- Face Size -->
                    <div class="validation-item">
                        <div class="validation-icon pending" id="iconDistance">📏</div>
                        <div class="validation-content">
                            <div class="validation-label">Ukuran Wajah</div>
                            <div class="validation-value" id="valueDistance">-</div>
                            <div class="progress-bar">
                                <div id="barDistance" class="progress-fill" style="width:0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Position -->
                    <div class="validation-item">
                        <div class="validation-icon pending" id="iconPosition">🎯</div>
                        <div class="validation-content">
                            <div class="validation-label">Posisi Wajah</div>
                            <div class="validation-value" id="valuePosition">-</div>
                        </div>
                    </div>

                    <!-- Lighting -->
                    <div class="validation-item">
                        <div class="validation-icon pending" id="iconLight">💡</div>
                        <div class="validation-content">
                            <div class="validation-label">Pencahayaan</div>
                            <div class="validation-value" id="valueLight">-</div>
                            <div class="progress-bar">
                                <div id="barLight" class="progress-fill" style="width:0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- BLINK DETECTION -->
                    <div class="validation-item">
                        <div class="validation-icon pending" id="iconBlink">👁️</div>
                        <div class="validation-content">
                            <div class="validation-label">Blink Detection</div>
                            <div class="validation-value" id="valueBlink">Menunggu kedipan...</div>
                            <div class="eye-status">
                                <div class="eye-indicator">
                                    <div class="eye-dot" id="leftEyeDot"></div>
                                    <span>Kiri</span>
                                </div>
                                <div class="eye-indicator">
                                    <div class="eye-dot" id="rightEyeDot"></div>
                                    <span>Kanan</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overall -->
                    <div class="validation-item" style="margin-top:12px;padding-top:16px;border-top:2px solid var(--border)">
                        <div class="validation-icon pending" id="iconOverall">⏳</div>
                        <div class="validation-content">
                            <div class="validation-label" style="font-size:14px">Status Keseluruhan</div>
                            <div class="validation-value" id="valueOverall">Menunggu validasi...</div>
                        </div>
                    </div>
                </div>

                <!-- Result Panel -->
                <div class="result-panel" id="resultPanel">
                    <div class="result-icon" id="resultIcon">✓</div>
                    <div class="result-title" id="resultTitle">Verifikasi Berhasil</div>
                    <div class="result-message" id="resultMessage">Halo, John Doe!</div>
                    <button id="btnRetry" class="btn-primary" style="margin:0 auto">🔄 Coba Lagi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MediaPipe Face Mesh (for blink detection) -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>

    <script>
        // Elements
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const ctx = overlay.getContext('2d');
        const faceGuide = document.getElementById('faceGuide');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const blinkInstruction = document.getElementById('blinkInstruction');
        const validationPanel = document.getElementById('validationPanel');
        const resultPanel = document.getElementById('resultPanel');

        const btnStart = document.getElementById('btnStart');
        const btnVerify = document.getElementById('btnVerify');
        const btnStop = document.getElementById('btnStop');
        const btnRetry = document.getElementById('btnRetry');

        // State
        let stream = null;
        let faceMesh = null;
        let camera = null;
        let lastValidation = {
            allPassed: false
        };

        // Blink detection state
        let blinkState = {
            detected: false,
            blinkCount: 0,
            lastEAR: 1.0,
            eyesClosed: false,
            requiredBlinks: 1,
            blinkHistory: []
        };

        // Thresholds
        const THRESHOLDS = {
            minFaceRatio: 0.18,
            maxFaceRatio: 0.65,
            maxOffsetX: 0.18,
            maxOffsetY: 0.18,
            minBrightness: 70,
            maxBrightness: 210,
            earThreshold: 0.21, // Eye Aspect Ratio threshold for blink
            earClosedFrames: 2, // Frames eyes must be closed
            earOpenFrames: 2 // Frames eyes must be open after
        };

        // Face Mesh landmark indices for eyes
        // Left eye: 362, 385, 387, 263, 373, 380
        // Right eye: 33, 160, 158, 133, 153, 144
        const LEFT_EYE = [362, 385, 387, 263, 373, 380];
        const RIGHT_EYE = [33, 160, 158, 133, 153, 144];

        // =========================================
        // Eye Aspect Ratio (EAR) Calculation
        // =========================================

        function distance(p1, p2) {
            return Math.sqrt(Math.pow(p1.x - p2.x, 2) + Math.pow(p1.y - p2.y, 2));
        }

        function calculateEAR(landmarks, eyeIndices) {
            // Get eye landmarks
            const p = eyeIndices.map(i => landmarks[i]);

            // Vertical distances
            const v1 = distance(p[1], p[5]);
            const v2 = distance(p[2], p[4]);

            // Horizontal distance
            const h = distance(p[0], p[3]);

            // Eye Aspect Ratio
            return (v1 + v2) / (2.0 * h);
        }

        function detectBlink(landmarks) {
            if (!landmarks || landmarks.length < 468) return null;

            const leftEAR = calculateEAR(landmarks, LEFT_EYE);
            const rightEAR = calculateEAR(landmarks, RIGHT_EYE);
            const avgEAR = (leftEAR + rightEAR) / 2;

            const leftOpen = leftEAR > THRESHOLDS.earThreshold;
            const rightOpen = rightEAR > THRESHOLDS.earThreshold;
            const eyesOpen = avgEAR > THRESHOLDS.earThreshold;

            // Update eye indicators
            document.getElementById('leftEyeDot').className = `eye-dot ${leftOpen ? 'open' : 'closed'}`;
            document.getElementById('rightEyeDot').className = `eye-dot ${rightOpen ? 'open' : 'closed'}`;

            // Blink detection state machine
            blinkState.blinkHistory.push(eyesOpen);
            if (blinkState.blinkHistory.length > 10) {
                blinkState.blinkHistory.shift();
            }

            // Detect blink: eyes were open -> closed -> open
            if (!blinkState.detected) {
                const history = blinkState.blinkHistory;
                const len = history.length;

                if (len >= 5) {
                    // Check pattern: open, open, closed, open, open
                    const wasOpen = history.slice(0, 2).every(v => v);
                    const wasClosed = history.slice(2, 4).some(v => !v);
                    const isOpen = history.slice(-2).every(v => v);

                    if (wasOpen && wasClosed && isOpen) {
                        blinkState.blinkCount++;
                        blinkState.blinkHistory = [];

                        if (blinkState.blinkCount >= blinkState.requiredBlinks) {
                            blinkState.detected = true;
                            blinkInstruction.textContent = '✓ Kedipan Terdeteksi!';
                            blinkInstruction.classList.add('success');
                        }
                    }
                }
            }

            return {
                leftEAR,
                rightEAR,
                avgEAR,
                eyesOpen,
                blinkDetected: blinkState.detected,
                blinkCount: blinkState.blinkCount
            };
        }

        // =========================================
        // MediaPipe Face Mesh Setup
        // =========================================

        function initFaceMesh() {
            faceMesh = new FaceMesh({
                locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
            });

            faceMesh.setOptions({
                maxNumFaces: 1,
                refineLandmarks: true,
                minDetectionConfidence: 0.5,
                minTrackingConfidence: 0.5
            });

            faceMesh.onResults(onFaceMeshResults);
        }

        function onFaceMeshResults(results) {
            if (!video.videoWidth) return;

            overlay.width = video.clientWidth;
            overlay.height = video.clientHeight;
            ctx.clearRect(0, 0, overlay.width, overlay.height);

            const brightness = calculateBrightness(video);
            const face = results.multiFaceLandmarks?.[0] || null;

            let blinkResult = null;

            if (face) {
                // Draw face mesh (simplified - just contour)
                ctx.strokeStyle = 'rgba(99, 102, 241, 0.5)';
                ctx.lineWidth = 1;

                // Draw eye contours
                drawEyeContour(face, LEFT_EYE);
                drawEyeContour(face, RIGHT_EYE);

                // Blink detection
                blinkResult = detectBlink(face);
            }

            // Validate face
            lastValidation = validateFace(face, video.videoWidth, video.videoHeight, brightness, blinkResult);
            updateValidationUI(lastValidation);

            // Update face guide
            faceGuide.className = 'face-guide ' + (
                face ? (lastValidation.allPassed ? 'ok' : 'warning') : ''
            );

            // Show blink instruction when other validations pass
            if (lastValidation.basicPassed && !blinkState.detected) {
                blinkInstruction.classList.add('show');
            } else if (blinkState.detected) {
                blinkInstruction.classList.add('show');
            } else {
                blinkInstruction.classList.remove('show');
                blinkInstruction.classList.remove('success');
            }
        }

        function drawEyeContour(landmarks, indices) {
            ctx.beginPath();
            indices.forEach((idx, i) => {
                const x = landmarks[idx].x * overlay.width;
                const y = landmarks[idx].y * overlay.height;
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });
            ctx.closePath();
            ctx.stroke();
        }

        // =========================================
        // Validation
        // =========================================

        function validateFace(landmarks, frameWidth, frameHeight, brightness, blinkResult) {
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
                blink: {
                    ok: false,
                    count: 0,
                    message: ''
                },
                basicPassed: false,
                allPassed: false
            };

            if (!landmarks || landmarks.length < 468) {
                result.distance.message = 'Tidak ada wajah';
                result.position.message = 'Tidak ada wajah';
                result.lighting.message = 'Tidak ada wajah';
                result.blink.message = 'Tidak ada wajah';
                return result;
            }

            result.faceDetected = true;

            // Calculate face bounding box from landmarks
            let minX = 1,
                maxX = 0,
                minY = 1,
                maxY = 0;
            landmarks.forEach(p => {
                minX = Math.min(minX, p.x);
                maxX = Math.max(maxX, p.x);
                minY = Math.min(minY, p.y);
                maxY = Math.max(maxY, p.y);
            });

            const faceWidth = maxX - minX;
            const faceHeight = maxY - minY;
            const faceCenterX = (minX + maxX) / 2;
            const faceCenterY = (minY + maxY) / 2;

            // Distance (face size)
            result.distance.value = faceWidth;
            if (faceWidth < THRESHOLDS.minFaceRatio) {
                result.distance.message = `Terlalu jauh (${(faceWidth*100).toFixed(0)}%)`;
            } else if (faceWidth > THRESHOLDS.maxFaceRatio) {
                result.distance.message = `Terlalu dekat (${(faceWidth*100).toFixed(0)}%)`;
            } else {
                result.distance.ok = true;
                result.distance.message = `OK (${(faceWidth*100).toFixed(0)}%)`;
            }

            // Position
            const offsetX = Math.abs(faceCenterX - 0.5);
            const offsetY = Math.abs(faceCenterY - 0.5);
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

            // Blink
            if (blinkResult) {
                result.blink.count = blinkResult.blinkCount;
                if (blinkResult.blinkDetected) {
                    result.blink.ok = true;
                    result.blink.message = `✓ Kedipan terdeteksi (${blinkResult.blinkCount}x)`;
                } else {
                    result.blink.message = `Menunggu kedipan... (EAR: ${blinkResult.avgEAR.toFixed(2)})`;
                }
            } else {
                result.blink.message = 'Analisis mata...';
            }

            result.basicPassed = result.distance.ok && result.position.ok && result.lighting.ok;
            result.allPassed = result.basicPassed && result.blink.ok;

            return result;
        }

        function calculateBrightness(source) {
            const canvas = document.createElement('canvas');
            const tempCtx = canvas.getContext('2d');
            canvas.width = 100;
            canvas.height = 75;
            tempCtx.drawImage(source, 0, 0, 100, 75);

            const data = tempCtx.getImageData(0, 0, 100, 75).data;
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
            setValidation('Blink', v.blink.ok, v.blink.message);
            setValidation('Overall', v.allPassed, v.allPassed ? 'Siap untuk verifikasi!' : 'Perbaiki kondisi');

            // Progress bars
            document.getElementById('barDistance').style.width = `${Math.min(100, v.distance.value * 150)}%`;
            document.getElementById('barDistance').className = `progress-fill ${v.distance.ok ? 'ok' : 'warning'}`;

            document.getElementById('barLight').style.width = `${Math.min(100, (v.lighting.value / 255) * 100)}%`;
            document.getElementById('barLight').className = `progress-fill ${v.lighting.ok ? 'ok' : 'warning'}`;

            // Status badge & verify button
            const badge = document.getElementById('statusBadge');
            if (v.allPassed) {
                badge.textContent = 'Siap';
                badge.className = 'status-badge ready';
                btnVerify.disabled = false;
            } else if (v.faceDetected) {
                badge.textContent = v.basicPassed ? 'Kedipkan' : 'Perbaiki';
                badge.className = 'status-badge waiting';
                btnVerify.disabled = true;
            } else {
                badge.textContent = 'No Face';
                badge.className = 'status-badge error';
                btnVerify.disabled = true;
            }
        }

        function setValidation(name, ok, text) {
            const icon = document.getElementById(`icon${name}`);
            icon.className = `validation-icon ${ok ? 'ok' : (text.includes('Tidak') ? 'pending' : 'warning')}`;
            icon.textContent = ok ? '✓' : (name === 'Blink' ? '👁️' : '⚠');
            document.getElementById(`value${name}`).textContent = text;
        }

        // =========================================
        // Camera Functions
        // =========================================

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

                // Reset blink state
                blinkState = {
                    detected: false,
                    blinkCount: 0,
                    lastEAR: 1.0,
                    eyesClosed: false,
                    requiredBlinks: 1,
                    blinkHistory: []
                };
                blinkInstruction.textContent = '👁️ Kedipkan mata Anda';
                blinkInstruction.classList.remove('success', 'show');

                initFaceMesh();

                camera = new Camera(video, {
                    onFrame: async () => {
                        if (faceMesh) await faceMesh.send({
                            image: video
                        });
                    },
                    width: 640,
                    height: 480
                });
                camera.start();

                btnStart.disabled = true;
                btnStop.disabled = false;

                validationPanel.style.display = 'block';
                resultPanel.classList.remove('show');

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
            ctx.clearRect(0, 0, overlay.width, overlay.height);

            btnStart.disabled = false;
            btnVerify.disabled = true;
            btnStop.disabled = true;

            blinkInstruction.classList.remove('show', 'success');
            document.getElementById('statusBadge').textContent = 'Menunggu';
            document.getElementById('statusBadge').className = 'status-badge waiting';
        }

        // =========================================
        // Verification
        // =========================================

        async function verify() {
            if (!lastValidation.allPassed) {
                alert('Validasi belum lengkap! Pastikan kedipan terdeteksi.');
                return;
            }

            loadingOverlay.classList.add('show');
            btnVerify.disabled = true;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            const blob = await new Promise(resolve => {
                canvas.toBlob(resolve, 'image/jpeg', 0.9);
            });

            const apiUrl = document.getElementById('apiUrl').value.replace(/\/$/, '');
            const formData = new FormData();
            formData.append('tenant_id', document.getElementById('tenantId').value);
            formData.append('user_id', document.getElementById('userId').value);
            formData.append('threshold', document.getElementById('threshold').value);
            formData.append('file', blob, 'capture.jpg');

            try {
                const response = await fetch(`${apiUrl}/verify`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                loadingOverlay.classList.remove('show');
                showResult(data);

            } catch (err) {
                loadingOverlay.classList.remove('show');
                showResult({
                    success: false,
                    verified: false,
                    message: 'Error: ' + err.message
                });
            }
        }

        function showResult(data) {
            validationPanel.style.display = 'none';
            resultPanel.classList.add('show');

            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const message = document.getElementById('resultMessage');

            if (data.verified) {
                icon.textContent = '✓';
                icon.className = 'result-icon success';
                title.textContent = 'Verifikasi Berhasil';
                title.style.color = 'var(--success)';
                message.textContent = `Halo, ${data.user_name}! (distance: ${data.distance?.toFixed(4)})`;
            } else {
                icon.textContent = '✗';
                icon.className = 'result-icon error';
                title.textContent = 'Verifikasi Gagal';
                title.style.color = 'var(--danger)';
                message.textContent = data.message || 'Wajah tidak cocok';
            }
        }

        function retry() {
            resultPanel.classList.remove('show');
            validationPanel.style.display = 'block';

            // Reset blink
            blinkState = {
                detected: false,
                blinkCount: 0,
                lastEAR: 1.0,
                eyesClosed: false,
                requiredBlinks: 1,
                blinkHistory: []
            };
            blinkInstruction.textContent = '👁️ Kedipkan mata Anda';
            blinkInstruction.classList.remove('success');

            btnVerify.disabled = true;
        }

        // Event listeners
        btnStart.onclick = startCamera;
        btnStop.onclick = stopCamera;
        btnVerify.onclick = verify;
        btnRetry.onclick = retry;
    </script>
</body>

</html>
