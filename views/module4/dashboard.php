<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h2 class="eco-heading mb-1">Dashboard Overview</h2>
        <p class="eco-subheading mb-0">Interactive analytics &amp; system performance monitoring.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn-eco-outline" href="index.php?page=module4-report&period=monthly"><i class="bi bi-file-earmark-bar-graph"></i> Monthly</a>
        <a class="btn-eco-outline" href="index.php?page=module4-report&period=annual"><i class="bi bi-file-earmark-bar-graph"></i> Annual</a>
        <div class="btn-group">
            <button type="button" class="btn-eco-outline dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
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
        <div class="eco-card h-100">
            <div class="fw-bold mb-3">6-Month Activity Trends</div>
            <canvas id="trendChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="eco-card h-100 d-flex flex-column justify-content-center align-items-center text-center">
            <div class="eco-stat-icon" style="margin-bottom: 14px;"><i class="bi bi-speedometer2"></i></div>
            <h3 class="mb-0" style="font-size: 34px; font-weight: 800;" id="avgDays">-</h3>
            <p class="eco-subheading" style="margin-bottom: 4px;">Average Days to Complete Collection</p>
            <small class="eco-subheading" style="margin-bottom: 0; font-size: 12px;">Analyzed automatically from system records</small>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="eco-card">
            <div class="fw-bold mb-3">Collection Status Distribution</div>
            <canvas id="collectionChart" style="max-height: 250px;"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="eco-card">
            <div class="fw-bold mb-3">Recycling Status Distribution</div>
            <canvas id="recyclingChart" style="max-height: 250px;"></canvas>
        </div>
    </div>
</div>

<script>
    (async () => {
        try {
            function generateRequestId() {
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                    const r = Math.random() * 16 | 0;
                    const v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            }

            // Pull the brand color from the real CSS variable instead of
            // re-typing the hex value here — this used to be a third
            // independent copy of #198754 in the codebase.
            const rootStyles = getComputedStyle(document.documentElement);
            const ecoPrimary = rootStyles.getPropertyValue('--eco-primary').trim() || '#168a5b';

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

            // 1. Top Stat Cards — real .eco-stat-card markup, matching
            // what View::statCard() renders server-side elsewhere.
            document.getElementById('stats').innerHTML = [
                ['Total Requests', d.collection_requests, 'bi-inbox'],
                ['Collections Completed', d.collection_completed, 'bi-check2-circle'],
                ['Recycling Submissions', d.recycling_submissions, 'bi-recycle'],
                ['Recycling Approved', d.recycling_approved, 'bi-patch-check']
            ].map(([label, value, icon]) => `
            <div class="col-md-3 col-sm-6">
                <div class="eco-stat-card">
                    <div class="eco-stat-icon"><i class="bi ${icon}"></i></div>
                    <div class="eco-stat-label">${label}</div>
                    <div class="eco-stat-number">${value ?? 0}</div>
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
                            { label: 'Collections', data: colData, borderColor: ecoPrimary, backgroundColor: 'rgba(22, 138, 91, 0.1)', fill: true, tension: 0.3 },
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
                            backgroundColor: ['#ffc107', '#17a2b8', '#0d6efd', ecoPrimary, '#dc3545']
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
                            backgroundColor: ['#ffc107', ecoPrimary, '#dc3545']
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