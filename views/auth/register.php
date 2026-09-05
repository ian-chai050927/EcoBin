<?php
/**
 * Module 1: User Authentication and Role Management
 * @author Chai Ee Yuan
 */
?>
<div class="auth-shell">

    <div class="auth-side">
        <div class="auth-side-brand">
            <i class="bi bi-recycle"></i> EcoBin
        </div>

        <div class="auth-side-quote" style="margin-top: auto; margin-bottom: auto;">
            Join the community. Report waste, schedule pickups, and earn rewards for recycling.
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-form-box">

            <h2 style="font-weight: 800; color: #1a1a2e; margin-bottom: 8px;">Create your account</h2>
            <p style="color: #666; margin-bottom: 32px; font-size: 14px;">Resident registration — get started in under a minute.</p>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; text-transform: uppercase; color: #666; font-weight: 700; margin-bottom: 6px; letter-spacing: 0.5px;">Name</label>
                    <input class="form-control" name="name" maxlength="100" required style="background-color: #edf2fc; border: none; padding: 12px 16px; border-radius: 4px; width: 100%;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; text-transform: uppercase; color: #666; font-weight: 700; margin-bottom: 6px; letter-spacing: 0.5px;">Email</label>
                    <input class="form-control" type="email" name="email" required style="background-color: #edf2fc; border: none; padding: 12px 16px; border-radius: 4px; width: 100%;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 11px; text-transform: uppercase; color: #666; font-weight: 700; margin-bottom: 6px; letter-spacing: 0.5px;">Password</label>
                    <input class="form-control" type="password" name="password" minlength="8" required style="background-color: #edf2fc; border: none; padding: 12px 16px; border-radius: 4px; width: 100%;">
                </div>

                <button class="btn-eco" style="background-color: #1b7f4f; color: white; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-bottom: 24px;">
                    Register &rarr;
                </button>
            </form>

            <div style="margin-bottom: 24px;">
                <a href="index.php?page=login" style="color: #333; font-size: 14px; text-decoration: none;">Log in</a>
            </div>

            <div style="border-left: 2px solid #1b7f4f; padding-left: 12px;">
                <p style="color: #666; font-size: 12px; margin: 0;">
                    Passwords are stored using PHP password hashing, not reversible encryption.
                </p>
            </div>

        </div>
    </div>

</div>