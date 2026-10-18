document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || !window.dashboardData) return;

    const loan = window.dashboardData.loan;
    const cats = window.dashboardData.cats;
    const colors = ['#2563eb', '#1e3a8a', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];
    const money = v => '$' + Number(v).toLocaleString('en-US');

    const loanCanvas = document.getElementById('loanChart');
    if (loanCanvas) {
        new Chart(loanCanvas, {
            type: 'bar',
            data: {
                labels: loan.labels,
                datasets: [
                    { label: 'Loans issued', data: loan.issued, backgroundColor: '#2563eb' },
                    { label: 'Interest collected', data: loan.collected, backgroundColor: '#16a34a' },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + money(c.parsed.y) } } },
                scales: { y: { beginAtZero: true, ticks: { callback: money } } },
            },
        });
    }

    const catCanvas = document.getElementById('categoryChart');
    if (catCanvas) {
        new Chart(catCanvas, {
            type: 'doughnut',
            data: {
                labels: cats.map(c => c.name),
                datasets: [{
                    data: cats.map(c => c.count),
                    backgroundColor: cats.map((_, i) => colors[i % colors.length]),
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false } },
            },
        });
    }
});