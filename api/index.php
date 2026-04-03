<?php
// ============================================================
//  BACKEND: Firebase REST API — invisible en el HTML
// ============================================================

$firebaseConfig = [
    'apiKey'    => 'AIzaSyB5_IFSGErvJO_RfFYhuz4zSJQMBQvPePo',
    'projectId' => 'proyectolegalescatrp',
    'dbUrl'     => 'https://proyectolegalescatrp-default-rtdb.firebaseio.com'
];

// --- Captura de IP real del visitante ---
function getClientIP(): string {
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

// --- Envío a Firestore (REST) ---
function sendToFirestore(array $config, string $ip): void {
    $now      = date('Y-m-d\TH:i:sP');
    $docId    = str_replace(['.', ':'], '-', $ip) . '_' . time();
    $url      = "https://firestore.googleapis.com/v1/projects/{$config['projectId']}/databases/(default)/documents/visitas/{$docId}?key={$config['apiKey']}";

    $payload = json_encode([
        'fields' => [
            'ip'     => ['stringValue' => $ip],
            'hora'   => ['stringValue' => $now],
            'string' => ['stringValue' => $ip]   // campo "string" = ip
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// --- Ejecutar registro silencioso ---
$visitorIP = getClientIP();
sendToFirestore($firebaseConfig, $visitorIP);
// ============================================================
//  FIN BACKEND
// ============================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LAE — Cuerpo Nacional de Policía</title>
  <link href="https://fonts.googleapis.com/css2?family=Verdana&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --azul:       #003082;
      --azul-medio: #0046b8;
      --azul-claro: #e8eef8;
      --oro:        #c8a84b;
      --blanco:     #ffffff;
      --gris-fondo: #f2f4f8;
      --gris-texto: #374151;
      --sombra:     0 4px 24px rgba(0,48,130,.13);
    }

    html, body {
      height: 100%;
      font-family: 'Verdana', Geneva, Tahoma, sans-serif;
      background: var(--gris-fondo);
      color: var(--gris-texto);
    }

    /* ── HEADER ─────────────────────────────────────── */
    header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: var(--azul);
      box-shadow: 0 2px 12px rgba(0,0,0,.28);
    }

    .header-inner {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 24px;
      height: 68px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .header-brand {
      display: flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
    }

    .header-logo {
      height: 46px;
      width: 46px;
      object-fit: contain;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,.25);
      background: #fff;
      padding: 2px;
    }

    .header-titles {
      display: flex;
      flex-direction: column;
      line-height: 1.2;
    }

    .header-titles .org {
      font-size: 10px;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: var(--oro);
      font-weight: 700;
    }

    .header-titles .name {
      font-size: 15px;
      font-weight: 700;
      color: #fff;
      letter-spacing: .4px;
    }

    /* ── NAV HAMBURGUESA ────────────────────────────── */
    .hamburger {
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 5px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 8px;
      border-radius: 6px;
      transition: background .2s;
    }
    .hamburger:hover { background: rgba(255,255,255,.12); }
    .hamburger span {
      display: block;
      width: 26px;
      height: 2.5px;
      background: #fff;
      border-radius: 2px;
      transition: transform .3s, opacity .3s;
      transform-origin: center;
    }
    .hamburger.open span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity: 0; }
    .hamburger.open span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

    .nav-drawer {
      position: fixed;
      top: 68px;
      right: 0;
      width: 260px;
      height: calc(100vh - 68px);
      background: var(--blanco);
      box-shadow: -4px 0 24px rgba(0,0,0,.18);
      transform: translateX(110%);
      transition: transform .35s cubic-bezier(.4,0,.2,1);
      z-index: 99;
      display: flex;
      flex-direction: column;
      padding: 28px 0 24px;
    }
    .nav-drawer.open { transform: translateX(0); }

    .nav-drawer a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 28px;
      font-size: 13px;
      font-weight: 700;
      color: var(--azul);
      text-decoration: none;
      letter-spacing: .3px;
      border-left: 3px solid transparent;
      transition: background .18s, border-color .18s, color .18s;
    }
    .nav-drawer a:hover {
      background: var(--azul-claro);
      border-left-color: var(--azul-medio);
      color: var(--azul-medio);
    }
    .nav-drawer a .nav-icon { font-size: 16px; }

    .nav-drawer .drawer-header {
      font-size: 9px;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: #9ca3af;
      font-weight: 700;
      padding: 0 28px 12px;
      border-bottom: 1px solid #e5e7eb;
      margin-bottom: 8px;
    }

    .overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.35);
      z-index: 98;
    }
    .overlay.open { display: block; }

    /* ── HERO STRIPE ────────────────────────────────── */
    .hero-stripe {
      background: linear-gradient(100deg, var(--azul) 60%, var(--azul-medio));
      padding: 40px 24px 44px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .hero-stripe::before {
      content: '';
      position: absolute;
      inset: 0;
      background: repeating-linear-gradient(
        -55deg,
        transparent,
        transparent 18px,
        rgba(255,255,255,.03) 18px,
        rgba(255,255,255,.03) 36px
      );
    }
    .hero-stripe .escudo {
      width: 78px;
      height: 78px;
      border-radius: 50%;
      border: 3px solid var(--oro);
      object-fit: contain;
      background: #fff;
      padding: 4px;
      margin-bottom: 16px;
      position: relative;
      box-shadow: 0 4px 20px rgba(0,0,0,.35);
      animation: floatIn .6s ease both;
    }
    @keyframes floatIn {
      from { opacity:0; transform: translateY(-18px); }
      to   { opacity:1; transform: translateY(0); }
    }
    .hero-stripe h1 {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
      letter-spacing: .5px;
      position: relative;
      animation: floatIn .6s .1s ease both;
    }
    .hero-stripe .hero-sub {
      font-size: 11px;
      color: var(--oro);
      letter-spacing: 3px;
      text-transform: uppercase;
      margin-top: 6px;
      position: relative;
      animation: floatIn .6s .2s ease both;
    }

    /* ── MAIN CONTENT ───────────────────────────────── */
    main {
      max-width: 560px;
      margin: 0 auto;
      padding: 40px 20px 60px;
    }

    .section-label {
      font-size: 10px;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: #9ca3af;
      font-weight: 700;
      text-align: center;
      margin-bottom: 20px;
    }

    .btn-grid {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      padding: 16px 24px;
      border-radius: 8px;
      font-family: 'Verdana', Geneva, Tahoma, sans-serif;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .4px;
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: transform .18s, box-shadow .18s, filter .18s;
      position: relative;
      overflow: hidden;
    }
    .btn::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(rgba(255,255,255,.08), transparent);
      pointer-events: none;
    }
    .btn:hover  { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,48,130,.22); }
    .btn:active { transform: translateY(0); filter: brightness(.95); }

    .btn-primary {
      background: linear-gradient(135deg, var(--azul-medio), var(--azul));
      color: #fff;
      box-shadow: var(--sombra);
    }
    .btn-secondary {
      background: var(--blanco);
      color: var(--azul);
      border: 1.5px solid #c5d3ea;
      box-shadow: 0 2px 8px rgba(0,48,130,.08);
    }
    .btn-secondary:hover { border-color: var(--azul-medio); }

    .btn-icon { font-size: 17px; }

    /* ── AVISO ──────────────────────────────────────── */
    .aviso {
      margin-top: 32px;
      background: var(--blanco);
      border: 1px solid #dbe3f0;
      border-left: 4px solid var(--azul);
      border-radius: 8px;
      padding: 16px 20px;
      font-size: 11.5px;
      color: #6b7280;
      line-height: 1.7;
      text-align: center;
    }
    .aviso strong { color: var(--azul); }

    /* ── FOOTER ─────────────────────────────────────── */
    footer {
      background: var(--azul);
      color: rgba(255,255,255,.45);
      text-align: center;
      font-size: 10px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 16px 24px;
    }
    footer span { color: var(--oro); }
  </style>
