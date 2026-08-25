<div class="row justify-content-center"><div class="col-md-5"><div class="card p-4">
<h2>Reset Password</h2><form method="post">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<input type="hidden" name="token" value="<?= \EcoBin\Services\Security::e($token) ?>">
<input class="form-control mb-3" type="password" name="password" minlength="8" placeholder="New password" required>
<button class="btn btn-success">Reset Password</button></form>
</div></div></div>