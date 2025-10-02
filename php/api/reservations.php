<?php
// php/api/reservations.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Reservation.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getReservations();
        break;
    case 'POST':
        createReservation();
        break;
    case 'PUT':
        updateReservation();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Méthode non autorisée"));
}

function getReservations() {
    $reservation = new Reservation();
    $stmt = $reservation->read();
    $num = $stmt->rowCount();
    
    if($num > 0) {
        $reservations_arr = array();
        $reservations_arr["data"] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $reservation_item = array(
                "id_reservation" => $id_reservation,
                "nom_client" => $nom_client,
                "telephone_client" => $telephone_client,
                "email_client" => $email_client,
                "nom_hotel" => $nom_hotel,
                "nom_type" => $nom_type,
                "date_arrivee" => $date_arrivee,
                "date_depart" => $date_depart,
                "nombre_nuits" => $nombre_nuits,
                "montant_total" => $montant_total,
                "statut" => $statut
            );
            array_push($reservations_arr["data"], $reservation_item);
        }
        
        http_response_code(200);
        echo json_encode($reservations_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Aucune réservation trouvée."));
    }
}

function createReservation() {
    $data = json_decode(file_get_contents("php://input"));
    
    if(
        !empty($data->nom_client) &&
        !empty($data->telephone_client) &&
        !empty($data->id_hotel) &&
        !empty($data->id_type) &&
        !empty($data->date_arrivee) &&
        !empty($data->date_depart)
    ) {
        $reservation = new Reservation();
        
        $reservation->nom_client = $data->nom_client;
        $reservation->telephone_client = $data->telephone_client;
        $reservation->email_client = $data->email_client;
        $reservation->id_hotel = $data->id_hotel;
        $reservation->id_type = $data->id_type;
        $reservation->date_arrivee = $data->date_arrivee;
        $reservation->date_depart = $data->date_depart;
        $reservation->nombre_nuits = $data->nombre_nuits;
        $reservation->montant_total = $data->montant_total;
        $reservation->statut = $data->statut;
        
        if($reservation->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Réservation créée avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer la réservation."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes."));
    }
}
?>