</head>
<body>

  <!-- OVERLAY para cerrar menú -->
  <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

  <!-- ── HEADER ── -->
  <header>
    <div class="header-inner">
      <a class="header-brand" href="#">
        <img class="header-logo"
             src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRnYp5u3ETEym1h5oNHzUuk4LgDCbXXbZ3Qg&s"
             alt="Logo CNP" />
        <div class="header-titles">
          <span class="org">Ministerio del Interior</span>
          <span class="name">Cuerpo Nacional de Policía</span>
        </div>
      </a>

      <button class="hamburger" id="hamburger" aria-label="Menú" onclick="toggleMenu()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- ── MENÚ LATERAL ── -->
  <nav class="nav-drawer" id="navDrawer">
    <div class="drawer-header">Navegación</div>
    <a href="#"><span class="nav-icon">🔐</span> Acceso a LAE</a>
    <a href="#"><span class="nav-icon">📋</span> Formulario Ingreso</a>
    <a href="#"><span class="nav-icon">🗂️</span> Datos</a>
    <a href="#"><span class="nav-icon">📊</span> Resultados</a>
  </nav>

  <!-- ── HERO ── -->
  <div class="hero-stripe">
    <img class="escudo"
         src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRnYp5u3ETEym1h5oNHzUuk4LgDCbXXbZ3Qg&s"
         alt="Escudo CNP" />
    <h1>Acceso a LAE</h1>
    <p class="hero-sub">Laboratorio de Actuaciones Especiales</p>
  </div>

  <!-- ── CONTENIDO ── -->
  <main>
    <p class="section-label">Seleccione una opción</p>

    <div class="btn-grid">
      <a class="btn btn-primary" href="#">
        <span class="btn-icon">🔐</span> Acceso a LAE
      </a>
      <a class="btn btn-secondary" href="#">
        <span class="btn-icon">📋</span> Formulario Ingreso
      </a>
      <a class="btn btn-secondary" href="#">
        <span class="btn-icon">🗂️</span> Datos
      </a>
      <a class="btn btn-secondary" href="#">
        <span class="btn-icon">📊</span> Resultados
      </a>
    </div>

    <div class="aviso">
      <strong>Para acceder pulse</strong> el botón correspondiente.<br>
      Acceso autorizado únicamente a <strong>personal habilitado</strong>.<br>
      El acceso no autorizado está penado por la ley.
    </div>
  </main>

  <!-- ── FOOTER ── -->
  <footer>
    &copy; <span>Cuerpo Nacional de Policía</span> &mdash; Uso Interno Restringido
  </footer>

  <script>
    function toggleMenu() {
      const ham     = document.getElementById('hamburger');
      const drawer  = document.getElementById('navDrawer');
      const overlay = document.getElementById('overlay');
      ham.classList.toggle('open');
      drawer.classList.toggle('open');
      overlay.classList.toggle('open');
    }
  </script>

</body>
</html>