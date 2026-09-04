<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card { border-left: 4px solid #198754; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-val { font-size: 2rem; font-weight: bold; color: #333; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Dashboard Overview</h2>
        <p class="text-muted">Interactive Analytics & System Performance Monitoring</p>
    </div>
    <div>
        <a class="btn btn-outline-success" href="index.php?page=module4-report&period=monthly"><i class="bi bi-file-earmark-bar-graph"></i> Monthly</a>
        <a class="btn btn-outline-success" href="index.php?page=module4-report&period=annual"><i class="bi bi-file-earmark-bar-graph"></i> Annual</a>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="index.php?page=module4-csv&type=collections">Collection Data (CSV)</a></li>
                <li><a class="dropdown-item" href="index.php?page=module4-csv&type=recycling">Recycling Data (CSV)</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="index.php?page=module4-pdf&period=monthly">Monthly Report (PDF)</a></li>
                <li><a class="dropdown-item" href="index.php?page=module4-pdf&period=annual">Annual Report (PDF)</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row g-3 mb-4" id="stats">
    <div class="col-12"><div class="alert alert-info">Loading statistics from EcoBin JSON web service...</div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white fw-bold">6-Month Activity Trends</div>
            <div class="card-body">
                <canvas id="trendChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white fw-bold">System Performance</div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <i class="bi bi-speedometer2 text-success mb-3" style="font-size: 3rem;"></i>
                <h3 class="display-5" id="avgDays">-</h3>
                <p class="text-muted">Average Days to Complete Collection</p>
                <small class="text-secondary mt-2">Analyzed automatically from system records</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Collection Status Distribution</div>
            <div class="card-body">
                <canvas id="collectionChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Recycling Status Distribution</div>
            <div class="card-body">
                <canvas id="recyclingChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
(async()=>{
    try {
        function generateRequestId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
        const req = {
            requestID: generateRequestId(),
            timestamp: new Date().toISOString(),
            service: 'dashboard.stats',
            payload: {}
        };
        const res = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Service-Token': '<?= \EcoBin\Services\Security::e((require __DIR__ . '/../../config/app.php')['service_token']) ?>'
            },
            body: JSON.stringify(req)
        });
        const json = await res.json(); 
        const d = json.data || {};
        
        // 1. Top Stat Cards
        document.getElementById('stats').innerHTML = [
            ['Total Requests', d.collection_requests],
            ['Collections Completed', d.collection_completed],
            ['Recycling Submissions', d.recycling_submissions],
            ['Recycling Approved', d.recycling_approved]
        ].map(x => `
            <div class="col-md-3 col-sm-6">
                <div class="card p-3 stat-card">
                    <div class="text-muted small">${x[0]}</div>
                    <div class="stat-val">${x[1] ?? 0}</div>
                </div>
            </div>
        `).join('');

        // 2. System Performance
        document.getElementById('avgDays').innerText = d.avg_completion_days + ' Days';

        // 3. Trend Chart
        if (d.trends) {
            const labels = Object.keys(d.trends).sort();
            const colData = labels.map(l => d.trends[l].collections);
            const recData = labels.map(l => d.trends[l].recycling);
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Collections', data: colData, borderColor: '#198754', backgroundColor: 'rgba(25, 135, 84, 0.1)', fill: true, tension: 0.3 },
                        { label: 'Recycling', data: recData, borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', fill: true, tension: 0.3 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // 4. Collection Status Doughnut
        if (d.collection_status) {
            new Chart(document.getElementById('collectionChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(d.collection_status),
                    datasets: [{
                        data: Object.values(d.collection_status),
                        backgroundColor: ['#ffc107', '#17a2b8', '#0d6efd', '#198754', '#dc3545']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // 5. Recycling Status Doughnut
        if (d.recycling_status) {
            new Chart(document.getElementById('recyclingChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(d.recycling_status),
                    datasets: [{
                        data: Object.values(d.recycling_status),
                        backgroundColor: ['#ffc107', '#198754', '#dc3545']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    } catch (e) {
        document.getElementById('stats').innerHTML = `<div class="alert alert-danger">Error loading dashboard data.</div>`;
    }
})();
</script>