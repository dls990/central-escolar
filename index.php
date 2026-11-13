<?php
/**
 * Redirecionador raiz para a pasta /public
 */
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = rtrim($scriptDirectory, '/');

header('Location: ' . $basePath . '/public/');
exit;
