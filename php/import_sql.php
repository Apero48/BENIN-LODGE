<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: text/plain; charset=utf-8');

if (!$conn) {
    echo "Impossible d'obtenir une connexion\n";
    exit(1);
}

// Utilise PDO
if (!($conn instanceof PDO)) {
    echo "Le script d'import requiert une connexion PDO.\n";
    exit(1);
}

$queries = [];

// Drop tables if exist (ordre inverse des FK)
$queries[] = "IF OBJECT_ID('dbo.reservations', 'U') IS NOT NULL DROP TABLE dbo.reservations;";
$queries[] = "IF OBJECT_ID('dbo.types_chambres', 'U') IS NOT NULL DROP TABLE dbo.types_chambres;";
$queries[] = "IF OBJECT_ID('dbo.hotels', 'U') IS NOT NULL DROP TABLE dbo.hotels;";

// Create hotels
$queries[] = <<<SQL
CREATE TABLE hotels (
    id_hotel INT IDENTITY(1,1) PRIMARY KEY,
    nom_hotel NVARCHAR(255) NOT NULL,
    ville NVARCHAR(100) NOT NULL,
    adresse NVARCHAR(MAX),
    telephone NVARCHAR(50),
    email NVARCHAR(255),
    etoiles INT,
    date_creation DATETIME2 DEFAULT GETDATE()
);
SQL;

// Create types_chambres
$queries[] = <<<SQL
CREATE TABLE types_chambres (
    id_type INT IDENTITY(1,1) PRIMARY KEY,
    nom_type NVARCHAR(100) NOT NULL,
    description NVARCHAR(MAX),
    tarif_nuit DECIMAL(18,2) NOT NULL,
    capacite_personnes INT NOT NULL,
    date_creation DATETIME2 DEFAULT GETDATE()
);
SQL;

// Create reservations
$queries[] = <<<SQL
CREATE TABLE reservations (
    id_reservation INT IDENTITY(1,1) PRIMARY KEY,
    nom_client NVARCHAR(255) NOT NULL,
    telephone_client NVARCHAR(50) NOT NULL,
    email_client NVARCHAR(255),
    id_hotel INT NOT NULL,
    id_type INT NOT NULL,
    date_arrivee DATE NOT NULL,
    date_depart DATE NOT NULL,
    nombre_nuits INT NOT NULL,
    montant_total DECIMAL(18,2) NOT NULL,
    statut NVARCHAR(20) DEFAULT N'en attente',
    date_creation DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_reservations_hotels FOREIGN KEY (id_hotel) REFERENCES hotels(id_hotel),
    CONSTRAINT FK_reservations_types FOREIGN KEY (id_type) REFERENCES types_chambres(id_type)
);
SQL;

// Insert sample hotels
$queries[] = "INSERT INTO hotels (nom_hotel, ville, adresse, telephone, email, etoiles) VALUES (N'Golden Tulip Le Diplomate', N'Cotonou', N'Rue des Ambassades', N'+229 21 30 12 34', N'contact@goldentulip.bj', 4);";
$queries[] = "INSERT INTO hotels (nom_hotel, ville, adresse, telephone, email, etoiles) VALUES (N'Azalaï Hôtel de la Plage', N'Grand-Popo', N'Boulevard de la Plage', N'+229 21 30 56 78', N'info@azalai.bj', 3);";
$queries[] = "INSERT INTO hotels (nom_hotel, ville, adresse, telephone, email, etoiles) VALUES (N'Auberge de Grand-Popo', N'Grand-Popo', N'Route Côtière', N'+229 21 30 90 12', N'reservation@auberge.bj', 2);";

// Insert sample types_chambres
$queries[] = "INSERT INTO types_chambres (nom_type, description, tarif_nuit, capacite_personnes) VALUES (N'Chambre Standard', N'Confort de base avec salle de bain privée', 25000.00, 2);";
$queries[] = "INSERT INTO types_chambres (nom_type, description, tarif_nuit, capacite_personnes) VALUES (N'Chambre Supérieure', N'Plus d''espace avec vue partielle', 35000.00, 2);";
$queries[] = "INSERT INTO types_chambres (nom_type, description, tarif_nuit, capacite_personnes) VALUES (N'Suite Junior', N'Chambre avec salon séparé', 55000.00, 3);";
$queries[] = "INSERT INTO types_chambres (nom_type, description, tarif_nuit, capacite_personnes) VALUES (N'Suite Présidentielle', N'Luxe maximum avec toutes commodités', 85000.00, 4);";

// Insert sample reservations
$queries[] = "INSERT INTO reservations (nom_client, telephone_client, email_client, id_hotel, id_type, date_arrivee, date_depart, nombre_nuits, montant_total, statut) VALUES (N'AGBODIAN Koffi', N'97123456', N'koffi@email.com', 1, 3, '2025-03-15', '2025-03-18', 3, 165000.00, N'confirmée');";
$queries[] = "INSERT INTO reservations (nom_client, telephone_client, email_client, id_hotel, id_type, date_arrivee, date_depart, nombre_nuits, montant_total, statut) VALUES (N'TOGNON Marie', N'97123457', N'marie@email.com', 2, 2, '2025-03-20', '2025-03-25', 5, 175000.00, N'en attente');";
$queries[] = "INSERT INTO reservations (nom_client, telephone_client, email_client, id_hotel, id_type, date_arrivee, date_depart, nombre_nuits, montant_total, statut) VALUES (N'HOUETO Jean', N'97123458', N'jean@email.com', 3, 1, '2025-03-22', '2025-03-24', 2, 50000.00, N'confirmée');";

$errors = [];
foreach ($queries as $i => $sql) {
    try {
        $conn->exec($sql);
        echo "Executed statement #" . ($i+1) . "\n";
    } catch (PDOException $e) {
        $errors[] = "Error on statement #" . ($i+1) . ": " . $e->getMessage();
        echo "Error on statement #" . ($i+1) . ": " . $e->getMessage() . "\n";
    }
}

if (empty($errors)) {
    echo "Import SQL terminé avec succès.\n";
} else {
    echo "Import SQL terminé avec erreurs:\n";
    foreach ($errors as $err) echo $err . "\n";
}

?>
