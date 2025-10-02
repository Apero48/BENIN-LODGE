<?php
// php/api/hotels.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Hotel.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getHotels();
        break;
    case 'POST':
        createHotel();
        break;
    case 'PUT':
        updateHotel();
        break;
    case 'DELETE':
        deleteHotel();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Méthode non autorisée"));
}

function getHotels() {
    $hotel = new Hotel();
    $stmt = $hotel->read();
    $num = $stmt->rowCount();
    
    if($num > 0) {
        $hotels_arr = array();
        $hotels_arr["data"] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $hotel_item = array(
                "id_hotel" => $id_hotel,
                "nom_hotel" => $nom_hotel,
                "ville" => $ville,
                "adresse" => $adresse,
                "telephone" => $telephone,
                "email" => $email,
                "etoiles" => $etoiles
            );
            array_push($hotels_arr["data"], $hotel_item);
        }
        
        http_response_code(200);
        echo json_encode($hotels_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Aucun hôtel trouvé."));
    }
}

function createHotel() {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->nom_hotel) && !empty($data->ville)) {
        $hotel = new Hotel();
        
        $hotel->nom_hotel = $data->nom_hotel;
        $hotel->ville = $data->ville;
        $hotel->adresse = $data->adresse ?? '';
        $hotel->telephone = $data->telephone ?? '';
        $hotel->email = $data->email ?? '';
        $hotel->etoiles = $data->etoiles ?? 3;
        
        if($hotel->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Hôtel créé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer l'hôtel."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes. Le nom et la ville sont requis."));
    }
}

function updateHotel() {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->id_hotel) && !empty($data->nom_hotel) && !empty($data->ville)) {
        $hotel = new Hotel();
        
        $hotel->id_hotel = $data->id_hotel;
        $hotel->nom_hotel = $data->nom_hotel;
        $hotel->ville = $data->ville;
        $hotel->adresse = $data->adresse ?? '';
        $hotel->telephone = $data->telephone ?? '';
        $hotel->email = $data->email ?? '';
        $hotel->etoiles = $data->etoiles ?? 3;
        
        if($hotel->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Hôtel mis à jour avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de mettre à jour l'hôtel."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes."));
    }
}

function deleteHotel() {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->id_hotel)) {
        $hotel = new Hotel();
        $hotel->id_hotel = $data->id_hotel;
        
        if($hotel->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Hôtel supprimé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de supprimer l'hôtel."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "ID hôtel manquant."));
    }
}
?>