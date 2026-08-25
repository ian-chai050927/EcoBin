<h2>Recycling Centre Operations</h2>
<p class="text-muted">Addresses lecturer feedback: operators maintain centre information, review submissions/appointments and update availability.</p>
<div class="card p-4 mb-4"><h4>Add / Update Recycling Centre</h4><form method="post" action="index.php?page=module3-center-save">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<div class="row g-2"><div class="col-md-3"><input class="form-control" name="name" placeholder="Centre name" required></div>
<div class="col-md-3"><input class="form-control" name="address" placeholder="Address" required></div>
<div class="col-md-3"><input class="form-control" name="accepted_materials" placeholder="Plastic, Paper, Metal" required></div>
<div class="col-md-2"><select class="form-select" name="availability"><option>Open</option><option>Full</option><option>Closed</option></select></div>
<div class="col-md-1"><button class="btn btn-success">Save</button></div></div></form></div>

<div class="row g-4"><div class="col-lg-6"><div class="card p-3"><h4>Review Recycling Submissions</h4><table class="table"><thead><tr><th>ID</th><th>Material</th><th>Weight</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($subs as $s): ?><tr><td><?= $s->id ?></td><td><?= \EcoBin\Services\Security::e($s->material) ?></td><td><?= $s->weightKg ?></td><td><?= \EcoBin\Services\Security::e($s->status) ?></td><td>
<?php if($s->status==='Pending'): ?><form method="post" action="index.php?page=module3-review-submission" class="d-flex gap-1">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>"><input type="hidden" name="submission_id" value="<?= $s->id ?>">
<button class="btn btn-sm btn-success" name="status" value="Approved">Approve</button><button class="btn btn-sm btn-danger" name="status" value="Rejected">Reject</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="col-lg-6"><div class="card p-3"><h4>Review Appointments</h4><table class="table"><thead><tr><th>ID</th><th>Date</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($appts as $a): ?><tr><td><?= $a->id ?></td><td><?= $a->appointmentAt->format('Y-m-d H:i') ?></td><td><?= \EcoBin\Services\Security::e($a->status) ?></td><td>
<form method="post" action="index.php?page=module3-review-appointment" class="d-flex gap-1"><input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>"><input type="hidden" name="appointment_id" value="<?= $a->id ?>">
<button class="btn btn-sm btn-success" name="status" value="Confirmed">Confirm</button><button class="btn btn-sm btn-primary" name="status" value="Completed">Complete</button><button class="btn btn-sm btn-secondary" name="status" value="Cancelled">Cancel</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>