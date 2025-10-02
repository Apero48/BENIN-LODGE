// js/charts.js
class ChartsManager {
    constructor() {
        this.charts = {};
        this.init();
    }

    init() {
        this.initializeCharts();
        this.setupEventListeners();
    }

    initializeCharts() {
        this.createRevenueChart();
        this.createOccupancyChart();
        this.createPerformanceChart();
        this.createDistributionChart();
    }

    createRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;

        this.charts.revenue = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Revenus (milliers FCFA)',
                    data: [1200, 1900, 1500, 2450, 2000, 2300, 2800, 2600, 2200, 2500, 2700, 3000],
                    backgroundColor: '#FF7F00',
                    borderColor: '#FF7F00',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Revenus mensuels 2025'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenus (milliers FCFA)'
                        }
                    }
                }
            }
        });
    }

    createOccupancyChart() {
        const ctx = document.getElementById('occupancyChart');
        if (!ctx) return;

        this.charts.occupancy = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Occupées', 'Disponibles'],
                datasets: [{
                    data: [72, 28],
                    backgroundColor: ['#1E3A8A', '#E5E7EB'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Taux d\'occupation actuel'
                    }
                }
            }
        });
    }

    createPerformanceChart() {
        const ctx = document.getElementById('hotelsPerformanceChart');
        if (!ctx) return;

        this.charts.performance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Golden Tulip', 'Azalai Hôtel', 'Auberge Grand-Popo'],
                datasets: [{
                    label: 'Revenus (milliers FCFA)',
                    data: [3200, 1800, 950],
                    backgroundColor: '#FF7F00'
                }, {
                    label: 'Taux d\'occupation (%)',
                    data: [85, 70, 60],
                    backgroundColor: '#1E3A8A',
                    type: 'line',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenus (milliers FCFA)'
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Taux d\'occupation (%)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }

    createDistributionChart() {
        const ctx = document.getElementById('reservationsDistributionChart');
        if (!ctx) return;

        this.charts.distribution = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Confirmées', 'En attente', 'Annulées'],
                datasets: [{
                    data: [70, 18, 12],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    setupEventListeners() {
        // Changement d'année pour le graphique des revenus
        document.getElementById('year-select')?.addEventListener('change', (e) => {
            this.updateRevenueChart(e.target.value);
        });
    }

    updateRevenueChart(year) {
        // Simuler des données différentes selon l'année
        const dataByYear = {
            '2023': [800, 1200, 900, 1500, 1300, 1400, 1600, 1500, 1300, 1400, 1500, 1700],
            '2024': [1000, 1500, 1200, 1800, 1600, 1700, 2000, 1900, 1700, 1800, 1900, 2100],
            '2025': [1200, 1900, 1500, 2450, 2000, 2300, 2800, 2600, 2200, 2500, 2700, 3000],
            '2026': [1400, 2100, 1800, 2600, 2200, 2500, 3000, 2800, 2400, 2700, 2900, 3200],
            '2027': [1600, 2300, 2000, 2800, 2400, 2700, 3200, 3000, 2600, 2900, 3100, 3500],
            '2028': [1800, 2500, 2200, 3000, 2600, 2900, 3400, 3200, 2800, 3100, 3300, 3800]
        };

        if (this.charts.revenue) {
            this.charts.revenue.data.datasets[0].data = dataByYear[year] || dataByYear['2025'];
            this.charts.revenue.options.plugins.title.text = `Revenus mensuels ${year}`;
            this.charts.revenue.update();
        }
    }

    // Méthode pour mettre à jour les graphiques avec des données réelles
    async updateChartsWithRealData() {
        try {
            const response = await fetch('php/api/dashboard.php');
            const data = await response.json();
            
            if (data.success && this.charts.revenue) {
                // Mettre à jour le graphique des revenus avec les données réelles
                this.charts.revenue.data.datasets[0].data = data.charts.revenueData.data;
                this.charts.revenue.update();
                
                // Mettre à jour le graphique d'occupation
                if (this.charts.occupancy) {
                    this.charts.occupancy.data.datasets[0].data = [
                        data.charts.occupancyData.occupied,
                        data.charts.occupancyData.available
                    ];
                    this.charts.occupancy.update();
                }
            }
        } catch (error) {
            console.error('Erreur mise à jour graphiques:', error);
        }
    }
}

// Initialisation
const chartsManager = new ChartsManager();