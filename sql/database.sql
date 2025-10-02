-- sql/database.sql
CREATE DATABASE IF NOT EXISTS benin_lodge;
USE benin_lodge;

-- Table des hôtels
CREATE TABLE hotels (
    id_hotel INT AUTO_INCREMENT PRIMARY KEY,
    nom_hotel VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(255),
    etoiles INT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des types de chambres
CREATE TABLE types_chambres (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(100) NOT NULL,
    description TEXT,
    tarif_nuit DECIMAL(10,2) NOT NULL,
    capacite_personnes INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des réservations
CREATE TABLE reservations (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    nom_client VARCHAR(255) NOT NULL,
    telephone_client VARCHAR(20) NOT NULL,
    email_client VARCHAR(255),
    id_hotel INT NOT NULL,
    id_type INT NOT NULL,
    date_arrivee DATE NOT NULL,
    date_depart DATE NOT NULL,
    nombre_nuits INT NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    statut ENUM('confirmée', 'en attente', 'annulée') DEFAULT 'en attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_hotel) REFERENCES hotels(id_hotel),
    FOREIGN KEY (id_type) REFERENCES types_chambres(id_type)
);

-- Données de test
INSERT INTO hotels (nom_hotel, ville, adresse, telephone, email, etoiles) VALUES
('Golden Tulip Le Diplomate', 'Cotonou', 'Rue des Ambassades', '+229 21 30 12 34', 'contact@goldentulip.bj', 4),
('Azalaï Hôtel de la Plage', 'Grand-Popo', 'Boulevard de la Plage', '+229 21 30 56 78', 'info@azalai.bj', 3),
('Auberge de Grand-Popo', 'Grand-Popo', 'Route Côtière', '+229 21 30 90 12', 'reservation@auberge.bj', 2);

INSERT INTO types_chambres (nom_type, description, tarif_nuit, capacite_personnes) VALUES
('Chambre Standard', 'Confort de base avec salle de bain privée', 25000.00, 2),
('Chambre Supérieure', 'Plus d''espace avec vue partielle', 35000.00, 2),
('Suite Junior', 'Chambre avec salon séparé', 55000.00, 3),
('Suite Présidentielle', 'Luxe maximum avec toutes commodités', 85000.00, 4);

INSERT INTO reservations (nom_client, telephone_client, email_client, id_hotel, id_type, date_arrivee, date_depart, nombre_nuits, montant_total, statut) VALUES
('AGBODIAN Koffi', '97123456', 'koffi@email.com', 1, 3, '2025-03-15', '2025-03-18', 3, 165000.00, 'confirmée'),
('TOGNON Marie', '97123457', 'marie@email.com', 2, 2, '2025-03-20', '2025-03-25', 5, 175000.00, 'en attente'),
('HOUETO Jean', '97123458', 'jean@email.com', 3, 1, '2025-03-22', '2025-03-24', 2, 50000.00, 'confirmée');