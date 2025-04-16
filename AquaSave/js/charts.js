// Dashboard Charts using Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Load Chart.js from CDN
    const chartScript = document.createElement('script');
    chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js';
    document.head.appendChild(chartScript);

    chartScript.onload = function() {
        initializeCharts();
    };

    function initializeCharts() {
        // Only initialize charts if we're on the dashboard page
        if (document.getElementById('water-usage-chart')) {
            createWaterUsageChart();
        }

        if (document.getElementById('water-sources-chart')) {
            createWaterSourcesChart();
        }

        if (document.getElementById('conservation-progress-chart')) {
            createConservationProgressChart();
        }

        if (document.getElementById('daily-consumption-chart')) {
            createDailyConsumptionChart();
        }
    }

    function createWaterUsageChart() {
        const ctx = document.getElementById('water-usage-chart').getContext('2d');
        
        // Sample data for demonstration
        const data = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'This Year',
                    data: [120, 115, 110, 105, 100, 95, 90, 85, 80, 75, 70, 65],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Last Year',
                    data: [150, 145, 140, 135, 130, 125, 120, 115, 110, 105, 100, 95],
                    borderColor: '#60a5fa',
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    borderDash: [5, 5]
                }
            ]
        };

        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} gallons`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Gallons'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        };

        new Chart(ctx, {
            type: 'line',
            data: data,
            options: options
        });
    }

    function createWaterSourcesChart() {
        const ctx = document.getElementById('water-sources-chart').getContext('2d');
        
        const data = {
            labels: [
                'Kitchen',
                'Bathroom',
                'Laundry',
                'Garden',
                'Other'
            ],
            datasets: [{
                data: [30, 40, 15, 10, 5],
                backgroundColor: [
                    '#3b82f6',
                    '#60a5fa',
                    '#93c5fd',
                    '#bfdbfe',
                    '#dbeafe'
                ],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        };

        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                }
            },
            cutout: '65%'
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: options
        });
    }

    function createConservationProgressChart() {
        const ctx = document.getElementById('conservation-progress-chart').getContext('2d');
        
        const data = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [
                {
                    label: 'Target',
                    data: [100, 95, 90, 85],
                    borderColor: 'rgba(96, 165, 250, 0.5)',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.1
                },
                {
                    label: 'Actual',
                    data: [110, 100, 85, 80],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true
                }
            ]
        };

        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} gallons`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Gallons'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        };

        new Chart(ctx, {
            type: 'line',
            data: data,
            options: options
        });
    }

    function createDailyConsumptionChart() {
        const ctx = document.getElementById('daily-consumption-chart').getContext('2d');
        
        const data = {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Daily Water Usage',
                data: [35, 40, 30, 25, 45, 55, 25],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(220, 38, 38, 0.7)', // Saturday highlighted in red as highest usage
                    'rgba(59, 130, 246, 0.7)'
                ],
                borderColor: [
                    '#3b82f6',
                    '#3b82f6',
                    '#3b82f6',
                    '#3b82f6',
                    '#3b82f6',
                    '#dc2626',
                    '#3b82f6'
                ],
                borderWidth: 1,
                barThickness: 18
            }]
        };

        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.raw} gallons`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Gallons'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        };

        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
    }
});
