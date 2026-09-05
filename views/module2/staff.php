<?php
/*
 * @author  Jordan Liew Yi Xiang
 * @module  Module 2 — Waste Report & Collection Management
 */

use EcoBin\Services\Security;

// A collector's day is ordered by what's scheduled first, not by
// internal ID — that's the real operational reading of this list.
usort($tasks, fn($a, $b) => ($a->scheduledDate ?? $a->createdAt) <=> ($b->scheduledDate ?? $b->createdAt));

$today = (new \DateTime())->format('Y-m-d');

?>

    <h2 class="eco-heading">My Collection Jobs</h2>
    <p class="eco-subheading">Your assigned stops, in schedule order.</p>

<?php if (count($tasks) === 0): ?>

    <div class="eco-card text-center py-5">
        <p class="eco-subheading mb-0">No jobs assigned to you right now.</p>
    </div>

<?php else: ?>

    <div class="row g-3">
        <?php foreach ($tasks as $c): $r = $c->wasteReport; $resident = $c->resident;
            $isToday = $c->scheduledDate && $c->scheduledDate->format('Y-m-d') === $today;
            ?>

            <div class="col-lg-6">
                <div class="eco-card <?= $isToday ? 'collection-card-today' : '' ?>">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-0">Collection #<?= $c->id ?></h5>
                            <?php if ($isToday): ?>
                                <span class="today-flag">Today</span>
                            <?php elseif ($c->scheduledDate): ?>
                                <span class="waiting-time"><?= $c->scheduledDate->format('D, d M') ?></span>
                            <?php endif; ?>
                        </div>
                        <?= \EcoBin\Services\View::statusBadge($c->status) ?>
                    </div>

                    <p class="mb-3">
                        <strong><?= Security::e($resident?->name ?? '') ?></strong><br>
                        <span class="eco-subheading" style="margin-bottom: 0;">
                    <?= Security::e($r?->category ?? '') ?>
                            <?php if ($r?->wasteSize): ?> · <?= Security::e($r->wasteSize) ?><?php endif; ?>
                </span><br>
                        <small class="eco-subheading" style="margin-bottom: 0;">
                            <i class="bi bi-geo-alt"></i> <?= Security::e($r?->address ?? '') ?>
                        </small>
                    </p>

                    <?php if ($c->status !== 'Completed'): ?>

                        <form method="post" action="index.php?page=module2-status">
                            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                            <input type="hidden" name="collection_id" value="<?= $c->id ?>">

                            <textarea class="form-control mb-2" name="remarks" maxlength="1000"
                                      placeholder="Note anything the resident or next shift should know"><?= Security::e($c->remarks ?? '') ?></textarea>

                            <?php if ($c->status === 'Assigned'): ?>
                                <button class="btn-eco-outline" name="status" value="In Progress">On My Way</button>
                            <?php elseif ($c->status === 'In Progress'): ?>
                                <button class="btn-eco" name="status" value="Completed">Collected</button>
                            <?php endif; ?>
                        </form>

                    <?php else: ?>

                        <div class="collection-assigned-note">Collected — done here</div>

                    <?php endif; ?>

                </div>
            </div>

        <?php endforeach; ?>
    </div>

<?php endif; ?>