<?php
// Configura a conexao com o banco de dados.






return [

    // Escolha 'mysql' para XAMPP ou 'sqlite' para usar um banco em arquivo.
    'driver' => 'mysql',


    // Credenciais do servidor MySQL local.
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'central_escolar',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],


    // Caminho do arquivo usado quando o driver SQLite esta selecionado.
    'sqlite' => [
        'database' => __DIR__ . '/../database/escola.sqlite'
    ]
];
