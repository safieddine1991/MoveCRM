<?php
/**
 * MoveCRM — router pour le serveur de développement PHP (`php -S`).
 * Reproduit les règles de réécriture EspoCRM (normalement gérées par Apache/nginx).
 * Usage: php -S localhost:8080 router-dev.php
 * NB: à n'utiliser qu'en local. En production: Apache/nginx ou Docker.
 */

$rootDir = __DIR__;
$publicDir = __DIR__ . '/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

// 1. API: /api/v1/... -> public/api/v1/index.php
// chdir requis: l'entrée fait require('../../../bootstrap.php') relatif au cwd
if (preg_match('#^/api/v1/portal-access/#', $uri)) {
    chdir($publicDir . '/api/v1/portal-access');
    require $publicDir . '/api/v1/portal-access/index.php';
    return true;
}
if (preg_match('#^/api/v1(/.*)$#', $uri, $m)) {
    // Slim 4 ne strip pas le basePath sous php -S : on retire /api/v1 du REQUEST_URI
    // pour que basePath soit vide et que les routes (/App/user…) matchent directement.
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    $_SERVER['REQUEST_URI'] = $m[1] . ($qs !== '' ? '?' . $qs : '');
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    chdir($publicDir . '/api/v1');
    require $publicDir . '/api/v1/index.php';
    return true;
}

// 3. Fichiers statiques existants sous public/ (robots.txt, etc.)
$publicFile = $publicDir . $uri;
if ($uri !== '/' && is_file($publicFile)) {
    return false;
}

// 4. Fichiers statiques à la racine du projet (client/, install/, vendor assets…)
$rootFile = $rootDir . $uri;
if ($uri !== '/' && is_file($rootFile)) {
    return false;
}

// 5. Tout le reste -> application front (public/index.php)
chdir($publicDir);
require $publicDir . '/index.php';
return true;
