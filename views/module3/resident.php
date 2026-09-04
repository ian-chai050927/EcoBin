<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h2 class="eco-heading mb-1">Recycling &amp; Rewards</h2>
        <p class="eco-subheading mb-0">Resident recycling submissions, appointments, reward points and history.</p>
    </div>
    <div style="max-width: 220px;">
        <?= \EcoBin\Services\View::statCard('Reward Balance', (int) $balance . ' pts', 'bi-coin') ?>
    </div>
</div>

<h4 class="mt-4">Environmental Achievement Badges</h4>
<div class="d-flex flex-wrap gap-2 mb-4">
    <?php if (empty($badges)): ?>
        <p class="eco-subheading mb-0">No badges earned yet. Keep recycling!</p>
    <?php else: ?>
        <?php foreach ($badges as $badge): ?>
            <span class="badge p-2 fs-6" style="background: var(--eco-primary-soft); color: var(--eco-primary-dark);">
                <i class="bi bi-award"></i> <?= \EcoBin\Services\Security::e($badge) ?>
            </span>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="eco-card h-100">
            <h4>Recycling Submission</h4>
            <form method="post" action="index.php?page=module3-submit">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <select class="form-select mb-2" name="center_id" required>
                    <option value="">Choose Centre</option>
                    <?php foreach ($centers as $c): ?>
                        <option value="<?= $c->id ?>"><?= \EcoBin\Services\Security::e($c->name) ?> (<?= \EcoBin\Services\Security::e($c->availability) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control mb-2" name="material" placeholder="Material e.g. Plastic, Metal, Paper" required>
                <input class="form-control mb-2" type="number" step="0.01" min="0.01" name="weight_kg" placeholder="Weight (kg)" required>
                <button class="btn-eco">Submit for Review</button>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="eco-card h-100">
            <h4>Recycling Appointment</h4>
            <form method="post" action="index.php?page=module3-appointment">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <select class="form-select mb-2" name="center_id" required>
                    <option value="">Choose Centre</option>
                    <?php foreach ($centers as $c): ?>
                        <option value="<?= $c->id ?>"><?= \EcoBin\Services\Security::e($c->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control mb-2" type="datetime-local" name="appointment_at" required>
                <button class="btn-eco">Book Appointment</button>
            </form>
        </div>
    </div>
</div>

<h4 class="mt-4">Redeem Reward Points</h4>
<div class="row g-4">
    <div class="col-md-4">
        <div class="eco-card text-center">
            <h5>$5 Voucher</h5>
            <p class="eco-subheading fw-bold" style="margin-bottom: 12px;">500 pts</p>
            <form method="post" action="index.php?page=module3-redeem">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <input type="hidden" name="points" value="500">
                <input type="hidden" name="reward_name" value="$5 Voucher">
                <button class="btn-eco w-100" <?= $balance < 500 ? 'disabled' : '' ?>>Redeem</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="eco-card text-center">
            <h5>$10 Voucher</h5>
            <p class="eco-subheading fw-bold" style="margin-bottom: 12px;">900 pts</p>
            <form method="post" action="index.php?page=module3-redeem">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <input type="hidden" name="points" value="900">
                <input type="hidden" name="reward_name" value="$10 Voucher">
                <button class="btn-eco w-100" <?= $balance < 900 ? 'disabled' : '' ?>>Redeem</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="eco-card text-center">
            <h5>Exclusive Eco-Bag</h5>
            <p class="eco-subheading fw-bold" style="margin-bottom: 12px;">1200 pts</p>
            <form method="post" action="index.php?page=module3-redeem">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <input type="hidden" name="points" value="1200">
                <input type="hidden" name="reward_name" value="Exclusive Eco-Bag">
                <button class="btn-eco w-100" <?= $balance < 1200 ? 'disabled' : '' ?>>Redeem</button>
            </form>
        </div>
    </div>
</div>

<div class="row mt-4 g-4">
    <div class="col-lg-8">
        <h4>Recycling Center Directory</h4>
        <div class="row g-3">
            <?php foreach ($centers as $c): ?>
                <div class="col-md-6">
                    <div class="eco-card h-100">
                        <h5 class="mb-2">
                            <?= \EcoBin\Services\Security::e($c->name) ?>
                            <span class="ms-2"><?= \EcoBin\Services\View::statusBadge($c->availability) ?></span>
                        </h5>
                        <p class="mb-1 small eco-subheading" style="margin-bottom: 4px;">
                            <i class="bi bi-geo-alt"></i> <?= \EcoBin\Services\Security::e($c->address) ?>
                        </p>
                        <p class="mb-0 small">
                            <i class="bi bi-recycle"></i> <?= \EcoBin\Services\Security::e($c->acceptedMaterials) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <h4>Leaderboard (Top 10)</h4>
        <div class="eco-card">
            <table class="table table-sm table-borderless mb-0">
                <thead><tr class="border-bottom"><th>Rank</th><th>Resident</th><th class="text-end">Points</th></tr></thead>
                <tbody>
                <?php $rank = 1; foreach ($leaderboard as $l): ?>
                    <tr>
                        <td><strong>#<?= $rank++ ?></strong></td>
                        <td><?= \EcoBin\Services\Security::e($l['name']) ?></td>
                        <td class="text-end fw-bold" style="color: var(--eco-primary-dark);"><?= $l['total_earned'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($leaderboard)): ?>
                    <tr><td colspan="3" class="text-center eco-subheading" style="margin-bottom:0;">No points earned yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<h4 class="mt-4">Recycling History</h4>
<div class="eco-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Material</th><th>Weight</th><th>Points</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($subs as $s): ?>
                <tr>
                    <td><?= $s->id ?></td>
                    <td><?= \EcoBin\Services\Security::e($s->material) ?></td>
                    <td><?= $s->weightKg ?> kg</td>
                    <td class="<?= $s->points > 0 ? 'fw-bold' : '' ?>" style="<?= $s->points > 0 ? 'color: var(--eco-primary-dark);' : '' ?>"><?= $s->points ?></td>
                    <td><?= \EcoBin\Services\View::statusBadge($s->status) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($subs)): ?>
                <tr><td colspan="5" class="text-center eco-subheading" style="margin-bottom:0;">No submissions found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>