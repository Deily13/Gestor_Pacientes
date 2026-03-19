<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #f5f3ef;
            --surface:  #ffffff;
            --border:   #e2ddd6;
            --text:     #1a1714;
            --muted:    #8c867e;
            --accent:   #c9541a;
            --accent-h: #a8430f;
            --danger:   #b91c1c;
            --success:  #166534;
            --radius:   10px;
            --shadow:   0 2px 12px rgba(0,0,0,.07);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2.5rem 1.5rem;
        }

        /* ── HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            line-height: 1;
        }
        .page-header p { color: var(--muted); font-size: .875rem; margin-top: .3rem; }
        .container { max-width: 1100px; margin: 0 auto; }

        /* ── TABLA ── */
        .table-wrap {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .table-toolbar {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: .75rem;
            align-items: center;
        }
        .search-input {
            flex: 1;
            max-width: 300px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: .5rem .9rem;
            font-size: .875rem;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            outline: none;
            transition: border-color .2s;
        }
        .search-input:focus { border-color: var(--accent); }

        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--bg); }
        th {
            text-align: left;
            padding: .75rem 1.25rem;
            font-size: .72rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            white-space: nowrap;
        }
        td {
            padding: .85rem 1.25rem;
            font-size: .875rem;
            border-top: 1px solid var(--border);
            vertical-align: middle;
        }
        tr:hover td { background: #faf9f7; }

        .badge {
            display: inline-block;
            padding: .2rem .6rem;
            border-radius: 99px;
            font-size: .72rem;
            font-weight: 600;
            background: #f0ece6;
            color: var(--muted);
        }
        .actions { display: flex; gap: .4rem; }
        .state-row td {
            text-align: center;
            padding: 3rem;
            color: var(--muted);
        }

        /* ── PAGINACIÓN ── */
        .pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: .5rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border);
            font-size: .85rem;
        }
        .page-btn {
            border: 1.5px solid var(--border);
            background: none;
            border-radius: var(--radius);
            padding: .35rem .75rem;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: .82rem;
            color: var(--text);
            transition: all .2s;
        }
        .page-btn:disabled { opacity: .35; cursor: not-allowed; }
        .page-btn:not(:disabled):hover { border-color: var(--accent); color: var(--accent); }
        .page-info { color: var(--muted); }

        /* ── BOTONES ── */
        .btn {
            padding: .55rem 1.2rem;
            border-radius: var(--radius);
            font-size: .875rem;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            border: none;
            transition: all .2s;
        }
        .btn-primary   { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-h); }
        .btn-secondary { background: transparent; border: 1.5px solid var(--border); color: var(--text); }
        .btn-secondary:hover { border-color: var(--text); }
        .btn-danger    { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #991b1b; }
        .btn-sm { padding: .3rem .75rem; font-size: .78rem; }

        /* ── MODAL ── */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            background: var(--surface);
            border-radius: var(--radius);
            width: min(560px, 95vw);
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
            animation: slideUp .25s ease;
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .modal-header {
            padding: 1.4rem 1.75rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 { font-family: 'DM Serif Display', serif; font-size: 1.3rem; }
        .modal-close {
            background: none; border: none;
            font-size: 1.3rem; cursor: pointer;
            color: var(--muted); transition: color .2s;
        }
        .modal-close:hover { color: var(--text); }
        .modal-body { padding: 1.4rem 1.75rem; }

        /* ── FORM ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; }
        .form-group { display: flex; flex-direction: column; gap: .3rem; }
        .form-group.full { grid-column: 1 / -1; }
        label { font-size: .75rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
        input, select {
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: .6rem .85rem;
            font-size: .9rem;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            outline: none;
            transition: border-color .2s;
        }
        input:focus, select:focus { border-color: var(--accent); }
        input:disabled { opacity: .55; cursor: not-allowed; }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.4rem;
        }

        /* ── CONFIRM MODAL ── */
        .confirm-body { padding: 2rem 1.75rem; text-align: center; }
        .confirm-body .icon { font-size: 2.2rem; margin-bottom: .75rem; }
        .confirm-body h3 { font-family: 'DM Serif Display', serif; font-size: 1.25rem; margin-bottom: .4rem; }
        .confirm-body p { color: var(--muted); font-size: .88rem; }
        .confirm-actions { display: flex; gap: .75rem; justify-content: center; margin-top: 1.4rem; }

        /* ── TOAST ── */
        #toast {
            position: fixed;
            bottom: 1.5rem; right: 1.5rem;
            padding: .7rem 1.2rem;
            border-radius: var(--radius);
            font-size: .875rem;
            font-weight: 500;
            color: #fff;
            opacity: 0;
            transform: translateY(8px);
            transition: all .3s;
            z-index: 9999;
            pointer-events: none;
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.success { background: var(--success); }
        #toast.error   { background: var(--danger); }
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1>Pacientes</h1>
            <p id="total-label">Cargando...</p>
        </div>
        <button class="btn btn-primary" id="btn-nuevo">+ Nuevo paciente</button>
    </div>

    <!-- TABLA -->
    <div class="table-wrap">
        <div class="table-toolbar">
            <input class="search-input" type="search" id="search" placeholder="Buscar por documento o nombre…">
        </div>
        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre completo</th>
                    <th>Género</th>
                    <th>Municipio</th>
                    <th>Correo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tbody">
                <tr class="state-row"><td colspan="6">Cargando pacientes…</td></tr>
            </tbody>
        </table>
        <div class="pagination">
            <button class="page-btn" id="btn-prev" disabled>← Anterior</button>
            <span class="page-info" id="page-info"></span>
            <button class="page-btn" id="btn-next" disabled>Siguiente →</button>
        </div>
    </div>

</div>

<!-- MODAL FORMULARIO -->
<div class="modal-backdrop" id="modal-form">
    <div class="modal">
        <div class="modal-header">
            <h2 id="modal-title">Nuevo paciente</h2>
            <button class="modal-close" data-close="modal-form">✕</button>
        </div>
        <div class="modal-body">
            <form id="form-paciente" novalidate>
                <input type="hidden" id="f-modo">
                <input type="hidden" id="f-doc-original">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tipo documento *</label>
                        <select id="f-tipo-doc" required>
                            <option value="">— Seleccionar —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número documento *</label>
                        <input type="text" id="f-numero-doc" required>
                    </div>
                    <div class="form-group">
                        <label>Primer nombre *</label>
                        <input type="text" id="f-nombre1" required>
                    </div>
                    <div class="form-group">
                        <label>Segundo nombre</label>
                        <input type="text" id="f-nombre2">
                    </div>
                    <div class="form-group">
                        <label>Primer apellido *</label>
                        <input type="text" id="f-apellido1" required>
                    </div>
                    <div class="form-group">
                        <label>Segundo apellido</label>
                        <input type="text" id="f-apellido2">
                    </div>
                    <div class="form-group">
                        <label>Género *</label>
                        <select id="f-genero" required>
                            <option value="">— Seleccionar —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Departamento *</label>
                        <select id="f-departamento" required>
                            <option value="">— Seleccionar —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Municipio *</label>
                        <select id="f-municipio" required>
                            <option value="">— Seleccionar departamento —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" id="f-correo">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" data-close="modal-form">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-guardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINAR -->
<div class="modal-backdrop" id="modal-confirm">
    <div class="modal" style="width:min(400px,95vw)">
        <div class="confirm-body">
            <div class="icon">🗑️</div>
            <h3>¿Eliminar paciente?</h3>
            <p>Esta acción no se puede deshacer.</p>
            <div class="confirm-actions">
                <button class="btn btn-secondary" data-close="modal-confirm">Cancelar</button>
                <button class="btn btn-danger" id="btn-confirm-delete">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toast"></div>

<script>
// ── Configuración Axios ────────────────────────────────
const TOKEN = localStorage.getItem('access_token') ?? '';
axios.defaults.headers.common['Authorization'] = `Bearer ${TOKEN}`;
axios.defaults.headers.common['Accept']        = 'application/json';
axios.defaults.headers.common['Content-Type']  = 'application/json';

// ── Estado ─────────────────────────────────────────────
let currentPage  = 1;
let totalPages   = 1;
let deleteTarget = null;
let searchTimer  = null;

// ── Modales ────────────────────────────────────────────
const openModal  = id => document.getElementById(id).classList.add('open');
const closeModal = id => document.getElementById(id).classList.remove('open');

document.querySelectorAll('[data-close]').forEach(btn =>
    btn.addEventListener('click', () => closeModal(btn.dataset.close))
);
document.querySelectorAll('.modal-backdrop').forEach(bd =>
    bd.addEventListener('click', e => { if (e.target === bd) bd.classList.remove('open'); })
);

// ── Toast ──────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `show ${type}`;
    setTimeout(() => t.className = '', 3500);
}

// ── Catálogos ──────────────────────────────────────────
async function cargarCatalogos() {
    try {
        const [tiposRes, generosRes, deptosRes] = await Promise.all([
            axios.get('/api/tipos-documento'),
            axios.get('/api/generos'),
            axios.get('/api/departamentos'),
        ]);
        llenarSelect('f-tipo-doc',     tiposRes.data.data   ?? tiposRes.data,   'id', 'nombre');
        llenarSelect('f-genero',       generosRes.data.data ?? generosRes.data, 'id', 'nombre');
        llenarSelect('f-departamento', deptosRes.data.data  ?? deptosRes.data,  'id', 'nombre');
    } catch { /* catálogos opcionales */ }
}

function llenarSelect(id, items, valKey, labelKey, selectedVal = null) {
    const sel = document.getElementById(id);
    sel.innerHTML = `<option value="">— Seleccionar —</option>` +
        (items ?? []).map(i =>
            `<option value="${i[valKey]}" ${String(i[valKey]) === String(selectedVal) ? 'selected' : ''}>${i[labelKey]}</option>`
        ).join('');
}

async function cargarMunicipios(selectedId = null) {
    const deptoId = document.getElementById('f-departamento').value;
    const sel = document.getElementById('f-municipio');
    if (!deptoId) { sel.innerHTML = '<option value="">— Seleccionar departamento —</option>'; return; }
    try {
        const { data } = await axios.get(`/api/municipios?departamento_id=${deptoId}`);
        llenarSelect('f-municipio', data.data ?? data, 'id', 'nombre', selectedId);
    } catch {}
}

document.getElementById('f-departamento').addEventListener('change', () => cargarMunicipios());

// ── Listar ─────────────────────────────────────────────
async function cargarPacientes(page = 1) {
    const q = document.getElementById('search').value.trim();
    document.getElementById('tbody').innerHTML =
        `<tr class="state-row"><td colspan="6">Cargando…</td></tr>`;

    try {
        const params = new URLSearchParams({ page });
        if (q) params.set('q', q);

        const { data } = await axios.get(`/api/paciente?${params}`);
        const lista    = data.data?.data ?? [];
        currentPage = data.data?.current_page ?? 1;
        totalPages  = data.data?.last_page    ?? 1;
        const total = data.data?.total        ?? 0;

        document.getElementById('total-label').textContent =
            `${total} paciente${total !== 1 ? 's' : ''} registrado${total !== 1 ? 's' : ''}`;
        document.getElementById('page-info').textContent =
            `Página ${currentPage} de ${totalPages}`;
        document.getElementById('btn-prev').disabled = currentPage <= 1;
        document.getElementById('btn-next').disabled = currentPage >= totalPages;

        if (!lista.length) {
            document.getElementById('tbody').innerHTML =
                `<tr class="state-row"><td colspan="6">No se encontraron pacientes.</td></tr>`;
            return;
        }

        document.getElementById('tbody').innerHTML = lista.map(p => `
            <tr>
                <td>
                    <span class="badge">${p.tipo_documento?.nombre ?? '—'}</span><br>
                    <strong>${p.numero_documento}</strong>
                </td>
                <td>${[p.nombre1, p.nombre2, p.apellido1, p.apellido2].filter(Boolean).join(' ')}</td>
                <td>${p.genero?.nombre ?? '—'}</td>
                <td>${p.municipio?.nombre ?? '—'}</td>
                <td style="color:var(--muted)">${p.correo ?? '—'}</td>
                <td>
                    <div class="actions">
                        <button class="btn btn-secondary btn-sm"
                            onclick="abrirEditar('${p.numero_documento}')">Editar</button>
                        <button class="btn btn-danger btn-sm"
                            onclick="confirmarEliminar('${p.numero_documento}')">Eliminar</button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch {
        document.getElementById('tbody').innerHTML =
            `<tr class="state-row"><td colspan="6">Error al cargar los pacientes.</td></tr>`;
    }
}

// ── Crear ──────────────────────────────────────────────
document.getElementById('btn-nuevo').addEventListener('click', () => {
    document.getElementById('form-paciente').reset();
    document.getElementById('f-modo').value         = 'create';
    document.getElementById('f-doc-original').value = '';
    document.getElementById('modal-title').textContent = 'Nuevo paciente';
    document.getElementById('f-numero-doc').disabled   = false;
    openModal('modal-form');
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

        document.getElementById('f-tipo-doc').value   = p.tipo_documento_id;
        document.getElementById('f-numero-doc').value = p.numero_documento;
        document.getElementById('f-nombre1').value    = p.nombre1   ?? '';
        document.getElementById('f-nombre2').value    = p.nombre2   ?? '';
        document.getElementById('f-apellido1').value  = p.apellido1 ?? '';
        document.getElementById('f-apellido2').value  = p.apellido2 ?? '';
        document.getElementById('f-genero').value     = p.genero_id;
        document.getElementById('f-departamento').value = p.departamento_id;
        await cargarMunicipios(p.municipio_id);
        document.getElementById('f-correo').value = p.correo ?? '';

        openModal('modal-form');
    } catch {
        showToast('Error al cargar el paciente.', 'error');
    }
}

// ── Guardar (crear o actualizar) ───────────────────────
document.getElementById('form-paciente').addEventListener('submit', async e => {
    e.preventDefault();

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
    };

    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    btn.textContent = 'Guardando…';

    try {
        if (modo === 'create') {
            await axios.post('/api/paciente', payload);
            showToast('Paciente creado correctamente.');
        } else {
            await axios.put(`/api/paciente/${doc}`, payload);
            showToast('Paciente actualizado correctamente.');
        }
        closeModal('modal-form');
        cargarPacientes(currentPage);
    } catch (err) {
        showToast(err.response?.data?.message ?? 'Error al guardar.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar';
    }
});

// ── Eliminar ───────────────────────────────────────────
function confirmarEliminar(doc) {
    deleteTarget = doc;
    openModal('modal-confirm');
}

document.getElementById('btn-confirm-delete').addEventListener('click', async () => {
    if (!deleteTarget) return;
    try {
        await axios.delete(`/api/paciente/${deleteTarget}`);
        showToast('Paciente eliminado.');
        closeModal('modal-confirm');
        cargarPacientes(currentPage);
    } catch {
        showToast('Error al eliminar.', 'error');
    } finally {
        deleteTarget = null;
    }
});

// ── Paginación ─────────────────────────────────────────
document.getElementById('btn-prev').addEventListener('click', () => cargarPacientes(currentPage - 1));
document.getElementById('btn-next').addEventListener('click', () => cargarPacientes(currentPage + 1));

// ── Búsqueda con debounce ──────────────────────────────
document.getElementById('search').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => cargarPacientes(1), 400);
});

// ── Init ───────────────────────────────────────────────
cargarCatalogos();
cargarPacientes();
</script>
</body>
</html>