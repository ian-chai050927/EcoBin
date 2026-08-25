<div class="d-flex justify-content-between align-items-center"><div><h2>Recycling & Rewards</h2><p class="text-muted">Resident recycling submissions, appointments, reward points and history.</p></div><div class="card p-3"><div class="small-muted">Reward Balance</div><div class="stat"><?= (int)$balance ?> pts</div></div></div>
<div class="row g-4 mt-1">
<div class="col-lg-6"><div class="card p-4"><h4>Recycling Submission</h4><form method="post" action="index.php?page=module3-submit">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<select class="form-select mb-2" name="center_id" required><option value="">Choose Centre</option><?php foreach($centers as $c): ?><option value="<?= $c->id ?>"><?= \EcoBin\Services\Security::e($c->name) ?> (<?= \EcoBin\Services\Security::e($c->availability) ?>)</option><?php endforeach; ?></select>
<input class="form-control mb-2" name="material" placeholder="Material e.g. Plastic" required>
<input class="form-control mb-2" type="number" step="0.01" min="0.01" name="weight_kg" placeholder="Weight (kg)" required>
<button class="btn btn-success">Submit for Review</button></form></div></div>
<div class="col-lg-6"><div class="card p-4"><h4>Recycling Appointment</h4><form method="post" action="index.php?page=module3-appointment">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<select class="form-select mb-2" name="center_id" required><option value="">Choose Centre</option><?php foreach($centers as $c): ?><option value="<?= $c->id ?>"><?= \EcoBin\Services\Security::e($c->name) ?></option><?php endforeach; ?></select>
<input class="form-control mb-2" type="datetime-local" name="appointment_at" required>
<button class="btn btn-success">Book Appointment</button></form></div></div>
</div>
<h4 class="mt-4">Recycling History</h4><div class="card p-3"><table class="table"><thead><tr><th>ID</th><th>Material</th><th>Weight</th><th>Points</th><th>Status</th></tr></thead><tbody>
<?php foreach($subs as $s): ?><tr><td><?= $s->id ?></td><td><?= \EcoBin\Services\Security::e($s->material) ?></td><td><?= $s->weightKg ?> kg</td><td><?= $s->points ?></td><td><?= \EcoBin\Services\Security::e($s->status) ?></td></tr><?php endforeach; ?></tbody></table></div>