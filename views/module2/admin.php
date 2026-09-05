<?php
/*
 * @author  Jordan Liew Yi Xiang
 * @module  Module 2 — Waste Report & Collection Management
 */

use EcoBin\Services\Security;

/**
 * Priority carries real operational weight here — an admin scanning
 * this list needs to see what's overdue first, not just a flat feed
 * in submission order.
 */
function priorityWeight(?string $priority): int
{
    return match ($priority) {
        'Urgent' => 0,
        'High'   => 1,
        'Normal' => 2,
        'Low'    => 3,
        default  => 2,
    };
}

function waitingSince(\DateTime $createdAt): string
{
    $diff = (new \DateTime())->diff($createdAt);

    if ($diff->days >= 1) {
        return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' waiting';
    }
    if ($diff->h >= 1) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' waiting';
    }
    return 'Just submitted';
}

$pending = array_filter(
    $collections,
    fn($c) =>
        $c->status === 'Pending'
        && !$c->collectionStaff
);

$assigned = array_filter(
    $collections,
    fn($c) =>
        in_array(
            $c->status,
            [
                'Assigned',
                'In Progress'
            ],
            true
        )
        && (bool) $c->collectionStaff
);


usort($pending, function ($a, $b) {
    $ra = $a->wasteReport;
    $rb = $b->wasteReport;
    $weightA = priorityWeight($ra?->priority ?? null);
    $weightB = priorityWeight($rb?->priority ?? null);
    return $weightA <=> $weightB ?: $a->createdAt <=> $b->createdAt;
});

?>

    <h2 class="eco-heading">Collection Operations</h2>
    <p class="eco-subheading">Assign collection staff, schedule resident requests and monitor collection progress.</p>

<?php if (count($pending) > 0): ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Awaiting Assignment</h5>
        <span class="dispatch-count"><?= count($pending) ?> unassigned</span>
    </div>

    <div class="row g-3 mb-5">
        <?php foreach ($pending as $c): $r = $c->wasteReport; $resident = $c->resident; ?>

            <div class="col-lg-6">
                <div class="eco-card collection-card-<?= strtolower($r?->priority ?? 'normal') ?>">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-0">Collection #<?= $c->id ?></h5>
                            <span class="waiting-time"><?= waitingSince($c->createdAt) ?></span>
                        </div>
                        <?php if (($r?->priority ?? 'Normal') !== 'Normal'): ?>
                            <span class="priority-flag priority-<?= strtolower($r->priority) ?>"><?= Security::e($r->priority) ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="mb-3">
                        <strong><?= Security::e($resident?->name ?? '') ?></strong><br>
                        <span class="eco-subheading" style="margin-bottom: 0;">
                    <?= Security::e($r?->category ?? '') ?>
                            <?php if ($r?->wasteSize): ?> · <?= Security::e($r->wasteSize) ?> load<?php endif; ?>
                </span><br>
                        <small class="eco-subheading" style="margin-bottom: 0;">
                            <i class="bi bi-geo-alt"></i> <?= Security::e($r?->address ?? '') ?>
                        </small>
                    </p>

                    <form method="post" action="index.php?page=module2-assign">
                        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                        <input type="hidden" name="collection_id" value="<?= $c->id ?>">

                        <select class="form-select mb-2" name="staff_id" required>
                            <option value="">Dispatch to...</option>
                            <?php foreach ($staff as $s): ?>
                                <option value="<?= $s->id ?>"><?= Security::e($s->name) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input class="form-control mb-2" type="date" name="scheduled_date" min="<?= date('Y-m-d') ?>"
                               value="<?= $c->preferredDate->format('Y-m-d') ?>" required>

                        <button class="btn-eco w-100">Confirm Dispatch</button>
                    </form>

                </div>
            </div>

        <?php endforeach; ?>
    </div>

<?php endif; ?>


<?php if (count($assigned) > 0): ?>

    <h5 class="mb-3">In Progress &amp; Scheduled</h5>

    <div class="row g-3">
        <?php foreach ($assigned as $c): $r = $c->wasteReport; $resident = $c->resident; $s = $c->collectionStaff; ?>

            <div class="col-lg-6">
                <div class="eco-card-flat">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">Collection #<?= $c->id ?></h6>
                        <?= \EcoBin\Services\View::statusBadge($c->status) ?>
                    </div>

                    <div class="small">
                        <?= Security::e($resident?->name ?? '') ?> · <?= Security::e($r?->category ?? '') ?><br>
                        Crew: <?= Security::e($s?->name ?? 'Unassigned') ?> ·
                        Scheduled <?= $c->scheduledDate?->format('d M') ?>
                    </div>

                </div>
            </div>

        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php if (
    count($pending) === 0
    &&
    count($assigned) === 0
): ?>
    <div class="eco-card text-center py-5">
        <p class="eco-subheading mb-0">No active collection requests at the moment.</p>
    </div>
<?php endif; ?>