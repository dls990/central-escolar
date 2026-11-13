<?php
/**
 * Configurações de Banco de Dados
 * Suporte nativo a MySQL (XAMPP / WampServer / Servidor Web) e SQLite
 */

return [
    // Driver padrão: 'mysql' (ou 'sqlite')
    'driver' => 'mysql',

    // Configurações do MySQL (Padrão para XAMPP / MariaDB / WampServer)
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'central_escolar',
        'username' => 'root',
        'password' => '', // No XAMPP a senha padrão do root é vazia
        'charset' => 'utf8mb4'
    ],

    // Configurações do SQLite (Portátil / Fallback de contingência)
    'sqlite' => [
        'database' => __DIR__ . '/../database/escola.sqlite'
    ]
];
