// js/app.js
class BeninLodgeApp {
    constructor() {
        this.currentPage = 'dashboard';
        this.init();
    }

    init() {
        this.setupNavigation();
        this.setupEventListeners();
        this.loadInitialData();
    }

    setupNavigation() {
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        const pageContents = document.querySelectorAll('.page-content');
        
        // Afficher la page tableau de bord par défaut
        document.getElementById('dashboard-page').style.display = 'block';
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Retirer la classe active de tous les liens
                navLinks.forEach(navLink => {
                    navLink.classList.remove('active');
                });
                
                // Ajouter la classe active au lien cliqué
                link.classList.add('active');
                
                // Masquer tous les contenus de page
                pageContents.forEach(page => {
                    page.style.display = 'none';
                });
                
                // Afficher la page correspondante
                const pageId = link.getAttribute('data-page') + '-page';
                document.getElementById(pageId).style.display = 'block';
                this.currentPage = link.getAttribute('data-page');
                
                // Charger les données spécifiques à la page
                this.loadPageData(this.currentPage);
            });
        });
    }

    async loadPageData(page) {
        switch(page) {
            case 'dashboard':
                await this.loadDashboardData();
                break;
            case 'reservations':
                await this.loadReservations();
                break;
            case 'hotels':
                await this.loadHotels();
                break;
            case 'rooms':
                await this.loadRoomTypes();
                break;
        }
    }

    async loadInitialData() {
        // Charger les données initiales pour le dashboard
        await this.loadDashboardData();
    }

    async loadDashboardData() {
        try {
            console.log('Chargement des données du dashboard...');
            // Pour l'instant, on utilise des données statiques
            // Plus tard, vous pourrez décommenter cette partie :
            /*
            const response = await fetch('php/api/dashboard.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateDashboardStats(data.stats);
            }
            */
        } catch (error) {
            console.error('Erreur chargement dashboard:', error);
        }
    }

    async loadReservations() {
        try {
            console.log('Chargement des réservations...');
            // Cette fonction sera appelée par reservations.js
        } catch (error) {
            console.error('Erreur chargement réservations:', error);
        }
    }

    async loadHotels() {
        try {
            console.log('Chargement des hôtels...');
            // Implémentez le chargement des hôtels ici
        } catch (error) {
            console.error('Erreur chargement hôtels:', error);
        }
    }

    async loadRoomTypes() {
        try {
            console.log('Chargement des types de chambres...');
            // Implémentez le chargement des types de chambres ici
        } catch (error) {
            console.error('Erreur chargement types de chambres:', error);
        }
    }

    updateDashboardStats(stats) {
        if (stats) {
            document.querySelector('.stat-card .number').textContent = stats.totalReservations || '127';
            document.querySelector('.stat-card.confirmed .number').textContent = stats.confirmed || '89';
            document.querySelector('.stat-card.pending .number').textContent = stats.pending || '23';
            document.querySelector('.stat-card.revenue .number').textContent = stats.revenue ? stats.revenue.toLocaleString() : '2,450,000';
        }
    }

    setupEventListeners() {
        // Calcul automatique réservation
        const arrivalDateInput = document.getElementById('arrival-date');
        const departureDateInput = document.getElementById('departure-date');
        const roomTypeSelect = document.getElementById('room-type');
        
        if (arrivalDateInput && departureDateInput && roomTypeSelect) {
            [arrivalDateInput, departureDateInput, roomTypeSelect].forEach(element => {
                element.addEventListener('change', () => this.calculateReservation());
            });
        }

        // Filtres réservations
        document.getElementById('apply-filters')?.addEventListener('click', () => this.applyFilters());
        document.getElementById('reset-filters')?.addEventListener('click', () => this.resetFilters());

        // Exports
        document.getElementById('export-csv')?.addEventListener('click', () => this.exportCSV());
        document.getElementById('export-pdf')?.addEventListener('click', () => this.exportPDF());

        // Navigation depuis le bouton "Nouvelle réservation"
        document.getElementById('new-reservation-btn')?.addEventListener('click', () => {
            this.navigateToPage('new-reservation');
        });

        // Annulation réservation
        document.getElementById('cancel-reservation')?.addEventListener('click', () => {
            if (confirm('Annuler la réservation en cours ?')) {
                this.resetReservationForm();
            }
        });
    }

    calculateReservation() {
        const arrivalDateInput = document.getElementById('arrival-date');
        const departureDateInput = document.getElementById('departure-date');
        const roomTypeSelect = document.getElementById('room-type');
        const nightsCountSpan = document.getElementById('nights-count');
        const pricePerNightSpan = document.getElementById('price-per-night');
        const totalAmountSpan = document.getElementById('total-amount');
        
        const arrivalDate = new Date(arrivalDateInput.value);
        const departureDate = new Date(departureDateInput.value);
        const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
        const roomPrice = selectedOption ? selectedOption.getAttribute('data-price') || 0 : 0;
        
        if (arrivalDate && departureDate && departureDate > arrivalDate) {
            const timeDiff = departureDate.getTime() - arrivalDate.getTime();
            const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            nightsCountSpan.textContent = nights;
            pricePerNightSpan.textContent = parseInt(roomPrice).toLocaleString();
            totalAmountSpan.textContent = (nights * parseInt(roomPrice)).toLocaleString();
        } else {
            nightsCountSpan.textContent = '0';
            pricePerNightSpan.textContent = '0';
            totalAmountSpan.textContent = '0';
        }
    }

    navigateToPage(page) {
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        const pageContents = document.querySelectorAll('.page-content');
        
        // Retirer la classe active de tous les liens
        navLinks.forEach(navLink => {
            navLink.classList.remove('active');
        });
        
        // Ajouter la classe active au lien cible
        document.querySelector(`[data-page="${page}"]`).classList.add('active');
        
        // Masquer tous les contenus de page
        pageContents.forEach(pageContent => {
            pageContent.style.display = 'none';
        });
        
        // Afficher la page correspondante
        document.getElementById(`${page}-page`).style.display = 'block';
        this.currentPage = page;
        
        // Charger les données spécifiques à la page
        this.loadPageData(page);
    }

    resetReservationForm() {
        const form = document.getElementById('reservation-form');
        if (form) {
            form.reset();
            document.getElementById('nights-count').textContent = '0';
            document.getElementById('price-per-night').textContent = '0';
            document.getElementById('total-amount').textContent = '0';
        }
    }

    async applyFilters() {
        console.log('Application des filtres...');
        // Implémentation des filtres
    }

    resetFilters() {
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-hotel').value = '';
        document.getElementById('filter-date-from').value = '';
        document.getElementById('filter-date-to').value = '';
    }

    async exportCSV() {
        console.log('Export CSV...');
        // Implémentation export CSV
    }

    async exportPDF() {
        console.log('Export PDF...');
        // Implémentation export PDF
    }

    showNotification(message, type = 'info') {
        // Créer une notification temporaire
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 1050; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
}

// Initialisation de l'application
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initialisation de BENIN LODGE...');
    window.app = new BeninLodgeApp();
    console.log('Application initialisée avec succès!');
});