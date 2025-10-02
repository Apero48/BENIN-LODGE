<?php
// php/models/Hotel.php
require_once '../config/database.php';

class Hotel {
    private $conn;
    private $table = 'hotels';

    public $id_hotel;
    public $nom_hotel;
    public $ville;
    public $adresse;
    public $telephone;
    public $email;
    public $etoiles;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY nom_hotel ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nom_hotel=:nom_hotel, ville=:ville, adresse=:adresse,
                      telephone=:telephone, email=:email, etoiles=:etoiles";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->nom_hotel = htmlspecialchars(strip_tags($this->nom_hotel));
        $this->ville = htmlspecialchars(strip_tags($this->ville));
        $this->adresse = htmlspecialchars(strip_tags($this->adresse));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Liaison des paramètres
        $stmt->bindParam(":nom_hotel", $this->nom_hotel);
        $stmt->bindParam(":ville", $this->ville);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":etoiles", $this->etoiles);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nom_hotel=:nom_hotel, ville=:ville, adresse=:adresse,
                      telephone=:telephone, email=:email, etoiles=:etoiles
                  WHERE id_hotel = :id_hotel";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->nom_hotel = htmlspecialchars(strip_tags($this->nom_hotel));
        $this->ville = htmlspecialchars(strip_tags($this->ville));
        $this->adresse = htmlspecialchars(strip_tags($this->adresse));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id_hotel = htmlspecialchars(strip_tags($this->id_hotel));
        
        // Liaison des paramètres
        $stmt->bindParam(":nom_hotel", $this->nom_hotel);
        $stmt->bindParam(":ville", $this->ville);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":etoiles", $this->etoiles);
        $stmt->bindParam(":id_hotel", $this->id_hotel);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_hotel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_hotel);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>