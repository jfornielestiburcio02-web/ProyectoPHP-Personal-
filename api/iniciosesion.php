<?php
// ============================================================
//  BACKEND — Firebase REST API (invisible en el HTML)
// ============================================================
session_start();

$firebaseConfig = [
    'apiKey'    => 'AIzaSyB5_IFSGErvJO_RfFYhuz4zSJQMBQvPePo',
    'projectId' => 'proyectolegalescatrp',
];

// --- Captura de IP ---
function getClientIP(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

// --- Consulta Firestore: buscar usuario por campo "cnp" ---
function buscarUsuarioPorCNP(array $cfg, string $cnp): ?array {
    $url = "https://firestore.googleapis.com/v1/projects/{$cfg['projectId']}/databases/(default)/documents:runQuery?key={$cfg['apiKey']}";

    $body = json_encode([
        'structuredQuery' => [
            'from'  => [['collectionId' => 'usuarios']],
            'where' => [
                'fieldFilter' => [
                    'field' => ['fieldPath' => 'cnp'],
                    'op'    => 'EQUAL',
                    'value' => ['stringValue' => strtoupper(trim($cnp))]
                ]
            ],
            'limit' => 1
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($resp, true);
    // runQuery devuelve array; el primer elemento con "document" es el resultado
    if (!empty($data[0]['document']['fields'])) {
        return $data[0]['document']['fields'];
    }
    return null;
}

// --- Extraer valor string de campo Firestore ---
function fStr(array $fields, string $key): string {
    return $fields[$key]['stringValue'] ?? '';
}

// --- Extraer array de valores de campo Firestore ---
function fArray(array $fields, string $key): array {
    $values = [];
    $items  = $fields[$key]['arrayValue']['values'] ?? [];
    foreach ($items as $item) {
        if (isset($item['stringValue'])) $values[] = $item['stringValue'];
    }
    return $values;
}

// ============================================================
//  PROCESO DE LOGIN (POST)
// ============================================================
$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cnpInput  = trim($_POST['cnp']  ?? '');
    $passInput = trim($_POST['pass'] ?? '');

    if ($cnpInput === '' || $passInput === '') {
        $error = 'Introduzca su CNP y contraseña.';
    } else {
        $usuario = buscarUsuarioPorCNP($firebaseConfig, $cnpInput);

        if ($usuario === null) {
            $error = 'Identificador no encontrado.';
        } else {
            $passDB     = fStr($usuario, 'contrasena');
            $perfiles   = fArray($usuario, 'perfilesCNP');
            $tieneLAE   = in_array('lae', $perfiles, true);

            if ($passInput !== $passDB) {
                $error = 'Contraseña incorrecta.';
            } elseif (!$tieneLAE) {
                $error = 'Acceso denegado: no tiene perfil LAE asignado.';
            } else {
                // ✅ LOGIN OK
                $_SESSION['lae_auth']       = true;
                $_SESSION['lae_cnp']        = fStr($usuario, 'cnp');
                $_SESSION['lae_nombre']     = fStr($usuario, 'nombreAgente');
                $_SESSION['lae_ip']         = getClientIP();
                $_SESSION['lae_login_time'] = date('Y-m-d H:i:s');
                header('Location: /inicio_lae.php');
                exit;
            }
        }
    }
}
// ============================================================
//  FIN BACKEND
// ============================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LAE — Acceso Identificado</title>
  <link href="https://fonts.googleapis.com/css2?family=Verdana&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --azul:       #003082;
      --azul-medio: #0046b8;
      --azul-claro: #e8eef8;
      --oro:        #c8a84b;
      --rojo:       #b91c1c;
      --blanco:     #ffffff;
      --gris-fondo: #f0f3f9;
    }

    html, body {
      min-height: 100vh;
      font-family: 'Verdana', Geneva, Tahoma, sans-serif;
      background: var(--gris-fondo);
      display: flex;
      flex-direction: column;
    }

    /* ── HEADER ── */
    header {
      background: var(--azul);
      box-shadow: 0 2px 12px rgba(0,0,0,.3);
    }
    .header-inner {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 24px;
      height: 64px;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .header-logo {
      height: 44px;
      width: 44px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,.3);
      background: #fff;
      padding: 2px;
      object-fit: contain;
    }
    .header-titles .org  { font-size: 9px;  letter-spacing: 2.5px; text-transform: uppercase; color: var(--oro); font-weight: 700; }
    .header-titles .name { font-size: 14px; font-weight: 700; color: #fff; letter-spacing: .3px; }

    /* ── HERO STRIPE ── */
    .stripe {
      background: linear-gradient(110deg, var(--azul) 55%, var(--azul-medio));
      padding: 36px 24px 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .stripe::before {
      content: '';
      position: absolute;
      inset: 0;
      background: repeating-linear-gradient(-55deg,transparent,transparent 18px,rgba(255,255,255,.03) 18px,rgba(255,255,255,.03) 36px);
    }
    .stripe-logo {
      width: 70px; height: 70px;
      border-radius: 50%;
      border: 3px solid var(--oro);
      background: #fff;
      padding: 4px;
      object-fit: contain;
      margin-bottom: 14px;
      box-shadow: 0 4px 18px rgba(0,0,0,.35);
      animation: drop .5s ease both;
      position: relative;
    }
    @keyframes drop {
      from { opacity:0; transform: translateY(-16px); }
      to   { opacity:1; transform: translateY(0); }
    }
    .stripe h1 {
      font-size: 20px;
      color: #fff;
      font-weight: 700;
      letter-spacing: .4px;
      position: relative;
      animation: drop .5s .1s ease both;
    }
    .stripe .sub {
      font-size: 10px;
      color: var(--oro);
      letter-spacing: 3px;
      text-transform: uppercase;
      margin-top: 6px;
      position: relative;
      animation: drop .5s .18s ease both;
    }

    /* ── CARD LOGIN ── */
    main {
      flex: 1;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 36px 16px 60px;
    }

    .card {
      background: var(--blanco);
      border: 1px solid #d1daea;
      border-radius: 12px;
      padding: 36px 36px 32px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 6px 28px rgba(0,48,130,.11);
      animation: drop .45s .25s ease both;
    }

    .card-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--azul);
      letter-spacing: .5px;
      text-align: center;
      margin-bottom: 6px;
    }
    .card-sub {
      font-size: 10.5px;
      color: #9ca3af;
      text-align: center;
      margin-bottom: 28px;
      letter-spacing: .3px;
    }

    /* ── ALERTA ERROR ── */
    .alert {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: #fef2f2;
      border: 1px solid #fca5a5;
      border-left: 4px solid var(--rojo);
      border-radius: 7px;
      padding: 12px 14px;
      margin-bottom: 22px;
      font-size: 11.5px;
      color: #991b1b;
      line-height: 1.5;
    }
    .alert-icon { font-size: 15px; flex-shrink: 0; margin-top: 1px; }

    /* ── CAMPOS ── */
    .field { margin-bottom: 18px; }
    .field label {
      display: block;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #6b7280;
      margin-bottom: 7px;
    }
    .field input {
      width: 100%;
      padding: 13px 14px;
      border: 1.5px solid #d1d5db;
      border-radius: 7px;
      font-family: 'Verdana', Geneva, Tahoma, sans-serif;
      font-size: 13px;
      color: #111827;
      background: #f9fafc;
      transition: border-color .2s, box-shadow .2s;
      outline: none;
      letter-spacing: 1px;
    }
    .field input:focus {
      border-color: var(--azul-medio);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(0,70,184,.12);
    }

    /* ── BOTÓN ── */
    .btn-login {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, var(--azul-medio), var(--azul));
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'Verdana', Geneva, Tahoma, sans-serif;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .5px;
      cursor: pointer;
      margin-top: 6px;
      box-shadow: 0 4px 14px rgba(0,48,130,.25);
      transition: filter .2s, transform .15s;
    }
    .btn-login:hover  { filter: brightness(1.1); transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); filter: brightness(.97); }

    /* ── AVISO LEGAL ── */
    .legal {
      margin-top: 22px;
      font-size: 10px;
      color: #9ca3af;
      text-align: center;
      line-height: 1.65;
      border-top: 1px solid #e5e7eb;
      padding-top: 16px;
    }
    .legal strong { color: #6b7280; }

    /* ── FOOTER ── */
    footer {
      background: var(--azul);
      color: rgba(255,255,255,.4);
      text-align: center;
      font-size: 9.5px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 14px 24px;
    }
    footer span { color: var(--oro); }
  </style>
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="header-inner">
      <img class="header-logo"
           src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRnYp5u3ETEym1h5oNHzUuk4LgDCbXXbZ3Qg&s"
           alt="CNP" />
      <div class="header-titles">
        <div class="org">Ministerio del Interior</div>
        <div class="name">Cuerpo Nacional de Policía</div>
      </div>
    </div>
  </header>

  <!-- HERO -->
  <div class="stripe">
    <img class="stripe-logo"
         src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRnYp5u3ETEym1h5oNHzUuk4LgDCbXXbZ3Qg&s"
         alt="CNP" />
    <h1>Acceso a LAE</h1>
    <p class="sub">Laboratorio de Actuaciones Especiales</p>
  </div>

  <!-- FORMULARIO -->
  <main>
    <div class="card">
      <p class="card-title">🔐 Identificación de Agente</p>
      <p class="card-sub">Introduzca sus credenciales de acceso</p>

      <?php if ($error !== ''): ?>
      <div class="alert">
        <span class="alert-icon">⚠️</span>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="" autocomplete="off">
        <div class="field">
          <label for="cnp">Número CNP</label>
          <input type="text"
                 id="cnp"
                 name="cnp"
                 placeholder="Ej: ALAREZR"
                 value="<?= htmlspecialchars($_POST['cnp'] ?? '') ?>"
                 maxlength="30"
                 required
                 autocomplete="off"
                 spellcheck="false" />
        </div>

        <div class="field">
          <label for="pass">Contraseña</label>
          <input type="password"
                 id="pass"
                 name="pass"
                 placeholder="••••••••"
                 maxlength="50"
                 required
                 autocomplete="new-password" />
        </div>

        <button type="submit" class="btn-login">
          <span>🔓</span> Acceder al sistema
        </button>
      </form>

      <p class="legal">
        <strong>Acceso restringido a personal autorizado.</strong><br>
        El uso indebido de este sistema constituye una infracción<br>
        tipificada en la legislación vigente.
      </p>
    </div>
  </main>

  <footer>
    &copy; <span>Cuerpo Nacional de Policía</span> &mdash; Uso Interno Restringido
  </footer>

</body>
</html>
