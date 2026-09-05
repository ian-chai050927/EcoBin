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

        <div class="auth-side-quote">
            Every report, every pickup, every kilogram recycled — tracked in one place for your community.
        </div>

        <div class="auth-side-stat">
            <div>
                <div class="auth-side-stat-num">SDG 12</div>
                <div class="auth-side-stat-label">Responsible Consumption &amp; Production</div>
            </div>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-form-box">

            <h2>Welcome back</h2>
            <p class="eco-subheading">Log in to manage your waste collection and recycling.</p>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

                <div class="auth-field">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="auth-field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                    <a href="index.php?page=forgot" class="auth-forgot">Forgot password?</a>
                </div>

                <button class="btn-auth">
                    Login <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="auth-links">
                <a href="index.php?page=register">Register</a>
            </div>

            <div class="auth-note">
                Demo password for seeded accounts: <strong>Password123!</strong>
            </div>

        </div>
    </div>

</div>