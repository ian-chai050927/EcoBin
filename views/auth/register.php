<div class="row justify-content-center"><div class="col-md-6"><div class="card p-4">
<h2>Resident Registration</h2>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<label class="form-label">Name</label><input class="form-control mb-3" name="name" maxlength="100" required>
<label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required>
<label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" minlength="8" required>
<button class="btn btn-success">Register</button>
</form>
<p class="small-muted mt-3">Passwords are stored using PHP password hashing, not reversible encryption.</p>
</div></div></div>