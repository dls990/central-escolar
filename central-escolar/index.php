<?php
/**
 * Redirecionador raiz para a pasta /public
 */
$uri = $_SERVER['REQUEST_URI'] ?? '/';
if (strpos($uri, 'public') === false) {
    header('Location: public/');
    exit;
}
require_once __DIR__ . '/public/index.php';
