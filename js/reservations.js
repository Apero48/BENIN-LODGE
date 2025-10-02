// js/reservations.js
class ReservationsManager {
    constructor() {
        this.reservations = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadReservations();
    }

    setupEventListeners() {
        // Sauvegarde des réservations
        document.getElementById('save-pending')?.addEventListener('click', () => this.saveReservation('en attente'));
        document.getElementById('save-confirm')?.addEventListener('click', () => this.saveReservation('confirmée'));
        document.getElementById('cancel-reservation')?.addEventListener('click', () => this.cancelReservation());
        
        // Bouton nouvelle réservation depuis la page réservations
        document.getElementById('new-reservation-btn')?.addEventListener('click', () => this.goToNewReservation());
    }

    async loadReservations() {
        try {
            const response = await fetch('php/api/reservations.php');
            const data = await response.json();
            
            if (data.data) {
                this.reservations = data.data;
                this.displayReservations();
            }
        } catch (error) {
            console.error('Erreur chargement réservations:', error);
            this.showMessage('Erreur lors du chargement des réservations', 'error');
        }
    }

    displayReservations() {
        const tbody = document.getElementById('reservations-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.reservations.forEach(reservation => {
            const row = document.createElement('tr');
            
            // Formater les dates
            const dateArrivee = new Date(reservation.date_arrivee).toLocaleDateString('fr-FR');
            const dateDepart = new Date(reservation.date_depart).toLocaleDateString('fr-FR');
            
            row.innerHTML = `
                <td>RES${reservation.id_reservation.toString().padStart(3, '0')}</td>
                <td>${reservation.nom_client}</td>
                <td>${reservation.telephone_client}<br>${reservation.email_client || ''}</td>
                <td>${reservation.nom_hotel}</td>
                <td>${reservation.nom_type}</td>
                <td>${dateArrivee} - ${dateDepart}</td>
                <td>${reservation.nombre_nuits}</td>
                <td>${parseInt(reservation.montant_total).toLocaleString()} FCFA</td>
                <td><span class="badge badge-${reservation.statut === 'confirmée' ? 'confirmed' : reservation.statut === 'en attente' ? 'pending' : 'cancelled'}">${reservation.statut}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="reservationsManager.showReservationDetails(${reservation.id_reservation})">Détails</button>
                    ${reservation.statut === 'en attente' ? 
                        `<button class="btn btn-sm btn-outline-success me-1" onclick="reservationsManager.confirmReservation(${reservation.id_reservation})">Confirmer</button>` : 
                        ''
                    }
                    <button class="btn btn-sm btn-outline-danger" onclick="reservationsManager.cancelReservation(${reservation.id_reservation})">Annuler</button>
                </td>
            `;
            
            tbody.appendChild(row);
        });
    }

    async saveReservation(status) {
        const formData = this.getFormData();
        if (!formData) return;

        formData.statut = status;

        try {
            const response = await fetch('php/api/reservations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (response.ok) {
                this.showMessage('Réservation créée avec succès!', 'success');
                this.resetForm();
                
                // Recharger les réservations si on est sur la page réservations
                if (document.getElementById('reservations-page').style.display !== 'none') {
                    this.loadReservations();
                }
            } else {
                this.showMessage('Erreur: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Erreur sauvegarde réservation:', error);
            this.showMessage('Erreur lors de la sauvegarde', 'error');
        }
    }

    getFormData() {
        const clientName = document.getElementById('client-name').value;
        const clientPhone = document.getElementById('client-phone').value;
        const arrivalDate = document.getElementById('arrival-date').value;
        const departureDate = document.getElementById('departure-date').value;
        const hotelId = document.getElementById('hotel-select').value;
        const roomTypeId = document.getElementById('room-type').value;

        // Validation basique
        if (!clientName || !clientPhone || !arrivalDate || !departureDate || !hotelId || !roomTypeId) {
            this.showMessage('Veuillez remplir tous les champs obligatoires', 'error');
            return null;
        }

        const nights = parseInt(document.getElementById('nights-count').textContent) || 0;
        const totalAmount = parseInt(document.getElementById('total-amount').textContent.replace(/\s/g, '')) || 0;

        return {
            nom_client: clientName,
            telephone_client: clientPhone,
            email_client: document.getElementById('client-email').value,
            id_hotel: parseInt(hotelId),
            id_type: parseInt(roomTypeId),
            date_arrivee: arrivalDate,
            date_depart: departureDate,
            nombre_nuits: nights,
            montant_total: totalAmount
        };
    }

    resetForm() {
        document.getElementById('reservation-form').reset();
        document.getElementById('nights-count').textContent = '0';
        document.getElementById('price-per-night').textContent = '0';
        document.getElementById('total-amount').textContent = '0';
    }

    cancelReservation() {
        if (confirm('Annuler la réservation en cours ? Les données saisies seront perdues.')) {
            this.resetForm();
            this.showMessage('Réservation annulée', 'info');
        }
    }

    async confirmReservation(reservationId) {
        if (confirm('Confirmer cette réservation ?')) {
            try {
                const response = await fetch('php/api/reservations.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_reservation: reservationId,
                        statut: 'confirmée'
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    this.showMessage('Réservation confirmée avec succès!', 'success');
                    this.loadReservations();
                } else {
                    this.showMessage('Erreur: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur confirmation réservation:', error);
                this.showMessage('Erreur lors de la confirmation', 'error');
            }
        }
    }

    showReservationDetails(reservationId) {
        const reservation = this.reservations.find(r => r.id_reservation === reservationId);
        if (reservation) {
            alert(`Détails de la réservation:\n
Client: ${reservation.nom_client}
Téléphone: ${reservation.telephone_client}
Email: ${reservation.email_client || 'Non renseigné'}
Hôtel: ${reservation.nom_hotel}
Type de chambre: ${reservation.nom_type}
Dates: ${new Date(reservation.date_arrivee).toLocaleDateString('fr-FR')} - ${new Date(reservation.date_depart).toLocaleDateString('fr-FR')}
Nuits: ${reservation.nombre_nuits}
Montant: ${parseInt(reservation.montant_total).toLocaleString()} FCFA
Statut: ${reservation.statut}`);
        }
    }

    goToNewReservation() {
        // Navigation vers la page nouvelle réservation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => link.classList.remove('active'));
        document.querySelector('[data-page="new-reservation"]').classList.add('active');
        
        document.querySelectorAll('.page-content').forEach(page => page.style.display = 'none');
        document.getElementById('new-reservation-page').style.display = 'block';
    }

    showMessage(message, type) {
        // Créer un élément de message temporaire
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
        messageDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Ajouter au début du main-content
        const mainContent = document.querySelector('.main-content');
        mainContent.insertBefore(messageDiv, mainContent.firstChild);
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
}

// Initialisation
const reservationsManager = new ReservationsManager();