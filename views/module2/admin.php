<h2>Collection Operations</h2>
<p class="text-muted">Assign collection staff, schedule resident requests and monitor collection progress.</p>
<div class="row g-3">
<?php foreach($collections as $c): $r=$reports[$c->wasteReportId]??null; $resident=$residents[$c->residentId]??null; ?>
<div class="col-lg-6"><div class="card p-4 h-100">
<div class="d-flex justify-content-between"><h5>Collection #<?= $c->id ?></h5><span class="badge text-bg-secondary"><?= \EcoBin\Services\Security::e($c->status) ?></span></div>
<p><strong>Resident:</strong> <?= \EcoBin\Services\Security::e($resident?->name ?? '') ?><br>
<strong>Category:</strong> <?= \EcoBin\Services\Security::e($r?->category ?? '') ?><br>
<strong>Address:</strong> <?= \EcoBin\Services\Security::e($r?->address ?? '') ?><br>
<strong>Preferred:</strong> <?= $c->preferredDate->format('Y-m-d') ?></p>
<?php if(!$c->collectionStaffId): ?><form method="post" action="index.php?page=module2-assign">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<input type="hidden" name="collection_id" value="<?= $c->id ?>">
<select class="form-select mb-2" name="staff_id" required><option value="">Select Collection Staff</option><?php foreach($staff as $s): ?><option value="<?= $s->id ?>"><?= \EcoBin\Services\Security::e($s->name) ?></option><?php endforeach; ?></select>
<input class="form-control mb-2" type="date" name="scheduled_date" min="<?= date('Y-m-d') ?>" required>
<button class="btn btn-success">Assign & Schedule</button></form>
<?php else: ?><div class="alert alert-success mb-0">Assigned to staff #<?= $c->collectionStaffId ?> for <?= $c->scheduledDate?->format('Y-m-d') ?></div><?php endif; ?>
</div></div><?php endforeach; ?></div>