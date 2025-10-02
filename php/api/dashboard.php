<?php
// php/api/dashboard.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

function getDashboardData() {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Statistiques principales
    $stats = array();
    
    // Total réservations
    $query = "SELECT COUNT(*) as total FROM reservations";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['totalReservations'] = $row['total'];
    
    // Réservations confirmées
    $query = "SELECT COUNT(*) as confirmed FROM reservations WHERE statut = 'confirmée'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['confirmed'] = $row['confirmed'];
    
    // Réservations en attente
    $query = "SELECT COUNT(*) as pending FROM reservations WHERE statut = 'en attente'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending'] = $row['pending'];
    
    // Revenus du mois
    $query = "SELECT COALESCE(SUM(montant_total), 0) as revenue 
              FROM reservations 
              WHERE MONTH(date_creation) = MONTH(CURRENT_DATE()) 
              AND YEAR(date_creation) = YEAR(CURRENT_DATE())";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['revenue'] = $row['revenue'];
    
    // Données pour les graphiques
    $charts = array();
    
    // Revenus mensuels (exemple de données)
    $charts['revenueData'] = array(
        'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        'data' => [1200, 1900, 1500, 2450, 2000, 2300, 2800, 2600, 2200, 2500, 2700, 3000]
    );
    
    // Taux d'occupation
    $charts['occupancyData'] = array(
        'occupied' => 72,
        'available' => 28
    );
    
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "stats" => $stats,
        "charts" => $charts
    ));
}

getDashboardData();
?>