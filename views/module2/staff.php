<h2>My Collection Jobs</h2>
<div class="row g-3"><?php foreach($tasks as $c): $r=$reports[$c->wasteReportId]??null; $resident=$residents[$c->residentId]??null; ?>
<div class="col-lg-6"><div class="card p-4">
<h5>Collection #<?= $c->id ?> · <?= \EcoBin\Services\Security::e($c->status) ?></h5>
<p><strong>Resident:</strong> <?= \EcoBin\Services\Security::e($resident?->name ?? '') ?><br>
<strong>Waste:</strong> <?= \EcoBin\Services\Security::e($r?->category ?? '') ?><br>
<strong>Location:</strong> <?= \EcoBin\Services\Security::e($r?->address ?? '') ?><br>
<strong>Scheduled:</strong> <?= $c->scheduledDate?->format('Y-m-d') ?? '-' ?></p>
<?php if($c->status!=='Completed'): ?><form method="post" action="index.php?page=module2-status">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<input type="hidden" name="collection_id" value="<?= $c->id ?>">
<textarea class="form-control mb-2" name="remarks" maxlength="1000" placeholder="Remarks"><?= \EcoBin\Services\Security::e($c->remarks ?? '') ?></textarea>
<?php if($c->status==='Assigned'): ?><button class="btn btn-primary" name="status" value="In Progress">Start Collection</button>
<?php elseif($c->status==='In Progress'): ?><button class="btn btn-success" name="status" value="Completed">Mark Completed</button><?php endif; ?>
</form><?php else: ?><div class="alert alert-success">Completed</div><?php endif; ?>
</div></div><?php endforeach; ?></div>