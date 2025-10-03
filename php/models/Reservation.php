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
        // Validation stricte
        $errors = [];

        // Nettoyage de base
        $this->nom_client = trim(htmlspecialchars(strip_tags($this->nom_client)));
        $this->telephone_client = trim(htmlspecialchars(strip_tags($this->telephone_client)));
        $this->email_client = trim(htmlspecialchars(strip_tags($this->email_client)));

        // Checks
        if (empty($this->nom_client)) $errors[] = 'nom_client requis';
        if (empty($this->telephone_client)) $errors[] = 'telephone_client requis';
        if (!empty($this->email_client) && !filter_var($this->email_client, FILTER_VALIDATE_EMAIL)) $errors[] = 'email_client invalide';
        if (!is_numeric($this->id_hotel) || intval($this->id_hotel) <= 0) $errors[] = 'id_hotel invalide';
        if (!is_numeric($this->id_type) || intval($this->id_type) <= 0) $errors[] = 'id_type invalide';
        // Dates
        $d1 = DateTime::createFromFormat('Y-m-d', $this->date_arrivee);
        $d2 = DateTime::createFromFormat('Y-m-d', $this->date_depart);
        if (!$d1) $errors[] = 'date_arrivee invalide (YYYY-MM-DD)';
        if (!$d2) $errors[] = 'date_depart invalide (YYYY-MM-DD)';
        if ($d1 && $d2 && $d2 < $d1) $errors[] = 'date_depart doit être >= date_arrivee';
        if (!is_numeric($this->nombre_nuits) || intval($this->nombre_nuits) <= 0) $errors[] = 'nombre_nuits invalide';
        if (!is_numeric($this->montant_total) || floatval($this->montant_total) < 0) $errors[] = 'montant_total invalide';

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Utiliser OUTPUT INSERTED.id_reservation pour récupérer l'ID inséré (SQL Server)
        $query = "INSERT INTO " . $this->table . " (nom_client, telephone_client, email_client, id_hotel, id_type, date_arrivee, date_depart, nombre_nuits, montant_total, statut) 
                  OUTPUT INSERTED.id_reservation
                  VALUES (:nom_client, :telephone_client, :email_client, :id_hotel, :id_type, :date_arrivee, :date_depart, :nombre_nuits, :montant_total, :statut)";

        $stmt = $this->conn->prepare($query);

        // Liaison des paramètres
        $stmt->bindValue(":nom_client", $this->nom_client, PDO::PARAM_STR);
        $stmt->bindValue(":telephone_client", $this->telephone_client, PDO::PARAM_STR);
        $stmt->bindValue(":email_client", $this->email_client ?: null, PDO::PARAM_STR);
        $stmt->bindValue(":id_hotel", intval($this->id_hotel), PDO::PARAM_INT);
        $stmt->bindValue(":id_type", intval($this->id_type), PDO::PARAM_INT);
        $stmt->bindValue(":date_arrivee", $this->date_arrivee);
        $stmt->bindValue(":date_depart", $this->date_depart);
        $stmt->bindValue(":nombre_nuits", intval($this->nombre_nuits), PDO::PARAM_INT);
        $stmt->bindValue(":montant_total", number_format((float)$this->montant_total, 2, '.', ''));
        $stmt->bindValue(":statut", $this->statut ?: 'en attente', PDO::PARAM_STR);

        try {
            $ok = $stmt->execute();
            if ($ok) {
                // Le statement retourne une ligne contenant id_reservation
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $insertedId = $row['id_reservation'] ?? null;
                return ['success' => true, 'id' => $insertedId];
            }
            return ['success' => false, 'errors' => ['échec exécution requête']];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['exception' => $e->getMessage()]];
        }
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