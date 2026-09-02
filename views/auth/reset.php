<div class="auth-shell">

    <div class="auth-side">
        <div class="auth-side-brand">
            <i class="bi bi-recycle"></i> EcoBin
        </div>

        <div class="auth-side-quote">
            Almost there — set a new password to finish.
        </div>

        <div></div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-form-box">

            <h2>Reset password</h2>
            <p class="eco-subheading">Choose a new password for your account.</p>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <input type="hidden" name="token" value="<?= \EcoBin\Services\Security::e($token) ?>">
                <input class="form-control mb-3" type="password" name="password" minlength="8" placeholder="New password" required>
                <button class="btn-eco w-100">Reset Password</button>
            </form>

        </div>
    </div>

</div>