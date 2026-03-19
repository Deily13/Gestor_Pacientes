<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Pacientes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #3d3580;
            --surface:   #4a3f9f;
            --card:      #5a50b8;
            --accent:    #7c6fd4;
            --accent-h:  #6a5ec2;
            --light:     #8f85d8;
            --border:    rgba(255,255,255,.15);
            --text:      #ffffff;
            --muted:     rgba(255,255,255,.6);
            --danger:    #e05c7a;
            --success:   #5cb85c;
            --radius:    14px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse at 10% 60%, rgba(124,111,212,.35) 0%, transparent 55%),
                radial-gradient(ellipse at 90% 10%, rgba(90,80,184,.4) 0%, transparent 50%);
        }

        /* ── SIDEBAR ── */
        .sidebar {
            height: 100%; width: 0;
            position: fixed; z-index: 2000;
            top: 0; left: 0;
            background: #2d2870;
            overflow-x: hidden;
            transition: .3s;
            padding-top: 60px;
            border-right: 1px solid var(--border);
        }
        .sidebar a {
            padding: 12px 20px; font-size: .95rem;
            color: var(--muted); display: block;
            text-decoration: none; transition: all .2s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover { color: #fff; background: rgba(255,255,255,.07); border-left-color: var(--light); }
        .sidebar .closebtn { position: absolute; top: 12px; right: 18px; font-size: 1.8rem; color: var(--muted); }
        .openbtn {
            position: fixed; top: 14px; left: 16px; z-index: 1500;
            background: var(--card); color: #fff; border: 1px solid var(--border);
            padding: .45rem 1rem; border-radius: 10px;
            font-size: .9rem; cursor: pointer;
            font-family: 'DM Sans', sans-serif; transition: background .2s;
        }
        .openbtn:hover { background: var(--accent); }

        /* ── MAIN ── */
        .main-content { padding: 80px 2rem 2rem; max-width: 1100px; margin: 0 auto; }

        .page-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 1.75rem; }
        .page-header h1 { font-family: 'DM Serif Display', serif; font-size: 2rem; line-height: 1; color: #fff; }
        .page-header p  { color: var(--muted); font-size: .875rem; margin-top: .25rem; }

        /* ── TABLA ── */
        .table-wrap {
            background: rgba(74,63,159,.6);
            backdrop-filter: blur(12px);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
            overflow: hidden;
        }
        .table-toolbar { padding: .9rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; gap: .75rem; align-items: center; }
        .search-input {
            flex: 1; max-width: 300px;
            border: 1.5px solid var(--border); border-radius: 10px;
            padding: .45rem .85rem; font-size: .875rem;
            font-family: 'DM Sans', sans-serif;
            background: rgba(255,255,255,.1);
            color: #fff;
            outline: none; transition: border-color .2s;
        }
        .search-input::placeholder { color: var(--muted); }
        .search-input:focus { border-color: var(--light); }

        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(0,0,0,.2); }
        th { text-align: left; padding: .7rem 1.25rem; font-size: .72rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .07em; white-space: nowrap; }
        td { padding: .8rem 1.25rem; font-size: .875rem; border-top: 1px solid var(--border); vertical-align: middle; color: #fff; }
        tr:hover td { background: rgba(255,255,255,.05); }

        .badge-tipo { display: inline-block; padding: .18rem .6rem; border-radius: 99px; font-size: .72rem; font-weight: 600; background: rgba(255,255,255,.15); color: #fff; }
        .actions { display: flex; gap: .4rem; }
        .state-row td { text-align: center; padding: 3rem; color: var(--muted); }

        .foto-tabla { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border); }
        .foto-placeholder { width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,.1); display: flex; align-items: center; justify-content: center; font-size: 1rem; border: 2px solid var(--border); }

        /* ── PAGINACIÓN ── */
        .pagination-bar { display: flex; justify-content: flex-end; align-items: center; gap: .5rem; padding: .9rem 1.25rem; border-top: 1px solid var(--border); font-size: .85rem; }
        .page-btn { border: 1.5px solid var(--border); background: rgba(255,255,255,.08); border-radius: 10px; padding: .3rem .75rem; cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: .8rem; color: #fff; transition: all .2s; }
        .page-btn:disabled { opacity: .35; cursor: not-allowed; }
        .page-btn:not(:disabled):hover { border-color: var(--light); background: rgba(143,133,216,.2); }
        .page-info { color: var(--muted); }

        /* ── MODAL ── */
        .modal-content {
            border: 1px solid var(--border) !important;
            border-radius: var(--radius) !important;
            background: #4a3f9f !important;
            box-shadow: 0 20px 60px rgba(0,0,0,.4) !important;
            color: #fff;
        }
        .modal-header  { border-bottom: 1px solid var(--border) !important; padding: 1.25rem 1.5rem .9rem; }
        .modal-title   { font-family: 'DM Serif Display', serif; font-size: 1.3rem; color: #fff; }
        .modal-body    { padding: 1.25rem 1.5rem; }
        .modal-footer  { border-top: 1px solid var(--border) !important; padding: .9rem 1.5rem; }
        .close         { color: var(--muted) !important; opacity: 1 !important; }
        .close:hover   { color: #fff !important; }

        /* ── FORM ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
        .form-group label { font-size: .75rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: .25rem; }
        .form-control {
            border: 1.5px solid var(--border) !important;
            border-radius: 10px !important;
            background: rgba(255,255,255,.1) !important;
            color: #fff !important;
            font-family: 'DM Sans', sans-serif !important;
            font-size: .9rem !important;
            transition: border-color .2s !important;
        }
        .form-control::placeholder { color: rgba(255,255,255,.3) !important; }
        .form-control:focus { border-color: var(--light) !important; box-shadow: 0 0 0 3px rgba(143,133,216,.2) !important; }
        .form-control:disabled { opacity: .45; }
        select.form-control option { background: #4a3f9f; color: #fff; }

        /* ── FOTO ── */
        .foto-upload-wrap {
            grid-column: 1 / -1;
            display: flex; align-items: center; gap: 1.25rem;
            padding: 1rem;
            border: 1.5px dashed rgba(255,255,255,.25);
            border-radius: var(--radius);
            background: rgba(255,255,255,.05);
            transition: border-color .2s;
        }
        .foto-upload-wrap:hover { border-color: var(--light); }
        .foto-preview { width: 80px; height: 80px; border-radius: 50%; border: 2px solid var(--border); flex-shrink: 0; background: rgba(255,255,255,.1); display: flex; align-items: center; justify-content: center; font-size: 2rem; overflow: hidden; }
        .foto-preview img { width: 100%; height: 100%; object-fit: cover; }
        .foto-upload-info { flex: 1; }
        .foto-upload-info strong { font-size: .875rem; color: #fff; }
        .foto-upload-info p { font-size: .8rem; color: var(--muted); margin: .2rem 0 .6rem; }

        .foto-tabs { display: flex; gap: .5rem; margin-bottom: .75rem; }
        .foto-tab { padding: .3rem .9rem; border-radius: 99px; border: 1.5px solid var(--border); background: none; font-size: .8rem; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all .2s; color: var(--muted); }
        .foto-tab.active { background: var(--accent); color: #fff; border-color: var(--accent); }

        .foto-panel { display: none; }
        .foto-panel.active { display: block; }

        .btn-upload { background: var(--accent); color: #fff; border: none; border-radius: 8px; padding: .4rem .9rem; font-size: .82rem; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background .2s; }
        .btn-upload:hover { background: var(--accent-h); }
        .btn-upload:disabled { opacity: .5; cursor: not-allowed; }
        #foto-file-input { display: none; }

        .upload-status { font-size: .78rem; color: var(--muted); margin-top: .4rem; }
        .upload-status.ok    { color: #29ec71; }
        .upload-status.error { color: #fca5a5; }

        /* ── BOTONES ── */
        .btn-accent { background: var(--accent); color: #fff; border: none; border-radius: 10px; padding: .5rem 1.2rem; font-size: .875rem; font-family: 'DM Sans', sans-serif; font-weight: 500; transition: all .2s; }
        .btn-accent:hover { background: var(--accent-h); color: #fff; transform: translateY(-1px); }
        .btn-secondary { background: rgba(255,255,255,.1) !important; border: 1px solid var(--border) !important; color: #fff !important; border-radius: 10px !important; }
        .btn-secondary:hover { background: rgba(255,255,255,.2) !important; }
        .btn-info    { background: #5b8dee !important; border: none !important; border-radius: 8px !important; }
        .btn-info:hover { background: #4a7de0 !important; }
        .btn-danger  { background: var(--danger) !important; border: none !important; border-radius: 8px !important; }
        .btn-danger:hover { background: #c94d6a !important; }

        /* ── CONFIRM ── */
        .confirm-body { padding: 1.5rem; text-align: center; }
        .confirm-body .icon { font-size: 2rem; margin-bottom: .6rem; }
        .confirm-body h5 { font-family: 'DM Serif Display', serif; font-size: 1.2rem; margin-bottom: .4rem; color: #fff; }
        .confirm-body p { color: var(--muted); font-size: .875rem; }

        /* ── TOAST ── */
        #toast { position: fixed; bottom: 1.5rem; right: 1.5rem; padding: .7rem 1.2rem; border-radius: 10px; font-size: .875rem; font-weight: 500; color: #fff; opacity: 0; transform: translateY(8px); transition: all .3s; z-index: 9999; pointer-events: none; }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.success { background: #166534; }
        #toast.error   { background: var(--danger); }
    </style>
</head>
<body>

<div id="mySidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
    <a href="#" onclick="cargarPacientes(1); closeNav()">👤 Pacientes</a>
    <a href="javascript:void(0)" onclick="cerrarSesion()">🚪 Cerrar sesión</a>
</div>
<button class="openbtn" onclick="openNav()">☰ Menú</button>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>Pacientes</h1>
            <p id="total-label">Cargando...</p>
        </div>
        <button type="button" class="btn btn-accent" data-toggle="modal" data-target="#modalForm" id="btn-nuevo">
            + Nuevo paciente
        </button>
    </div>

    <div class="table-wrap">
        <div class="table-toolbar">
            <input class="search-input" type="search" id="search" placeholder="Buscar por documento o nombre…">
        </div>
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Documento</th>
                    <th>Nombre completo</th>
                    <th>Género</th>
                    <th>Municipio</th>
                    <th>Correo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tbody">
                <tr class="state-row"><td colspan="7">Cargando pacientes…</td></tr>
            </tbody>
        </table>
        <div class="pagination-bar">
            <button class="page-btn" id="btn-prev" disabled>← Anterior</button>
            <span class="page-info" id="page-info"></span>
            <button class="page-btn" id="btn-next" disabled>Siguiente →</button>
        </div>
    </div>
</div>

<!-- MODAL FORMULARIO -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Nuevo paciente</h5>
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <form id="form-paciente" novalidate>
                    <input type="hidden" id="f-modo">
                    <input type="hidden" id="f-doc-original">
                    <input type="hidden" id="f-foto-url">
                    <div class="form-grid">

                        <div class="foto-upload-wrap">
                            <div class="foto-preview" id="foto-preview">👤</div>
                            <div class="foto-upload-info">
                                <strong>Foto del paciente</strong>
                                <p>Sube una imagen o pega una URL</p>
                                <div class="foto-tabs">
                                    <button type="button" class="foto-tab active" onclick="cambiarTab(event,'archivo')">📁 Archivo</button>
                                    <button type="button" class="foto-tab" onclick="cambiarTab(event,'url')">🔗 URL</button>
                                </div>
                                <div class="foto-panel active" id="panel-archivo">
                                    <button type="button" class="btn-upload" id="btn-subir-foto"
                                        onclick="document.getElementById('foto-file-input').click()">
                                        Seleccionar imagen
                                    </button>
                                    <input type="file" id="foto-file-input" accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <div class="upload-status" id="upload-status"></div>
                                </div>
                                <div class="foto-panel" id="panel-url">
                                    <input type="url" class="form-control" id="f-foto-url-input"
                                           placeholder="https://ejemplo.com/foto.jpg"
                                           style="margin-bottom:.4rem">
                                    <button type="button" class="btn-upload" onclick="usarUrl()">Usar URL</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tipo documento *</label>
                            <select class="form-control" id="f-tipo-doc" required>
                                <option value="">— Seleccionar —</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Número documento *</label>
                            <input type="text" class="form-control" id="f-numero-doc" required>
                        </div>
                        <div class="form-group">
                            <label>Primer nombre *</label>
                            <input type="text" class="form-control" id="f-nombre1" required>
                        </div>
                        <div class="form-group">
                            <label>Segundo nombre</label>
                            <input type="text" class="form-control" id="f-nombre2">
                        </div>
                        <div class="form-group">
                            <label>Primer apellido *</label>
                            <input type="text" class="form-control" id="f-apellido1" required>
                        </div>
                        <div class="form-group">
                            <label>Segundo apellido</label>
                            <input type="text" class="form-control" id="f-apellido2">
                        </div>
                        <div class="form-group">
                            <label>Género *</label>
                            <select class="form-control" id="f-genero" required>
                                <option value="">— Seleccionar —</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Departamento *</label>
                            <select class="form-control" id="f-departamento" required>
                                <option value="">— Seleccionar —</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Municipio *</label>
                            <select class="form-control" id="f-municipio" required>
                                <option value="">— Seleccionar departamento —</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" class="form-control" id="f-correo">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-accent" id="btn-guardar">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINAR -->
<div class="modal fade" id="modalConfirm" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="confirm-body">
                <div class="icon">🗑️</div>
                <h5>¿Eliminar paciente?</h5>
                <p>Esta acción no se puede deshacer.</p>
                <div class="d-flex justify-content-center mt-3" style="gap:.75rem">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn-confirm-delete">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toast"></div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    const TOKEN = localStorage.getItem('access_token');
    if (!TOKEN) window.location.href = '/login';

    axios.defaults.headers.common['Authorization'] = `Bearer ${TOKEN}`;
    axios.defaults.headers.common['Accept']        = 'application/json';

    axios.interceptors.response.use(
        r => r,
        error => {
            if (error.response?.status === 401) {
                localStorage.removeItem('access_token');
                window.location.href = '/login';
            }
            return Promise.reject(error);
        }
    );

    function cerrarSesion() {
        axios.post('/api/logout').finally(() => {
            localStorage.removeItem('access_token');
            window.location.href = '/login';
        });
    }

    function openNav()  { document.getElementById('mySidebar').style.width = '220px'; }
    function closeNav() { document.getElementById('mySidebar').style.width = '0'; }

    let currentPage = 1, totalPages = 1, deleteTarget = null, searchTimer = null;

    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = `show ${type}`;
        setTimeout(() => t.className = '', 3500);
    }

    // ── Foto ───────────────────────────────────────────────
    function cambiarTab(e, tab) {
        document.querySelectorAll('.foto-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.foto-panel').forEach(p => p.classList.remove('active'));
        e.target.classList.add('active');
        document.getElementById(`panel-${tab}`).classList.add('active');
    }

    function actualizarPreview(url) {
        const p = document.getElementById('foto-preview');
        p.innerHTML = url ? `<img src="${url}" alt="foto" onerror="this.parentElement.innerHTML='👤'">` : '👤';
        document.getElementById('f-foto-url').value = url ?? '';
    }

    function usarUrl() {
        const url = document.getElementById('f-foto-url-input').value.trim();
        if (!url) return;
        actualizarPreview(url);
        setUploadStatus('✓ URL guardada', 'ok');
    }

    function setUploadStatus(msg, type = '') {
        const el = document.getElementById('upload-status');
        el.textContent = msg;
        el.className = `upload-status ${type}`;
    }

    document.getElementById('foto-file-input').addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;
        const btn = document.getElementById('btn-subir-foto');
        btn.disabled = true;
        setUploadStatus('Subiendo imagen…');
        const formData = new FormData();
        formData.append('foto', file);
        try {
            const { data } = await axios.post('/api/paciente/upload-foto', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            actualizarPreview(data.foto_url);
            setUploadStatus('✓ Imagen subida correctamente', 'ok');
        } catch {
            setUploadStatus('✗ Error al subir la imagen', 'error');
        } finally {
            btn.disabled = false;
            this.value = '';
        }
    });

    // ── Catálogos ──────────────────────────────────────────
    async function cargarCatalogos() {
        try {
            const [t, g, d] = await Promise.all([
                axios.get('/api/tipos-documento'),
                axios.get('/api/generos'),
                axios.get('/api/departamentos'),
            ]);
            llenarSelect('f-tipo-doc',     t.data.data ?? t.data, 'id', 'nombre');
            llenarSelect('f-genero',       g.data.data ?? g.data, 'id', 'nombre');
            llenarSelect('f-departamento', d.data.data ?? d.data, 'id', 'nombre');
        } catch {}
    }

    function llenarSelect(id, items, vk, lk, sel = null) {
        document.getElementById(id).innerHTML =
            `<option value="">— Seleccionar —</option>` +
            (items ?? []).map(i =>
                `<option value="${i[vk]}" ${String(i[vk]) === String(sel) ? 'selected' : ''}>${i[lk]}</option>`
            ).join('');
    }

    async function cargarMunicipios(sel = null) {
        const id = document.getElementById('f-departamento').value;
        if (!id) { document.getElementById('f-municipio').innerHTML = '<option value="">— Seleccionar departamento —</option>'; return; }
        try {
            const { data } = await axios.get(`/api/municipios?departamento_id=${id}`);
            llenarSelect('f-municipio', data.data ?? data, 'id', 'nombre', sel);
        } catch {}
    }

    document.getElementById('f-departamento').addEventListener('change', () => cargarMunicipios());

    // ── Listar ─────────────────────────────────────────────
    async function cargarPacientes(page = 1) {
        const q = document.getElementById('search').value.trim();
        document.getElementById('tbody').innerHTML = `<tr class="state-row"><td colspan="7">Cargando…</td></tr>`;
        try {
            const params = new URLSearchParams({ page });
            if (q) params.set('q', q);
            const { data } = await axios.get(`/api/paciente?${params}`);
            const lista = data.data?.data ?? [];
            currentPage = data.data?.current_page ?? 1;
            totalPages  = data.data?.last_page    ?? 1;
            const total = data.data?.total        ?? 0;

            document.getElementById('total-label').textContent = `${total} paciente${total !== 1 ? 's' : ''} registrado${total !== 1 ? 's' : ''}`;
            document.getElementById('page-info').textContent   = `Página ${currentPage} de ${totalPages}`;
            document.getElementById('btn-prev').disabled = currentPage <= 1;
            document.getElementById('btn-next').disabled = currentPage >= totalPages;

            if (!lista.length) {
                document.getElementById('tbody').innerHTML = `<tr class="state-row"><td colspan="7">No se encontraron pacientes.</td></tr>`;
                return;
            }
            document.getElementById('tbody').innerHTML = lista.map(p => `
                <tr>
                    <td>${p.foto_url ? `<img src="${p.foto_url}" class="foto-tabla" alt="foto">` : `<div class="foto-placeholder">👤</div>`}</td>
                    <td><span class="badge-tipo">${p.tipo_documento?.nombre ?? '—'}</span><br><strong>${p.numero_documento}</strong></td>
                    <td>${[p.nombre1, p.nombre2, p.apellido1, p.apellido2].filter(Boolean).join(' ')}</td>
                    <td>${p.genero?.nombre ?? '—'}</td>
                    <td>${p.municipio?.nombre ?? '—'}</td>
                    <td style="color:var(--muted)">${p.correo ?? '—'}</td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-info btn-sm" onclick="abrirEditar('${p.numero_documento}')">Editar</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminar('${p.numero_documento}')">Eliminar</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch {
            document.getElementById('tbody').innerHTML = `<tr class="state-row"><td colspan="7">Error al cargar los pacientes.</td></tr>`;
        }
    }

    // ── Nuevo ──────────────────────────────────────────────
    document.getElementById('btn-nuevo').addEventListener('click', () => {
        document.getElementById('form-paciente').reset();
        document.getElementById('f-modo').value = 'create';
        document.getElementById('f-doc-original').value = '';
        document.getElementById('modal-title').textContent = 'Nuevo paciente';
        document.getElementById('f-numero-doc').disabled = false;
        document.getElementById('f-foto-url-input').value = '';
        actualizarPreview('');
        setUploadStatus('');
    });

    // ── Editar ─────────────────────────────────────────────
    async function abrirEditar(doc) {
        try {
            const { data } = await axios.get(`/api/paciente/${doc}`);
            const p = data.data;
            document.getElementById('f-modo').value           = 'edit';
            document.getElementById('f-doc-original').value   = doc;
            document.getElementById('modal-title').textContent = 'Editar paciente';
            document.getElementById('f-numero-doc').disabled   = true;
            document.getElementById('f-tipo-doc').value      = p.tipo_documento_id;
            document.getElementById('f-numero-doc').value    = p.numero_documento;
            document.getElementById('f-nombre1').value       = p.nombre1   ?? '';
            document.getElementById('f-nombre2').value       = p.nombre2   ?? '';
            document.getElementById('f-apellido1').value     = p.apellido1 ?? '';
            document.getElementById('f-apellido2').value     = p.apellido2 ?? '';
            document.getElementById('f-genero').value        = p.genero_id;
            document.getElementById('f-departamento').value  = p.departamento_id;
            await cargarMunicipios(p.municipio_id);
            document.getElementById('f-correo').value        = p.correo   ?? '';
            document.getElementById('f-foto-url-input').value = p.foto_url ?? '';
            actualizarPreview(p.foto_url ?? '');
            setUploadStatus('');
            $('#modalForm').modal('show');
        } catch {
            showToast('Error al cargar el paciente.', 'error');
        }
    }

    // ── Guardar ────────────────────────────────────────────
    document.getElementById('btn-guardar').addEventListener('click', async () => {
        const modo = document.getElementById('f-modo').value;
        const doc  = document.getElementById('f-doc-original').value;
        const payload = {
            tipo_documento_id: document.getElementById('f-tipo-doc').value,
            numero_documento:  document.getElementById('f-numero-doc').value.trim(),
            nombre1:           document.getElementById('f-nombre1').value.trim(),
            nombre2:           document.getElementById('f-nombre2').value.trim() || null,
            apellido1:         document.getElementById('f-apellido1').value.trim(),
            apellido2:         document.getElementById('f-apellido2').value.trim() || null,
            genero_id:         document.getElementById('f-genero').value,
            departamento_id:   document.getElementById('f-departamento').value,
            municipio_id:      document.getElementById('f-municipio').value,
            correo:            document.getElementById('f-correo').value.trim() || null,
            foto_url:          document.getElementById('f-foto-url').value.trim() || null,
        };
        const btn = document.getElementById('btn-guardar');
        btn.disabled = true; btn.textContent = 'Guardando…';
        try {
            if (modo === 'create') {
                await axios.post('/api/paciente', payload);
                showToast('Paciente creado correctamente.');
            } else {
                await axios.put(`/api/paciente/${doc}`, payload);
                showToast('Paciente actualizado correctamente.');
            }
            $('#modalForm').modal('hide');
            cargarPacientes(currentPage);
        } catch (err) {
            showToast(err.response?.data?.message ?? 'Error al guardar.', 'error');
        } finally {
            btn.disabled = false; btn.textContent = 'Guardar';
        }
    });

    // ── Eliminar ───────────────────────────────────────────
    function confirmarEliminar(doc) { deleteTarget = doc; $('#modalConfirm').modal('show'); }

    document.getElementById('btn-confirm-delete').addEventListener('click', async () => {
        if (!deleteTarget) return;
        try {
            await axios.delete(`/api/paciente/${deleteTarget}`);
            showToast('Paciente eliminado.');
            $('#modalConfirm').modal('hide');
            cargarPacientes(currentPage);
        } catch {
            showToast('Error al eliminar.', 'error');
        } finally { deleteTarget = null; }
    });

    document.getElementById('btn-prev').addEventListener('click', () => cargarPacientes(currentPage - 1));
    document.getElementById('btn-next').addEventListener('click', () => cargarPacientes(currentPage + 1));

    document.getElementById('search').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => cargarPacientes(1), 400);
    });

    cargarCatalogos();
    cargarPacientes();
</script>
</body>
</html>