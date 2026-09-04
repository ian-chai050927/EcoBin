<style>
@media print {
    body * { visibility: hidden; }
    #printableReport, #printableReport * { visibility: visible; }
    #printableReport { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
}
.report-header { border-bottom: 3px solid #198754; padding-bottom: 10px; margin-bottom: 20px; }
.stat-box { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; text-align: center; }
.stat-box .title { font-size: 0.9rem; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; }
.stat-box .value { font-size: 1.8rem; font-weight: bold; color: #198754; margin-top: 5px; }
</style>

<?php if (!($skipToolbar ?? false)): ?>
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="index.php?page=module4" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <button class="btn btn-success" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
    </div>
<?php endif; ?>

<div id="printableReport" class="card shadow-sm p-5 bg-white">
    <div class="report-header d-flex justify-content-between align-items-end">
        <div>
            <h1 class="text-success mb-0">EcoBin</h1>
            <h4 class="text-secondary"><?= ucfirst(\EcoBin\Services\Security::e($period)) ?> Operations Report</h4>
        </div>
        <div class="text-end text-muted">
            <small>Generated on: <?= date('d M Y, H:i') ?></small><br>
            <small>Period: Current <?= $period==='annual'?'Year':'Month' ?></small>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="title">Total Collections</div>
                <div class="value"><?= count($collections) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="title">Completion Rate</div>
                <div class="value"><?= $completionRate ?>%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="title">Total Recycled</div>
                <div class="value"><?= number_format($totalWeight, 1) ?> kg</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="title">Avg Turnaround</div>
                <div class="value"><?= $avgProcessingDays ?> Days</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5 class="text-success border-bottom pb-2 mb-3">Waste Collection Summary</h5>
            <?php if (count($collections) > 0): ?>
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Resident ID</th>
                        <th>Created At</th>
                        <th>Scheduled Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($collections as $c): ?>
                    <tr>
                        <td>#<?= $c->id ?></td>
                        <td><?= $c->residentId ?></td>
                        <td><?= $c->createdAt->format('Y-m-d H:i') ?></td>
                        <td><?= $c->scheduledDate ? $c->scheduledDate->format('Y-m-d') : '-' ?></td>
                        <td>
                            <span class="badge bg-<?= $c->status === 'Completed' ? 'success' : ($c->status === 'Pending' ? 'warning text-dark' : 'primary') ?>">
                                <?= \EcoBin\Services\Security::e($c->status) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-muted">No collection records found for this period.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h5 class="text-success border-bottom pb-2 mb-3">Recycling Submissions Summary</h5>
            <?php if (count($recycling) > 0): ?>
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Center ID</th>
                        <th>Material</th>
                        <th>Weight (kg)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recycling as $r): ?>
                    <tr>
                        <td>#<?= $r->id ?></td>
                        <td><?= $r->centerId ?></td>
                        <td><?= \EcoBin\Services\Security::e($r->material) ?></td>
                        <td><?= $r->weightKg ?></td>
                        <td>
                            <span class="badge bg-<?= $r->status === 'Approved' ? 'success' : ($r->status === 'Rejected' ? 'danger' : 'warning text-dark') ?>">
                                <?= \EcoBin\Services\Security::e($r->status) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-muted">No recycling records found for this period.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-5 pt-3 border-top text-muted small">
        <p>EcoBin Smart Waste Management System - Generated Report</p>
    </div>
</div>