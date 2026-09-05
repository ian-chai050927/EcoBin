<?php
/**
 * Module 1: User Authentication and Role Management
 * @author Chai Ee Yuan
 */
?>
<div class="auth-shell">

    <!-- ======================================================
         LEFT PANEL — branding & motivation
    ====================================================== -->
    <div class="auth-side">
        <div class="auth-side-brand">
            <i class="bi bi-recycle"></i> EcoBin
        </div>

        <div class="auth-side-quote">
            Lost access? No problem — we'll get you back in&nbsp;seconds.
        </div>

        <div class="auth-side-stat">
            <div>
                <div class="auth-side-stat-num">🔒</div>
                <div class="auth-side-stat-label">Secure token · expires in 1 hour</div>
            </div>
        </div>
    </div>

    <!-- ======================================================
         RIGHT PANEL — form
    ====================================================== -->
    <div class="auth-form-panel">
        <div class="auth-form-box">

            <!-- Back link -->
            <a href="index.php?page=login" class="d-inline-flex align-items-center gap-1 text-muted small mb-4 text-decoration-none" style="font-weight:500;">
                <i class="bi bi-arrow-left"></i> Back to login
            </a>

            <h2 style="margin-bottom:6px;">Forgot your password?</h2>
            <p class="eco-subheading" style="margin-bottom:28px;">
                Enter the email address linked to your EcoBin account and we'll email you a secure reset link.
            </p>

            <?php
            // Flash messages
            $flashType = $_SESSION['flash_type'] ?? null;
            $flashMsg  = $_SESSION['flash_msg'] ?? null;
            unset($_SESSION['flash_type'], $_SESSION['flash_msg']);
            ?>

            <?php if ($flashMsg): ?>
                <div class="alert alert-<?= $flashType === 'success' ? 'success' : 'danger' ?> d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius:10px;font-size:0.9rem;">
                    <i class="bi bi-<?= $flashType === 'success' ? 'check-circle' : 'exclamation-circle' ?>-fill"></i>
                    <?= \EcoBin\Services\Security::e($flashMsg) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?page=forgot" novalidate>
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

                <div class="auth-field">
                    <label for="forgot-email">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                            <i class="bi bi-envelope text-muted"></i>
                        </span>
                        <input
                            id="forgot-email"
                            type="email"
                            name="email"
                            class="form-control border-start-0"
                            style="border-radius:0 10px 10px 0;"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <button id="forgot-submit-btn" type="submit" class="btn-auth w-100 mt-2">
                    <span id="forgot-btn-text">Send Reset Link <i class="bi bi-send ms-1"></i></span>
                    <span id="forgot-btn-loading" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>Sending…
                    </span>
                </button>
            </form>

            <!-- How it works -->
            <div class="eco-card mt-4" style="background:#f7faf8;border:1px solid #e5ebe7;padding:20px 24px;">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div style="width:28px;height:28px;background:#e8f5ee;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-1-circle-fill text-success" style="font-size:14px;"></i>
                    </div>
                    <div class="small">Enter your registered email and click <strong>Send Reset Link</strong>.</div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div style="width:28px;height:28px;background:#e8f5ee;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-2-circle-fill text-success" style="font-size:14px;"></i>
                    </div>
                    <div class="small">Check your email inbox for the reset link (check spam if needed).</div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <div style="width:28px;height:28px;background:#e8f5ee;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-3-circle-fill text-success" style="font-size:14px;"></i>
                    </div>
                    <div class="small">Click the link and choose your new password. Link expires in <strong>1 hour</strong>.</div>
                </div>
            </div>

            <?php if (isset($app['mail']['enabled']) && !$app['mail']['enabled']): ?>
                <p class="eco-subheading small mt-3 mb-0" style="font-size:0.78rem;">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Local demo:</strong> email is written to <code>storage/mail.log</code>. Check that file for your reset link.
                </p>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
// Show loading state on submit
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('forgot-btn-text').classList.add('d-none');
    document.getElementById('forgot-btn-loading').classList.remove('d-none');
    document.getElementById('forgot-submit-btn').disabled = true;
});
</script>