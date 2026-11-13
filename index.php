<?php
// Encaminha o acesso para a aplicacao publica.




$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = rtrim($scriptDirectory, '/');

header('Location: ' . $basePath . '/public/');
exit;
