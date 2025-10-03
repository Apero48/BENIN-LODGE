<?php
// php/api/query.php
// Endpoint générique sécurisé pour interrogations prédéfinies
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Vérification simple d'un token d'API (défini dans les variables d'environnement)
$apiToken = getenv('API_TOKEN') ?: null;
$provided = null;
// Supporter header X-API-KEY ou param api_key
foreach (getallheaders() as $k => $v) {
    if (strtolower($k) === 'x-api-key') { $provided = $v; break; }
}
if (!$provided && isset($_GET['api_key'])) $provided = $_GET['api_key'];

if ($apiToken && $provided !== $apiToken) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

include_once __DIR__ . '/../models/Hotel.php';
include_once __DIR__ . '/../models/RoomType.php';
include_once __DIR__ . '/../models/Reservation.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'list_hotels':
        $hotel = new Hotel();
        $stmt = $hotel->read();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $rows]);
        break;

    case 'get_hotel':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'id manquant']); break; }
        $hotel = new Hotel();
        $hotel->id_hotel = $id;
        $stmt = $hotel->read_single();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) echo json_encode($row); else { http_response_code(404); echo json_encode(['error'=>'Introuvable']); }
        break;

    case 'list_room_types':
        $rt = new RoomType();
        $stmt = $rt->read();
        echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    case 'list_reservations':
        $r = new Reservation();
        $stmt = $r->read();
        echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    default:
        http_response_code(400);
        echo json_encode(["error"=>"action inconnue", "available"=>["list_hotels","get_hotel","list_room_types","list_reservations"]]);
}

?>
