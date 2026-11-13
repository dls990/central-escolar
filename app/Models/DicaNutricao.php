<?php
// Gerencia as dicas de nutricao.

namespace App\Models;

use Core\Model;
use PDO;

class DicaNutricao extends Model {
    public static function all(): array {
        $stmt = self::getConnection()->query("SELECT * FROM dicas_nutricao ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public static function getAleatoria(): array {
        $db = self::getConnection();
        $funcaoAleatoria = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'RANDOM()' : 'RAND()';
        $stmt = $db->query("SELECT * FROM dicas_nutricao ORDER BY {$funcaoAleatoria} LIMIT 1");
        $dica = $stmt->fetch();
        return $dica ?: [
            'titulo' => 'Coma Colorido!',
            'dica' => 'Colocar cores no prato deixa o corpo forte e a mente afiada!',
            'icone' => '🍎'
        ];
    }
}
