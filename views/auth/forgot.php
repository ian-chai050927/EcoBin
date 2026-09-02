<div class="auth-shell">

    <div class="auth-side">
        <div class="auth-side-brand">
            <i class="bi bi-recycle"></i> EcoBin
        </div>

        <div class="auth-side-quote">
            Lost access happens. Let's get you back in.
        </div>

        <div></div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-form-box">

            <h2>Forgot password</h2>
            <p class="eco-subheading">Enter your email and we'll send a reset link.</p>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
                <button class="btn-eco w-100">Generate Reset Email</button>
            </form>

            <p class="eco-subheading small mt-3 mb-0">
                For local XAMPP demo, generated email is written to storage/mail.log unless SMTP is enabled.
            </p>

        </div>
    </div>

</div>