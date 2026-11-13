<?php
// Define recursos compartilhados pelos modelos.




namespace Core;

use App\Models\Database;
use PDO;

abstract class Model {
    protected static ?PDO $db = null;

    // Abre a conexao compartilhada somente quando um modelo precisar dela.
    protected static function getConnection(): PDO {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
}
