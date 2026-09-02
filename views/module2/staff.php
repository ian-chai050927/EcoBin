<h2 class="eco-heading">My Collection Jobs</h2>
<p class="eco-subheading">Jobs assigned to you, in progress order.</p>

<div class="row g-3">
    <?php foreach ($tasks as $c): $r = $reports[$c->wasteReportId] ?? null; $resident = $residents[$c->residentId] ?? null; ?>

        <div class="col-lg-6">
            <div class="eco-card h-100">

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
                        Scheduled: <?= $c->scheduledDate?->format('d M Y') ?? '-' ?>
                    </small>
                </p>

                <?php if ($c->status !== 'Completed'): ?>

                    <form method="post" action="index.php?page=module2-status">
                        <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                        <input type="hidden" name="collection_id" value="<?= $c->id ?>">

                        <textarea class="form-control mb-2" name="remarks" maxlength="1000" placeholder="Remarks"><?= \EcoBin\Services\Security::e($c->remarks ?? '') ?></textarea>

                        <?php if ($c->status === 'Assigned'): ?>
                            <button class="btn-eco-outline" name="status" value="In Progress">Start Collection</button>
                        <?php elseif ($c->status === 'In Progress'): ?>
                            <button class="btn-eco" name="status" value="Completed">Mark Completed</button>
                        <?php endif; ?>
                    </form>

                <?php else: ?>

                    <div class="collection-assigned-note">Completed</div>

                <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>
</div>