<?php
// php/config/database.php
/**
 * Database connection helper for Microsoft SQL Server.
 *
 * Utilise PDO (pdo_sqlsrv) si disponible, sinon bascule vers l'extension sqlsrv.
 * Prérequis : installer et activer l'extension pdo_sqlsrv ou sqlsrv pour PHP.
 */
class Database {
    // Par défaut, utiliser le nom de service Docker 'db' et le port 1433.
    // On lit aussi les variables d'environnement pour faciliter la configuration.
    private $server;
    private $db_name;
    private $username;
    private $password;
    // $conn peut être un objet PDO ou une ressource sqlsrv
    public $conn;

    public function getConnection() {
        $this->conn = null;

        // Charger les valeurs depuis les variables d'environnement si présentes
        $this->server = getenv('DB_SERVER') ?: 'db,1433';
        $this->db_name = getenv('DB_NAME') ?: 'benin_lodge_db';
        $this->username = getenv('DB_USER') ?: 'SA';
        $this->password = getenv('DB_PASSWORD') ?: 'YourStrong!Passw0rd';

        // 1) Essayer PDO avec driver sqlsrv
        if (extension_loaded('pdo')) {
            try {
                // Vérifie si le driver sqlsrv est disponible
                if (in_array('sqlsrv', PDO::getAvailableDrivers())) {
                    // Utiliser Encrypt=No et TrustServerCertificate=Yes pour le réseau Docker local
                    $dsn = "sqlsrv:Server=" . $this->server . ";Database=" . $this->db_name . ";Encrypt=No;TrustServerCertificate=Yes";
                    $this->conn = new PDO($dsn, $this->username, $this->password);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    return $this->conn;
                }
            } catch (PDOException $e) {
                // Log et fallback
                error_log("PDO SQLSRV connection failed: " . $e->getMessage());
            }
        }

        // 2) Fallback vers l'extension sqlsrv (procédural)
        if (function_exists('sqlsrv_connect')) {
            $connectionInfo = array(
                "Database" => $this->db_name,
                "UID" => $this->username,
                "PWD" => $this->password,
                // Options pour éviter les erreurs TLS dans un réseau local
                "Encrypt" => 0,
                "TrustServerCertificate" => 1
            );
            $serverName = $this->server;
            $conn = sqlsrv_connect($serverName, $connectionInfo);
            if ($conn === false) {
                $errors = sqlsrv_errors();
                $msg = "";
                if ($errors !== null) {
                    foreach ($errors as $error) {
                        $msg .= "SQLSTATE: " . ($error['SQLSTATE'] ?? '') . " - " . ($error['message'] ?? '') . "; ";
                    }
                }
                echo "Erreur de connexion sqlsrv: " . $msg;
            } else {
                $this->conn = $conn;
            }
            return $this->conn;
        }

        echo "Aucune extension disponible pour se connecter à SQL Server. Installez pdo_sqlsrv ou sqlsrv.";
        return null;
    }
}

/*
Example d'utilisation :
require_once __DIR__ . '/database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    // Si PDO
    if ($conn instanceof PDO) {
        $stmt = $conn->query("SELECT 1 AS test");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        var_dump($row);
    } else {
        // Ressource sqlsrv
        $sql = "SELECT 1 AS test";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt !== false) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            var_dump($row);
        } else {
            var_dump(sqlsrv_errors());
        }
    }
}
*/

?>