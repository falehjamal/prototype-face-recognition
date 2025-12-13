<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Multi-Tenant Enrollment | Face Recognition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="assets/style.css" />
    <script src="assets/jquery.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <div class="eyebrow">Multi-Tenant Console</div>
            <h1>Kelola Enrollment Multi-Tenant</h1>
            <p>Tambah wajah baru ke database tenant dengan tenant_id dan user_id.</p>
            <div class="api-row">
                <label for="apiBase" class="field-label">API base URL</label>
                <input type="text" id="apiBase" value="http://localhost:8000" />
            </div>
            <div class="status-bar">
                <span class="pill muted" id="uploadStatus">Siap upload</span>
                <span class="pill muted" id="listStatus">Memuat daftar…</span>
                <a href="tenant_deteksi.php" class="pill ghost" style="text-decoration:none;">Ke Identify</a>
                <a href="index.php" class="pill ghost" style="text-decoration:none;">Legacy Mode</a>
            </div>
        </header>

        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Konfigurasi Tenant</p>
                    <h2>Pilih Tenant + User</h2>
                    <p class="sub">Tenant ID dan User ID wajib diisi sebelum enroll.</p>
                </div>
            </div>

            <div class="field-grid">
                <div>
                    <label class="field-label" for="tenantId">Tenant ID</label>
                    <input type="number" id="tenantId" value="1" min="1" />
                    <p class="micro">ID tenant dari tabel tenants di gateway database.</p>
                </div>
                <div>
                    <label class="field-label" for="userId">User ID</label>
                    <input type="number" id="userId" value="1" min="1" />
                    <p class="micro">ID user dari tabel user_{tenant_id} di tenant database.</p>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Tambah Enrollment</p>
                    <h2>Upload foto + nama</h2>
                    <p class="sub">Gunakan foto dengan wajah jelas. Model: ArcFace 512-d.</p>
                </div>
            </div>

            <div class="field-grid">
                <div>
                    <label class="field-label" for="imageFile">Gambar</label>
                    <input type="file" id="imageFile" accept="image/*" />
                    <p class="micro">JPEG/PNG, wajah jelas, satu orang per foto.</p>
                </div>
                <div>
                    <label class="field-label" for="enrollName">Nama / Label</label>
                    <input type="text" id="enrollName" placeholder="misal: Alice" />
                    <p class="micro">Nama ini muncul ketika terdeteksi.</p>
                </div>
            </div>

            <div class="button-row">
                <button id="enrollBtn">Simpan Enrollment</button>
                <button id="refreshBtn" class="ghost">Refresh daftar</button>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Daftar Enrollment</p>
                    <h2>Data tersimpan (tenant <span id="currentTenantDisplay">1</span>)</h2>
                    <p class="sub">Diambil dari tabel enrollment_{tenant_id} di tenant database.</p>
                </div>
            </div>
            <div class="taglist" id="enrollmentList">
                <span class="tag">Memuat…</span>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Hapus Enrollment</p>
                    <h2>Hapus by ID</h2>
                    <p class="sub">Masukkan ID enrollment yang ingin dihapus.</p>
                </div>
            </div>

            <div class="field-grid">
                <div>
                    <label class="field-label" for="deleteEnrollmentId">Enrollment ID</label>
                    <input type="number" id="deleteEnrollmentId" placeholder="misal: 1" min="1" />
                </div>
            </div>
            <div class="button-row">
                <button id="deleteBtn" class="ghost">Hapus Enrollment</button>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <div>
                    <p class="badge">Log</p>
                    <h2>Respons</h2>
                    <p class="sub">Hasil upload / daftar akan tampil di sini.</p>
                </div>
            </div>
            <pre id="result">{ "result": "menunggu request" }</pre>
        </section>
    </div>

    <script>
        $(function() {
            const uploadChip = document.getElementById('uploadStatus');
            const listChip = document.getElementById('listStatus');

            const setResult = (payload) => {
                const formatted = typeof payload === 'string' ? payload : JSON.stringify(payload, null, 2);
                $('#result').text(formatted);
            };

            const updateChip = (el, text, tone) => {
                el.textContent = text;
                el.className = `pill ${tone}`;
            };

            const getTenantId = () => parseInt($('#tenantId').val()) || 1;
            const getUserId = () => parseInt($('#userId').val()) || 1;

            const makeEndpoints = () => {
                const base = document.getElementById('apiBase').value.replace(/\/$/, '');
                const tenantId = getTenantId();
                return {
                    enroll: `${base}/enroll`,
                    enrollments: `${base}/enrollments/${tenantId}`,
                    deleteById: (id) => `${base}/enrollments/${tenantId}/${id}`,
                    deleteByName: (name) => `${base}/enrollments/${tenantId}/name/${encodeURIComponent(name)}`,
                };
            };

            const requireFile = () => {
                const file = document.getElementById('imageFile').files[0];
                if (!file) {
                    alert('Pilih file gambar terlebih dahulu.');
                    return null;
                }
                return file;
            };

            const loadEnrollments = () => {
                const tenantId = getTenantId();
                $('#currentTenantDisplay').text(tenantId);
                const {
                    enrollments
                } = makeEndpoints();
                updateChip(listChip, 'Memuat daftar…', 'muted');
                $.get(enrollments)
                    .done((data) => {
                        const list = data.enrollments || [];
                        const html = list.length ?
                            list.map((e) => `
                <span class="tag" title="ID: ${e.id}, User: ${e.user_id}">
                  ${e.name} <small style="opacity:0.6">(ID:${e.id})</small>
                </span>
              `).join(' ') :
                            '<span class="tag">Belum ada enrollment</span>';
                        $('#enrollmentList').html(html);
                        updateChip(listChip, `Tenant ${tenantId}: ${list.length} enrollments`, 'success');
                        setResult(data);
                    })
                    .fail((jqXHR) => {
                        $('#enrollmentList').html('<span class="tag">Gagal memuat</span>');
                        updateChip(listChip, 'Gagal memuat', 'danger');
                        let body = jqXHR.responseText;
                        try {
                            body = JSON.parse(body);
                        } catch (_) {}
                        setResult({
                            status: jqXHR.status,
                            error: body
                        });
                    });
            };

            const ajaxSend = (url, formData, action, onSuccess) => {
                setResult({
                    status: 'loading',
                    action
                });
                $.ajax({
                    url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (data) => {
                        setResult(data);
                        if (onSuccess) onSuccess(data);
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
                        updateChip(uploadChip, 'Gagal', 'danger');
                    },
                });
            };

            // Enroll button
            $('#enrollBtn').on('click', () => {
                const file = requireFile();
                if (!file) return;
                const name = $('#enrollName').val().trim();
                if (!name) {
                    alert('Isi nama untuk enrollment.');
                    return;
                }
                const fd = new FormData();
                fd.append('tenant_id', getTenantId());
                fd.append('user_id', getUserId());
                fd.append('name', name);
                fd.append('file', file);
                const {
                    enroll
                } = makeEndpoints();
                updateChip(uploadChip, 'Uploading…', 'accent');
                ajaxSend(enroll, fd, 'enroll', () => {
                    updateChip(uploadChip, 'Upload berhasil', 'success');
                    loadEnrollments();
                });
            });

            // Delete button
            $('#deleteBtn').on('click', () => {
                const enrollmentId = parseInt($('#deleteEnrollmentId').val());
                if (!enrollmentId) {
                    alert('Masukkan Enrollment ID yang ingin dihapus.');
                    return;
                }
                if (!confirm(`Hapus enrollment ID ${enrollmentId}?`)) return;

                const {
                    deleteById
                } = makeEndpoints();
                const url = deleteById(enrollmentId);

                $.ajax({
                    url,
                    method: 'DELETE',
                    success: (data) => {
                        setResult(data);
                        loadEnrollments();
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
                    },
                });
            });

            // Refresh enrollments when tenant changes
            $('#tenantId').on('change', loadEnrollments);
            $('#refreshBtn').on('click', loadEnrollments);
            loadEnrollments();
        });
    </script>
</body>

</html>
