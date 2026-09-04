<?php
// Verificación temporal: login + render del dashboard sin warnings.
$base = 'http://localhost/gestion-citaslc';
$cookieJar = __DIR__ . '/prueba-cookies.txt';
@unlink($cookieJar);

function request(string $method, string $url, array $post = [], string $cookieJar = ''): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($cookieJar !== '') {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$status, (string) $response];
}

// Login
[$status, $response] = request('GET', $base . '/login', [], $cookieJar);
preg_match('/name="_token" value="([^"]+)"/', $response, $m);
$token = $m[1] ?? '';
[$status, $response] = request('POST', $base . '/login', [
    '_token' => $token,
    'email' => 'admin@citas.local',
    'password' => 'Admin123*',
], $cookieJar);
echo "POST /login -> $status\n";

// Dashboard autenticado
[$status, $response] = request('GET', $base . '/', [], $cookieJar);
$body = substr($response, (int) strpos($response, "\r\n\r\n") + 4);
echo "GET / -> $status\n";

$issues = [];
if (stripos($body, 'Warning') !== false) $issues[] = 'Contiene "Warning"';
if (stripos($body, 'Undefined variable') !== false) $issues[] = 'Contiene "Undefined variable"';
if (stripos($body, 'Fatal error') !== false) $issues[] = 'Contiene "Fatal error"';
if (substr_count($body, '<html') !== 1) $issues[] = 'HTML anidado (count <html> = ' . substr_count($body, '<html') . ')';
if (strpos($body, 'Pacientes registrados') === false) $issues[] = 'Falta "Pacientes registrados"';
if (strpos($body, 'Citas activas') === false) $issues[] = 'Falta "Citas activas"';
if (strpos($body, 'Citas para hoy') === false) $issues[] = 'Falta "Citas para hoy"';
if (strpos($body, 'Panel principal') === false) $issues[] = 'Falta "Panel principal"';

if ($issues) {
    echo "PROBLEMAS DETECTADOS:\n- " . implode("\n- ", $issues) . "\n";
} else {
    echo "RESULTADO: DASHBOARD OK - sin warnings y con las 3 tarjetas\n";
}
@unlink($cookieJar);
