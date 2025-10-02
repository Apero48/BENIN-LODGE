<?php
require_once __DIR__ . '/config/database.php';
$db = new Database();
// Se connecter sans database pour créer la base
putenv('DB_NAME=master');
$conn = $db->getConnection();
if ($conn instanceof PDO) {
    try {
        $conn->exec('CREATE DATABASE [benin_lodge_db];');
        echo "Database created via PDO\n";
    } catch (PDOException $e) {
        echo "PDO create DB failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "No PDO connection available\n";
}
