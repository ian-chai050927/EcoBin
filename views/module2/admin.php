<h2 class="eco-heading">Collection Operations</h2>
<p class="eco-subheading">Assign collection staff, schedule resident requests and monitor collection progress.</p>

<div class="row g-3">
    <?php foreach ($collections as $c): $r = $reports[$c->wasteReportId] ?? null; $resident = $residents[$c->residentId] ?? null; ?>

        <div class="col-lg-6">
            <div class="eco-card <?= !$c->collectionStaffId ? 'collection-card-pending' : '' ?> h-100">

                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="mb-0">Collection #<?= $c->id ?></h5>
                    <?= \EcoBin\Services\View::statusBadge($c->status) ?>
                </div>

                <p class="mb-3">
                    <strong><?= \EcoBin\Services\Security::e($resident?->name ?? '') ?></strong><br>
                    <span class="eco-subheading" style="margin-bottom: 0;">
                    <?= \EcoBin\Services\Security::e($r?->category ?? '') ?>
                    · <?= \EcoBin\Services\Security::e($r?->address ?? '') ?>
                </span><br>
                    <small class="eco-subheading" style="margin-bottom: 0;">
                        Preferred: <?= $c->preferredDate->format('d M Y') ?>
                    </small>
                </p>

                <?php if (!$c->collectionStaffId): ?>

                    <form method="post" action="index.php?page=module2-assign">
                        <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                        <input type="hidden" name="collection_id" value="<?= $c->id ?>">

                        <select class="form-select mb-2" name="staff_id" required>
                            <option value="">Select Collection Staff</option>
                            <?php foreach ($staff as $s): ?>
                                <option value="<?= $s->id ?>"><?= \EcoBin\Services\Security::e($s->name) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input class="form-control mb-2" type="date" name="scheduled_date" min="<?= date('Y-m-d') ?>" required>

                        <button class="btn-eco w-100">Assign &amp; Schedule</button>
                    </form>

                <?php else: ?>

                    <div class="collection-assigned-note">
                        Assigned to staff #<?= $c->collectionStaffId ?> for <?= $c->scheduledDate?->format('d M Y') ?>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>
</div>