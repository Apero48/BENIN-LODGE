<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: text/plain; charset=utf-8');

if (!$conn) {
    echo "Échec de la connexion\n";
    exit(1);
}

if ($conn instanceof PDO) {
    try {
        $stmt = $conn->query('SELECT GETDATE() AS now');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Connexion PDO réussie. GETDATE() = " . ($row['now'] ?? 'n/a') . "\n";
    } catch (Exception $e) {
        echo "Erreur PDO: " . $e->getMessage() . "\n";
    }
} else {
    $sql = 'SELECT GETDATE() AS now';
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        echo "Erreur sqlsrv: \n";
        var_dump(sqlsrv_errors());
    } else {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        echo "Connexion sqlsrv réussie. GETDATE() = " . ($row['now'] ?? 'n/a') . "\n";
    }
}

?>
