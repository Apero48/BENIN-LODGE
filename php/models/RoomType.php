<?php
// php/models/RoomType.php
require_once '../config/database.php';

class RoomType {
    private $conn;
    private $table = 'types_chambres';

    public $id_type;
    public $nom_type;
    public $description;
    public $tarif_nuit;
    public $capacite_personnes;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY nom_type ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nom_type=:nom_type, description=:description,
                      tarif_nuit=:tarif_nuit, capacite_personnes=:capacite_personnes";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->nom_type = htmlspecialchars(strip_tags($this->nom_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Liaison des paramètres
        $stmt->bindParam(":nom_type", $this->nom_type);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":tarif_nuit", $this->tarif_nuit);
        $stmt->bindParam(":capacite_personnes", $this->capacite_personnes);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nom_type=:nom_type, description=:description,
                      tarif_nuit=:tarif_nuit, capacite_personnes=:capacite_personnes
                  WHERE id_type = :id_type";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->nom_type = htmlspecialchars(strip_tags($this->nom_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id_type = htmlspecialchars(strip_tags($this->id_type));
        
        // Liaison des paramètres
        $stmt->bindParam(":nom_type", $this->nom_type);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":tarif_nuit", $this->tarif_nuit);
        $stmt->bindParam(":capacite_personnes", $this->capacite_personnes);
        $stmt->bindParam(":id_type", $this->id_type);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_type = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_type);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>