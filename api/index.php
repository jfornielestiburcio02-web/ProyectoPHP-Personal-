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
  <title>Acceso LAE</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: #0a0d14;
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #c9d6e3;
    }

    .card {
      background: #111827;
      border: 1px solid #1f2f45;
      border-radius: 12px;
      padding: 48px 56px;
      max-width: 480px;
      width: 94%;
      text-align: center;
      box-shadow: 0 8px 40px rgba(0,0,0,.6);
    }

    .badge {
      display: inline-block;
      background: #0d2137;
      color: #4a9ede;
      font-size: 11px;
      letter-spacing: 3px;
      text-transform: uppercase;
      padding: 5px 14px;
      border-radius: 20px;
      border: 1px solid #1b4f72;
      margin-bottom: 22px;
    }

    h1 {
      font-size: 22px;
      font-weight: 700;
      color: #e8f0f7;
      letter-spacing: 1px;
      margin-bottom: 8px;
    }

    .subtitle {
      font-size: 13px;
      color: #6b7f96;
      margin-bottom: 36px;
    }

    .btn-group {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 14px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: .5px;
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: filter .2s, transform .15s;
    }
    .btn:hover  { filter: brightness(1.15); transform: translateY(-1px); }
    .btn:active { transform: translateY(0); }

    .btn-primary {
      background: linear-gradient(135deg, #1a6eb5, #0e4a80);
      color: #fff;
    }
    .btn-secondary {
      background: #1a2535;
      color: #7db8e8;
      border: 1px solid #243446;
    }

    .divider {
      border: none;
      border-top: 1px solid #1c2b3a;
      margin: 28px 0 20px;
    }

    .footer-note {
      font-size: 11px;
      color: #3b4f63;
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <div class="card">
    <span class="badge">Acceso Restringido</span>
    <h1>Acceso a LAE</h1>
    <p class="subtitle">Laboratorio de Actuaciones Especiales</p>

    <div class="btn-group">
      <a class="btn btn-primary" href="#">Acceso a LAE</a>
      <a class="btn btn-secondary" href="#">Formulario Ingreso</a>
      <a class="btn btn-secondary" href="#">Datos</a>
      <a class="btn btn-secondary" href="#">Resultados</a>
    </div>

    <hr class="divider" />

    <p class="footer-note">
      Para acceder pulse el botón correspondiente.<br>
      Acceso autorizado únicamente a personal habilitado.
    </p>
  </div>
</body>
</html>
