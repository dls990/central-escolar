<?php
// Configura a conexao com o banco de dados.






return [

    'driver' => 'mysql',


    'mysql' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'central_escolar',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],


    'sqlite' => [
        'database' => __DIR__ . '/../database/escola.sqlite'
    ]
];
