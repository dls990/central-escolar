<?php
// Encaminha o acesso para a aplicacao publica.




$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = rtrim($scriptDirectory, '/');

// Encaminha sempre para o front controller publico do projeto.
header('Location: ' . $basePath . '/public/');
exit;
