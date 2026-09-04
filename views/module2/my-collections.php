<?php

use EcoBin\Services\Security;

function collectionStatusClass(string $status): string
{
    return match ($status) {
        'Pending'     => 'eco-status-pending',
        'Assigned'    => 'eco-status-assigned',
        'In Progress' => 'eco-status-progress',
        'Completed'   => 'eco-status-completed',
        'Cancelled'   => 'eco-status-cancelled',
        default       => 'eco-status-cancelled'
    };
}

function collectionProgress(string $status): int
{
    return match ($status) {
        'Pending'     => 25,
        'Assigned'    => 50,
        'In Progress' => 75,
        'Completed'   => 100,
        default       => 0
    };
}

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$filteredCollections = array_filter(
    $collections,
    function ($collection) use ($search, $statusFilter, $reports) {

        if ($statusFilter !== '' && $collection->status !== $statusFilter) {
            return false;
        }

        if ($search === '') {
            return true;
        }

        $report = $collection->wasteReport;

        $haystack = strtolower(
            $collection->id . ' '
            . ($report->category ?? '') . ' '
            . ($report->address ?? '') . ' '
            . ($report->description ?? '') . ' '
            . $collection->status
        );

        return str_contains($haystack, strtolower($search));
    }
);

?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <h2 class="eco-heading mb-1">My Collections</h2>
        <p class="eco-subheading mb-0">Search and track your submitted collection requests.</p>
    </div>
    <span class="badge text-bg-success"><?= count($collections) ?> Total</span>
</div>

<!-- SEARCH / FILTER -->
<div class="eco-card mb-4">
    <form method="get" class="row g-3">

        <input type="hidden" name="page" value="my-collections">

        <div class="col-lg-6">
            <label class="form-label">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input class="form-control" name="search" value="<?= Security::e($search) ?>"
                       placeholder="Search request ID, category or location">
            </div>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="">All Statuses</option>
                <?php foreach (['Pending', 'Assigned', 'In Progress', 'Completed', 'Cancelled'] as $status): ?>
                    <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-2 d-flex align-items-end">
            <button class="btn btn-eco w-100">Filter</button>
        </div>

    </form>
</div>

<!-- NO RESULT -->
<?php if (count($filteredCollections) === 0): ?>
    <div class="eco-card text-center py-5">
        <div style="font-size:50px;">🗑️</div>
        <h4 class="mt-3">No Collection Requests Found</h4>
        <p class="text-muted mb-0">
            <?php if (count($collections) === 0): ?>
                You haven't submitted any waste reports yet.
                <a href="index.php?page=module2">Report waste</a> to get started.
            <?php else: ?>
                Try changing your search or filter above.
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>

<!-- COLLECTION CARDS -->
<div class="row g-4">

    <?php foreach ($filteredCollections as $c):
        $report   = $c->wasteReport;
        $progress = collectionProgress($c->status);
        ?>

        <div class="col-12">
            <div class="eco-card">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Collection Request</div>
                        <h4 class="mt-1 mb-1">
                            #<?= $c->id ?>
                            <?php if ($report): ?> · <?= Security::e($report->category) ?><?php endif; ?>
                        </h4>
                        <div class="text-muted">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?= Security::e($report->address ?? 'No location available') ?>
                        </div>
                    </div>

                    <div class="text-end">
                        <span class="eco-status <?= collectionStatusClass($c->status) ?>"><?= Security::e($c->status) ?></span>

                        <?php if ($report): ?>
                            <div class="mt-2">
                                <?php if (property_exists($report, 'priority')): ?>
                                    <span class="badge text-bg-light"><?= Security::e($report->priority) ?> Priority</span>
                                <?php endif; ?>
                                <?php if (property_exists($report, 'wasteSize')): ?>
                                    <span class="badge text-bg-light"><?= Security::e($report->wasteSize) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>

                <div class="row g-4">

                    <div class="col-lg-4">
                        <h6>Request Details</h6>
                        <div class="small">
                            <div class="mb-2">
                                <span class="text-muted">Submitted</span><br>
                                <strong><?= isset($c->createdAt) ? $c->createdAt->format('d M Y, h:i A') : '-' ?></strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted">Preferred Date</span><br>
                                <strong><?= $c->preferredDate->format('d M Y') ?></strong>
                            </div>
                            <div>
                                <span class="text-muted">Scheduled Date</span><br>
                                <strong><?= $c->scheduledDate ? $c->scheduledDate->format('d M Y') : 'Not scheduled yet' ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <h6>Waste Details</h6>
                        <?php if ($report): ?>
                            <div class="small">
                                <div class="mb-2">
                                    <strong>Description</strong>
                                    <div class="text-muted"><?= Security::e($report->description) ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">Waste report information unavailable.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-4">
                        <h6>Collection Remarks</h6>
                        <div class="text-muted small">
                            <?= $c->remarks ? nl2br(Security::e($c->remarks)) : 'No collection remarks yet.' ?>
                        </div>
                    </div>

                </div>

                <?php if ($c->status !== 'Cancelled'): ?>
                    <hr>
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Collection Progress</strong>
                            <span class="text-muted small"><?= $progress ?>%</span>
                        </div>

                        <div class="progress" style="height:9px;">
                            <div class="progress-bar bg-success" style="width: <?= $progress ?>%;"></div>
                        </div>

                        <div class="row text-center mt-3 small">
                            <div class="col">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <div>Submitted</div>
                            </div>
                            <div class="col">
                                <i class="bi <?= in_array($c->status, ['Assigned', 'In Progress', 'Completed'], true) ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i>
                                <div>Assigned</div>
                            </div>
                            <div class="col">
                                <i class="bi <?= in_array($c->status, ['In Progress', 'Completed'], true) ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i>
                                <div>In Progress</div>
                            </div>
                            <div class="col">
                                <i class="bi <?= $c->status === 'Completed' ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i>
                                <div>Completed</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($c->status === 'Pending'): ?>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted small">You may cancel this request before collection staff is assigned.</span>
                        <form method="post" action="index.php?page=module2-cancel"
                              onsubmit="return confirm('Are you sure you want to cancel this collection request?');">
                            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                            <input type="hidden" name="collection_id" value="<?= $c->id ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Cancel Request
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>

</div>