<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laravel Request Test | Face Verification</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="assets/style.css" />
    <style>
        .video-wrap {
            position: relative;
            width: 640px;
            height: 480px;
            background: #000;
            border-radius: 14px;
            overflow: hidden;
        }

        #camera {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #overlayCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }

        .status-big {
            font-size: 24px;
            font-weight: 700;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 10px;
        }

        .status-big.success {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status-big.error {
            background: rgba(244, 63, 94, 0.2);
            color: #f43f5e;
        }

        .status-big.waiting {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }
    </style>
    <script src="assets/jquery.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <div class="eyebrow">Laravel Request Simulator</div>
            <h1>Test Verifikasi Absensi</h1>
            <p>Simulasi flow absensi: Laravel mengirim tenant_id + user_id + foto → Python verifikasi.</p>
            <div class="api-row">
                <label for="apiBase" class="field-label">API base URL</label>
                <input type="text" id="apiBase" value="http://localhost:8000" />
            </div>
            <div class="status-bar">
                <span class="pill muted" id="cameraStatus">Camera: standby</span>
                <span class="pill muted" id="verifyStatus">Verify: standby</span>
                <a href="tenant_enroll.php" class="pill ghost" style="text-decoration:none;">Enroll</a>
            </div>
        </header>

        <!-- Config Section -->
        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Konfigurasi</p>
                    <h2>Parameter dari Laravel</h2>
                    <p class="sub">Ini adalah parameter yang akan dikirim Laravel saat user absen.</p>
                </div>
            </div>

            <div class="field-grid">
                <div>
                    <label class="field-label" for="tenantId">Tenant ID</label>
                    <input type="number" id="tenantId" value="1" min="1" />
                    <p class="micro">ID tenant dari session Laravel.</p>
                </div>
                <div>
                    <label class="field-label" for="userId">User ID</label>
                    <input type="number" id="userId" value="1" min="1" />
                    <p class="micro">ID user yang sedang absen (dari auth).</p>
                </div>
                <div>
                    <label class="field-label" for="thresholdInput">Threshold</label>
                    <input type="number" step="0.01" min="0.1" max="1" id="thresholdInput" value="0.35" />
                    <p class="micro">0.30-0.40 (lebih kecil = lebih ketat).</p>
                </div>
            </div>
        </section>

        <!-- Camera Section -->
        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Kamera</p>
                    <h2>Verifikasi Wajah</h2>
                    <p class="sub">Klik "Verifikasi Sekarang" untuk mengirim foto ke endpoint /verify.</p>
                </div>
            </div>

            <div class="camera-row">
                <div class="video-wrap">
                    <video id="camera" autoplay playsinline muted></video>
                    <canvas id="overlayCanvas"></canvas>
                </div>
                <canvas id="captureCanvas" width="640" height="480" hidden></canvas>
            </div>

            <div class="button-row tight">
                <button id="startCameraBtn" class="secondary">Nyalakan Kamera</button>
                <button id="verifyBtn">🔍 Verifikasi Sekarang</button>
                <button id="stopCameraBtn" class="ghost">Stop Kamera</button>
            </div>

            <!-- Big Status Display -->
            <div id="statusBig" class="status-big waiting">Menunggu verifikasi...</div>
        </section>

        <!-- Response Log -->
        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Response</p>
                    <h2>Hasil dari Python API</h2>
                    <p class="sub">Response lengkap dari endpoint /verify.</p>
                </div>
            </div>
            <pre id="result">{ "result": "menunggu request" }</pre>
        </section>

        <!-- Flow Explanation -->
        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Flow</p>
                    <h2>Cara Kerja</h2>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-tile">1. Laravel kirim: tenant_id + user_id + foto</div>
                <div class="stat-tile">2. Python cari enrollment user di DB tenant</div>
                <div class="stat-tile">3. Bandingkan face encoding</div>
                <div class="stat-tile">4. Return: verified=true/false + message</div>
            </div>
        </section>
    </div>

    <script>
        $(function() {
            const videoEl = document.getElementById('camera');
            const overlayCanvas = document.getElementById('overlayCanvas');
            const overlayCtx = overlayCanvas.getContext('2d');
            const cameraChip = document.getElementById('cameraStatus');
            const verifyChip = document.getElementById('verifyStatus');
            const statusBig = document.getElementById('statusBig');
            let mediaStream = null;

            const getTenantId = () => parseInt($('#tenantId').val()) || 1;
            const getUserId = () => parseInt($('#userId').val()) || 1;
            const getThreshold = () => parseFloat($('#thresholdInput').val()) || 0.35;

            const setResult = (payload) => {
                const formatted = typeof payload === 'string' ? payload : JSON.stringify(payload, null, 2);
                $('#result').text(formatted);
            };

            const updateChip = (el, text, tone) => {
                el.textContent = text;
                el.className = `pill ${tone}`;
            };

            const updateStatusBig = (text, type) => {
                statusBig.textContent = text;
                statusBig.className = `status-big ${type}`;
            };

            const syncOverlaySize = () => {
                if (!videoEl.videoWidth) return {
                    scaleX: 1,
                    scaleY: 1
                };
                overlayCanvas.width = videoEl.clientWidth;
                overlayCanvas.height = videoEl.clientHeight;
                return {
                    scaleX: videoEl.clientWidth / videoEl.videoWidth,
                    scaleY: videoEl.clientHeight / videoEl.videoHeight,
                };
            };

            const clearOverlay = () => {
                overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);
            };

            const drawBbox = (data) => {
                const bbox = data && data.bbox;
                if (!bbox || typeof bbox.left === 'undefined') {
                    clearOverlay();
                    return;
                }
                const {
                    scaleX,
                    scaleY
                } = syncOverlaySize();
                const left = bbox.left * scaleX;
                const top = bbox.top * scaleY;
                const width = (bbox.right - bbox.left) * scaleX;
                const height = (bbox.bottom - bbox.top) * scaleY;

                clearOverlay();
                overlayCtx.strokeStyle = data.verified ? '#22c55e' : '#f43f5e';
                overlayCtx.lineWidth = 4;
                overlayCtx.strokeRect(left, top, width, height);

                const label = data.verified ? `✓ ${data.user_name}` : '✗ Bukan Orang Tsb';
                overlayCtx.font = "bold 18px 'Space Grotesk', sans-serif";
                overlayCtx.fillStyle = data.verified ? '#22c55e' : '#f43f5e';
                overlayCtx.fillText(label, left, top - 10);
            };

            // Camera functions
            const getCameraStream = async () => {
                if (mediaStream) return mediaStream;
                try {
                    mediaStream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                    videoEl.srcObject = mediaStream;
                    await videoEl.play();
                    syncOverlaySize();
                    updateChip(cameraChip, 'Camera: aktif', 'success');
                    return mediaStream;
                } catch (err) {
                    updateChip(cameraChip, 'Camera: gagal', 'danger');
                    setResult({
                        error: err.message
                    });
                    return null;
                }
            };

            const stopCamera = () => {
                if (mediaStream) {
                    mediaStream.getTracks().forEach(t => t.stop());
                    mediaStream = null;
                    videoEl.srcObject = null;
                }
                clearOverlay();
                updateChip(cameraChip, 'Camera: standby', 'muted');
            };

            const captureFrame = () => {
                if (!videoEl.videoWidth) return null;
                const canvas = document.getElementById('captureCanvas');
                canvas.width = videoEl.videoWidth;
                canvas.height = videoEl.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
                return new Promise(resolve => {
                    canvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.9);
                });
            };

            // Verify function
            const doVerify = async () => {
                const blob = await captureFrame();
                if (!blob) {
                    alert('Kamera belum siap!');
                    return;
                }

                updateChip(verifyChip, 'Verifying...', 'accent');
                updateStatusBig('Memverifikasi...', 'waiting');

                const fd = new FormData();
                fd.append('tenant_id', getTenantId());
                fd.append('user_id', getUserId());
                fd.append('threshold', getThreshold());
                fd.append('file', new File([blob], 'capture.jpg', {
                    type: 'image/jpeg'
                }));

                const baseUrl = document.getElementById('apiBase').value.replace(/\/$/, '');

                $.ajax({
                    url: `${baseUrl}/verify`,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: (data) => {
                        setResult(data);
                        drawBbox(data);

                        if (data.verified) {
                            updateChip(verifyChip, '✓ Verified', 'success');
                            updateStatusBig(`✓ BERHASIL - ${data.user_name}`, 'success');
                        } else {
                            updateChip(verifyChip, '✗ Not Match', 'danger');
                            updateStatusBig(`✗ GAGAL - ${data.message}`, 'error');
                        }
                    },
                    error: (jqXHR) => {
                        let body = jqXHR.responseText;
                        try {
                            body = JSON.parse(body);
                        } catch (_) {}
                        setResult({
                            status: jqXHR.status,
                            error: body
                        });
                        updateChip(verifyChip, 'Error', 'danger');
                        updateStatusBig('Error: ' + (body.detail || 'Request gagal'), 'error');
                    }
                });
            };

            // Button handlers
            $('#startCameraBtn').on('click', getCameraStream);
            $('#stopCameraBtn').on('click', stopCamera);
            $('#verifyBtn').on('click', doVerify);
            $(window).on('resize', syncOverlaySize);
        });
    </script>
</body>

</html>
