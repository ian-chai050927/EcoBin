<style>
    @media print {
        body * { visibility: hidden; }
        #printableReport, #printableReport * { visibility: visible; }
        #printableReport { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
    .report-header {
        border-bottom: 3px solid var(--eco-primary);
        padding-bottom: 14px;
        margin-bottom: 24px;
    }
</style>

<?php if (!($skipToolbar ?? false)): ?>
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="index.php?page=module4" class="btn-eco-outline"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <button class="btn-eco" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
    </div>
<?php endif; ?>

<div id="printableReport" class="eco-card" style="padding: 48px;">
    <div class="report-header d-flex justify-content-between align-items-end">
        <div>
            <h1 class="eco-heading mb-0" style="color: var(--eco-primary);">EcoBin</h1>
            <h4 class="eco-subheading mb-0"><?= ucfirst(\EcoBin\Services\Security::e($period)) ?> Operations Report</h4>
        </div>
        <div class="text-end eco-subheading" style="margin-bottom: 0;">
            <small>Generated on: <?= date('d M Y, H:i') ?></small><br>
            <small>Period: Current <?= $period === 'annual' ? 'Year' : 'Month' ?></small>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <?= \EcoBin\Services\View::statCard('Total Collections', (string) count($collections)) ?>
        </div>
        <div class="col-md-3">
            <?= \EcoBin\Services\View::statCard('Completion Rate', $completionRate . '%') ?>
        </div>
        <div class="col-md-3">
            <?= \EcoBin\Services\View::statCard('Total Recycled', number_format($totalWeight, 1) . ' kg') ?>
        </div>
        <div class="col-md-3">
            <?= \EcoBin\Services\View::statCard('Avg Turnaround', $avgProcessingDays . ' Days') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5 class="border-bottom pb-2 mb-3" style="color: var(--eco-primary);">Waste Collection Summary</h5>
            <?php if (count($collections) > 0): ?>
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Resident</th>
                        <th>Created At</th>
                        <th>Scheduled Date</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($collections as $c): ?>
                        <tr>
                            <td>#<?= $c->id ?></td>
                            <td><?= \EcoBin\Services\Security::e($c->resident->name) ?> (#<?= $c->resident->id ?>)</td>
                            <td><?= $c->createdAt->format('Y-m-d H:i') ?></td>
                            <td><?= $c->scheduledDate ? $c->scheduledDate->format('Y-m-d') : '-' ?></td>
                            <td><?= \EcoBin\Services\View::statusBadge($c->status) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="eco-subheading" style="margin-bottom: 0;">No collection records found for this period.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h5 class="border-bottom pb-2 mb-3" style="color: var(--eco-primary);">Recycling Submissions Summary</h5>
            <?php if (count($recycling) > 0): ?>
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Center</th>
                        <th>Material</th>
                        <th>Weight (kg)</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recycling as $r): ?>
                        <tr>
                            <td>#<?= $r->id ?></td>
                            <td><?= \EcoBin\Services\Security::e($r->center->name) ?> (#<?= $r->center->id ?>)</td>
                            <td><?= \EcoBin\Services\Security::e($r->material) ?></td>
                            <td><?= $r->weightKg ?></td>
                            <td><?= \EcoBin\Services\View::statusBadge($r->status) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="eco-subheading" style="margin-bottom: 0;">No recycling records found for this period.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-5 pt-3 border-top eco-subheading small" style="margin-bottom: 0;">
        <p class="mb-0">EcoBin Smart Waste Management System — Generated Report</p>
    </div>
</div>