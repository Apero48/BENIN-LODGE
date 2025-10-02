<?php
// php/models/Reservation.php
require_once '../config/database.php';

class Reservation {
    private $conn;
    private $table = 'reservations';

    public $id_reservation;
    public $nom_client;
    public $telephone_client;
    public $email_client;
    public $id_hotel;
    public $id_type;
    public $date_arrivee;
    public $date_depart;
    public $nombre_nuits;
    public $montant_total;
    public $statut;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function read() {
        $query = "SELECT r.*, h.nom_hotel, t.nom_type 
                  FROM " . $this->table . " r
                  LEFT JOIN hotels h ON r.id_hotel = h.id_hotel
                  LEFT JOIN types_chambres t ON r.id_type = t.id_type
                  ORDER BY r.date_creation DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nom_client=:nom_client, telephone_client=:telephone_client, 
                      email_client=:email_client, id_hotel=:id_hotel, id_type=:id_type,
                      date_arrivee=:date_arrivee, date_depart=:date_depart,
                      nombre_nuits=:nombre_nuits, montant_total=:montant_total, statut=:statut";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->nom_client = htmlspecialchars(strip_tags($this->nom_client));
        $this->telephone_client = htmlspecialchars(strip_tags($this->telephone_client));
        $this->email_client = htmlspecialchars(strip_tags($this->email_client));
        
        // Liaison des paramètres
        $stmt->bindParam(":nom_client", $this->nom_client);
        $stmt->bindParam(":telephone_client", $this->telephone_client);
        $stmt->bindParam(":email_client", $this->email_client);
        $stmt->bindParam(":id_hotel", $this->id_hotel);
        $stmt->bindParam(":id_type", $this->id_type);
        $stmt->bindParam(":date_arrivee", $this->date_arrivee);
        $stmt->bindParam(":date_depart", $this->date_depart);
        $stmt->bindParam(":nombre_nuits", $this->nombre_nuits);
        $stmt->bindParam(":montant_total", $this->montant_total);
        $stmt->bindParam(":statut", $this->statut);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table . " 
                  SET statut = :statut 
                  WHERE id_reservation = :id_reservation";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':id_reservation', $this->id_reservation);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>