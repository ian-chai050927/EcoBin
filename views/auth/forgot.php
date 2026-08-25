<div class="row justify-content-center"><div class="col-md-5"><div class="card p-4">
<h2>Forgot Password</h2><form method="post">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
<button class="btn btn-success">Generate Reset Email</button></form>
<p class="small-muted mt-3">For local XAMPP demo, generated email is written to storage/mail.log unless SMTP is enabled.</p>
</div></div></div>