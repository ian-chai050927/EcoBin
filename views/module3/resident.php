<div class="mb-4">
    <h2 class="eco-heading mb-1">Recycling &amp; Rewards</h2>
    <p class="eco-subheading mb-0">Record recycling submissions, schedule drop-off appointments, redeem vouchers, and track your environmental impact.</p>
</div>

<!-- Environmental Impact & KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Reward Balance', number_format((int)$balance) . ' pts', 'bi-coin') ?>
    </div>
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Total Recycled', number_format((float)$totalWeight, 2) . ' kg', 'bi-recycle') ?>
    </div>
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Est. CO₂ Offset', number_format((float)$co2Offset, 2) . ' kg', 'bi-tree') ?>
    </div>
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Active Appointments', (int)$activeApptsCount, 'bi-calendar-check') ?>
    </div>
</div>

<!-- Environmental Badges -->
<h5 class="mb-2"><i class="bi bi-award me-1"></i> Environmental Achievements</h5>
<div class="row g-3 mb-4">
    <?php
    $allBadges = [
        ['name' => 'Eco Starter', 'desc' => 'Approved first recycling submission', 'icon' => '🌱', 'target' => 1, 'type' => 'sub'],
        ['name' => 'Green Warrior', 'desc' => 'Recycled at least 10 kg of materials', 'icon' => '🌿', 'target' => 10, 'type' => 'weight'],
        ['name' => 'Recycling Master', 'desc' => 'Recycled at least 50 kg of materials', 'icon' => '🏆', 'target' => 50, 'type' => 'weight'],
    ];
    foreach ($allBadges as $b):
        $earned = in_array($b['name'], $badges, true);
    ?>
        <div class="col-md-4">
            <div class="eco-card p-3 text-center <?= $earned ? 'border-success' : 'opacity-75' ?>" style="<?= $earned ? 'background: #f0fdf4; border-width: 2px;' : '' ?>">
                <div style="font-size: 2rem;"><?= $b['icon'] ?></div>
                <h6 class="mt-2 mb-1 fw-bold"><?= $b['name'] ?></h6>
                <p class="eco-subheading small mb-2"><?= $b['desc'] ?></p>
                <?php if ($earned): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Earned</span>
                <?php else: ?>
                    <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i> Locked</span>
                    <div class="small text-muted mt-1">
                        <?php if ($b['type'] === 'weight'): ?>
                            <?= max(0, $b['target'] - (int)$totalWeight) ?> kg remaining
                        <?php else: ?>
                            Submit recycling to unlock
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Submissions and Appointments Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="eco-card h-100">
            <h4 class="mb-2"><i class="bi bi-box-seam me-1 text-success"></i> Recycling Submission</h4>
            <p class="eco-subheading small mb-3">Submit recycling details after drop-off. Once reviewed by the operator, reward points are credited.</p>
            <form method="post" action="index.php?page=module3-submit">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <div class="mb-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Recycling Centre</label>
                    <select class="form-select" name="center_id" required>
                        <option value="">Choose Centre</option>
                        <?php foreach ($centers as $c): ?>
                            <option value="<?= $c->id ?>" <?= $c->availability !== 'Open' ? 'disabled' : '' ?>>
                                <?= \EcoBin\Services\Security::e($c->name) ?> (<?= \EcoBin\Services\Security::e($c->availability) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Material Type</label>
                    <select class="form-select" name="material" id="submit_material" required>
                        <option value="">-- Select Material Type --</option>
                        <?php foreach ($materialOptions as $matKey => $matLabel): ?>
                            <option value="<?= \EcoBin\Services\Security::e($matKey) ?>">
                                <?= \EcoBin\Services\Security::e($matLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text small text-muted">
                        Strategy Rates: <strong>Plastic 15 pts/kg</strong> | <strong>Metal 20 pts/kg</strong> | <strong>Paper 10 pts/kg</strong> | <strong>Others 5 pts/kg</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Weight (kg)</label>
                    <input class="form-control" type="number" step="0.01" min="0.01" max="500.00" name="weight_kg" placeholder="Weight in kilograms (e.g. 3.50)" required>
                </div>
                <button class="btn-eco w-100">Submit for Review</button>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="eco-card h-100">
            <h4 class="mb-2"><i class="bi bi-calendar-event me-1 text-success"></i> Recycling Appointment</h4>
            <p class="eco-subheading small mb-3">Book an appointment slot at your local centre for bulk drop-off and priority processing.</p>
            <form method="post" action="index.php?page=module3-appointment">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <div class="mb-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Recycling Centre</label>
                    <select class="form-select" name="center_id" required>
                        <option value="">Choose Centre</option>
                        <?php foreach ($centers as $c): ?>
                            <option value="<?= $c->id ?>" <?= $c->availability !== 'Open' ? 'disabled' : '' ?>>
                                <?= \EcoBin\Services\Security::e($c->name) ?> (<?= \EcoBin\Services\Security::e($c->availability) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Preferred Date &amp; Time</label>
                    <input class="form-control" type="datetime-local" name="appointment_at" min="<?= date('Y-m-d\TH:i') ?>" required>
                    <div class="form-text small text-muted">Appointments must be scheduled for a future time slot.</div>
                </div>
                <button class="btn-eco w-100">Book Appointment</button>
            </form>
        </div>
    </div>
</div>

<!-- Redeem Rewards Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-gift me-1 text-success"></i> Redeem Reward Points</h4>
    <span class="badge bg-light text-dark border px-3 py-2 fs-6">Available: <strong><?= number_format((int)$balance) ?> pts</strong></span>
</div>
<div class="row g-4 mb-4">
    <?php foreach ($catalog as $key => $item):
        $canRedeem = ($balance >= $item['points']);
    ?>
        <div class="col-md-6 col-xl-3">
            <div class="eco-card text-center h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="mb-2" style="font-size: 2rem; color: var(--eco-primary-dark);"><i class="bi <?= $item['icon'] ?>"></i></div>
                    <h5 class="mb-1"><?= \EcoBin\Services\Security::e($item['name']) ?></h5>
                    <p class="eco-subheading small mb-2"><?= \EcoBin\Services\Security::e($item['desc']) ?></p>
                    <p class="fs-5 fw-bold text-success mb-3"><?= number_format($item['points']) ?> pts</p>
                </div>
                <form method="post" action="index.php?page=module3-redeem">
                    <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                    <input type="hidden" name="reward_id" value="<?= $key ?>">
                    <button class="btn-eco w-100" <?= !$canRedeem ? 'disabled' : '' ?>>
                        <?= $canRedeem ? 'Redeem Voucher' : 'Need ' . number_format($item['points'] - $balance) . ' more' ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- My Appointments Table -->
<h4 class="mt-4"><i class="bi bi-calendar-check me-1 text-success"></i> My Recycling Appointments</h4>
<div class="eco-card mb-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Recycling Centre</th>
                    <th>Date &amp; Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appts as $a): ?>
                <tr>
                    <td>#<?= $a->id ?></td>
                    <td>
                        <strong><?= \EcoBin\Services\Security::e($a->center->name ?? 'Centre') ?></strong>
                        <?php if (!empty($a->center->address)): ?>
                            <div class="small text-muted"><?= \EcoBin\Services\Security::e($a->center->address) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= $a->appointmentAt ? $a->appointmentAt->format('d M Y, h:i A') : 'N/A' ?></td>
                    <td><?= \EcoBin\Services\View::statusBadge($a->status) ?></td>
                    <td>
                        <?php if (in_array($a->status, ['Pending', 'Confirmed'], true)): ?>
                            <form method="post" action="index.php?page=module3-cancel-appointment" onsubmit="return confirm('Are you sure you want to cancel this appointment?');" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                                <input type="hidden" name="appointment_id" value="<?= $a->id ?>">
                                <button class="btn btn-sm btn-outline-danger">Cancel</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($appts)): ?>
                <tr><td colspan="5" class="text-center eco-subheading py-3" style="margin-bottom:0;">No booked appointments yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recycling Submissions History -->
<h4 class="mt-4"><i class="bi bi-clock-history me-1 text-success"></i> Recycling Submissions History</h4>
<div class="eco-card mb-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Material</th>
                    <th>Weight</th>
                    <th>Points Awarded</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subs as $s): ?>
                <tr>
                    <td>#<?= $s->id ?></td>
                    <td><?= \EcoBin\Services\Security::e($s->material) ?></td>
                    <td><?= $s->weightKg ?> kg</td>
                    <td class="<?= $s->points > 0 ? 'fw-bold text-success' : '' ?>">
                        <?= $s->points > 0 ? '+' . $s->points . ' pts' : '—' ?>
                    </td>
                    <td><?= \EcoBin\Services\View::statusBadge($s->status) ?></td>
                    <td class="small text-muted"><?= $s->createdAt ? $s->createdAt->format('d M Y, h:i A') : 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($subs)): ?>
                <tr><td colspan="6" class="text-center eco-subheading py-3" style="margin-bottom:0;">No recycling submissions found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reward Points & Redemption History Table -->
