<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Pacientes — Iniciar sesión</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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
            --radius:    16px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(124,111,212,.4) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(90,80,184,.5) 0%, transparent 50%);
        }

        .login-wrapper {
            display: flex;
            width: min(880px, 95vw);
            min-height: 460px;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,.4);
            border: 1px solid var(--border);
        }

        .login-side {
            flex: 1;
            background: linear-gradient(145deg, #5a50b8, #3d3580);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .login-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                45deg,
                rgba(255,255,255,.03) 0px,
                rgba(255,255,255,.03) 1px,
                transparent 1px,
                transparent 28px
            );
        }
        .login-side::after {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(124,111,212,.3);
        }
        .login-side h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 2.2rem;
            color: #fff;
            line-height: 1.15;
            position: relative;
            z-index: 1;
        }
        .login-side p {
            color: var(--muted);
            font-size: .9rem;
            margin-top: .6rem;
            position: relative;
            z-index: 1;
        }

        .login-form {
            flex: 1;
            background: rgba(74,63,159,.85);
            backdrop-filter: blur(20px);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid var(--border);
        }
        .login-form h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 1.75rem;
            color: #fff;
            margin-bottom: .3rem;
        }
        .login-form .subtitle {
            color: var(--muted);
            font-size: .875rem;
            margin-bottom: 2rem;
        }

        .form-group label {
            font-size: .75rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .3rem;
        }
        .form-control {
            border: 1.5px solid var(--border) !important;
            border-radius: 10px !important;
            background: rgba(255,255,255,.1) !important;
            color: #fff !important;
            font-family: 'DM Sans', sans-serif !important;
            font-size: .9rem !important;
            padding: .65rem .9rem !important;
            transition: border-color .2s !important;
        }
        .form-control::placeholder { color: rgba(255,255,255,.35) !important; }
        .form-control:focus {
            border-color: var(--light) !important;
            box-shadow: 0 0 0 3px rgba(143,133,216,.2) !important;
            background: rgba(255,255,255,.15) !important;
        }

        .btn-login {
            width: 100%;
            padding: .7rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .95rem;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            margin-top: .5rem;
            transition: background .2s, transform .1s;
        }
        .btn-login:hover { background: var(--accent-h); transform: translateY(-1px); }
        .btn-login:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        .error-msg {
            background: rgba(185,28,28,.3);
            border: 1px solid rgba(185,28,28,.5);
            color: #fca5a5;
            border-radius: 10px;
            padding: .65rem 1rem;
            font-size: .85rem;
            margin-top: 1rem;
            display: none;
        }

        @media (max-width: 600px) {
            .login-side { display: none; }
            .login-form  { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-side">
        <h2>Gestor de<br>Pacientes</h2>
        <p>Sistema de gestión clínica — acceso seguro con JWT</p>
    </div>
    <div class="login-form">
        <h1>Bienvenido</h1>
        <p class="subtitle">Inicia sesión para continuar</p>

        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" class="form-control" id="email"
                   placeholder="admin@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" class="form-control" id="password"
                   placeholder="••••••••" autocomplete="current-password">
        </div>

        <button type="button" class="btn-login" id="btn-login">Iniciar sesión</button>
        <div class="error-msg" id="error-msg"></div>
    </div>
</div>

<script>
    localStorage.removeItem('access_token');

    const btn      = document.getElementById('btn-login');
    const errorMsg = document.getElementById('error-msg');

    btn.addEventListener('click', async function () {
        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        errorMsg.style.display = 'none';

        if (!email || !password) {
            errorMsg.textContent   = 'Por favor completa todos los campos.';
            errorMsg.style.display = 'block';
            return;
        }

        btn.disabled    = true;
        btn.textContent = 'Ingresando…';

        try {
            const { data } = await axios.post('/api/login', { email, password });
            localStorage.setItem('access_token', data.access_token);
            window.location.href = '/pacientes';
        } catch (err) {
            const msg = err.response?.data?.message ?? 'Credenciales incorrectas.';
            errorMsg.textContent   = msg;
            errorMsg.style.display = 'block';
            btn.disabled    = false;
            btn.textContent = 'Iniciar sesión';
        }
    });
</script>
</body>
</html>