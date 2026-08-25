<div class="row justify-content-center"><div class="col-md-5"><div class="card p-4">
<h2>Login</h2>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required>
<label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" required>
<button class="btn btn-success w-100">Login</button>
</form>
<div class="mt-3"><a href="index.php?page=forgot">Forgot password?</a> · <a href="index.php?page=register">Register</a></div>
<div class="alert alert-secondary mt-3 small">Demo password for seeded accounts: <strong>Password123!</strong></div>
</div></div></div>