<h4 class="mt-4"><i class="bi bi-wallet2 me-1 text-success"></i> Reward Points &amp; Redemption History</h4>
<div class="eco-card mb-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Activity / Description</th>
                    <th>Type</th>
                    <th>Points</th>
                    <th>Date &amp; Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rewards as $r): ?>
                <tr>
                    <td>#<?= $r->id ?></td>
                    <td><?= \EcoBin\Services\Security::e($r->description ?? $r->type) ?></td>
                    <td>
                        <?php if ($r->type === 'Earn'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-arrow-down-left me-1"></i> Earned</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-arrow-up-right me-1"></i> Redeemed</span>
                        <?php endif; ?>
                    </td>
                    <td class="<?= $r->type === 'Earn' ? 'text-success' : 'text-danger' ?> fw-bold">
                        <?= $r->type === 'Earn' ? '+' : '' ?><?= $r->points ?> pts
                    </td>
                    <td class="small text-muted"><?= $r->createdAt ? $r->createdAt->format('d M Y, h:i A') : 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rewards)): ?>
                <tr><td colspan="5" class="text-center eco-subheading py-3" style="margin-bottom:0;">No reward transactions yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Directory and Leaderboard Row -->
<div class="row g-4">
    <div class="col-lg-8">
        <h4><i class="bi bi-buildings me-1 text-success"></i> Recycling Centre Directory</h4>
        <div class="row g-3">
            <?php foreach ($centers as $c): ?>
                <div class="col-md-6">
                    <div class="eco-card h-100">
                        <h5 class="mb-2">
                            <?= \EcoBin\Services\Security::e($c->name) ?>
                            <span class="ms-2"><?= \EcoBin\Services\View::statusBadge($c->availability) ?></span>
                        </h5>
                        <p class="mb-1 small eco-subheading">
                            <i class="bi bi-geo-alt me-1 text-danger"></i> <?= \EcoBin\Services\Security::e($c->address) ?>
                        </p>
                        <p class="mb-1 small">
                            <i class="bi bi-recycle me-1 text-success"></i> <?= \EcoBin\Services\Security::e($c->acceptedMaterials) ?>
                        </p>
                        <?php if (!empty($c->operatingHours)): ?>
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-clock me-1"></i> <?= \EcoBin\Services\Security::e($c->operatingHours) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($centers)): ?>
                <div class="col-12"><div class="eco-card text-center text-muted">No recycling centres registered yet.</div></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <h4><i class="bi bi-trophy me-1 text-warning"></i> Leaderboard (Top 10)</h4>
        <div class="eco-card">
            <table class="table table-sm table-borderless mb-0">
                <thead><tr class="border-bottom"><th>Rank</th><th>Resident</th><th class="text-end">Earned</th></tr></thead>
                <tbody>
                <?php $rank = 1; foreach ($leaderboard as $l): ?>
                    <tr>
                        <td><strong>#<?= $rank++ ?></strong></td>
                        <td><?= \EcoBin\Services\Security::e($l['name']) ?></td>
                        <td class="text-end fw-bold" style="color: var(--eco-primary-dark);"><?= number_format((int)$l['total_earned']) ?> pts</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($leaderboard)): ?>
                    <tr><td colspan="3" class="text-center eco-subheading py-3" style="margin-bottom:0;">No points earned yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>