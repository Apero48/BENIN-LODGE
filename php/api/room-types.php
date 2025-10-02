<?php
// php/api/room-types.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/RoomType.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getRoomTypes();
        break;
    case 'POST':
        createRoomType();
        break;
    case 'PUT':
        updateRoomType();
        break;
    case 'DELETE':
        deleteRoomType();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Méthode non autorisée"));
}

function getRoomTypes() {
    $roomType = new RoomType();
    $stmt = $roomType->read();
    $num = $stmt->rowCount();
    
    if($num > 0) {
        $room_types_arr = array();
        $room_types_arr["data"] = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $room_type_item = array(
                "id_type" => $id_type,
                "nom_type" => $nom_type,
                "description" => $description,
                "tarif_nuit" => $tarif_nuit,
                "capacite_personnes" => $capacite_personnes
            );
            array_push($room_types_arr["data"], $room_type_item);
        }
        
        http_response_code(200);
        echo json_encode($room_types_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Aucun type de chambre trouvé."));
    }
}

function createRoomType() {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->nom_type) && !empty($data->tarif_nuit) && !empty($data->capacite_personnes)) {
        $roomType = new RoomType();
        
        $roomType->nom_type = $data->nom_type;
        $roomType->description = $data->description ?? '';
        $roomType->tarif_nuit = $data->tarif_nuit;
        $roomType->capacite_personnes = $data->capacite_personnes;
        
        if($roomType->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Type de chambre créé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer le type de chambre."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes. Le nom, le tarif et la capacité sont requis."));
    }
}

function updateRoomType() {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->id_type) && !empty($data->nom_type) && !empty($data->tarif_nuit)) {
        $roomType = new RoomType();
        
        $roomType->id_type = $data->id_type;
        $roomType->nom_type = $data->nom_type;
        $roomType->description = $data->description ?? '';
        $roomType->tarif_nuit = $data->tarif_nuit;
        $roomType->capacite_personnes = $data->capacite_personnes;
        
        if($roomType->update()) {
            http_response_code(200);
            echo json_encode(array("message" => "Type de chambre mis à jour avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de mettre à jour le type de chambre."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes."));
    }
}

function deleteRoomType() {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->id_type)) {
        $roomType = new RoomType();
        $roomType->id_type = $data->id_type;
        
        if($roomType->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Type de chambre supprimé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de supprimer le type de chambre."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "ID type de chambre manquant."));
    }
}
?>