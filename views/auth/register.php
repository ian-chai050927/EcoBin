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
            Report waste, book collection, and earn points for what you recycle.
        </div>

        <div class="auth-side-stat">
            <div>
                <div class="auth-side-stat-num">4 roles</div>
                <div class="auth-side-stat-label">Residents, staff, operators &amp; admins in one system</div>
            </div>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-form-box">

            <h2>Create your account</h2>
            <p class="eco-subheading">Resident registration — get started in under a minute.</p>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

                <label class="form-label">Name</label>
                <input class="form-control mb-3" name="name" maxlength="100" required>

                <label class="form-label">Email</label>
                <input class="form-control mb-3" type="email" name="email" required>

                <label class="form-label">Password</label>
                <input class="form-control mb-3" type="password" name="password" minlength="8" required>

                <button class="btn-eco w-100">Register</button>
            </form>

            <p class="eco-subheading small mt-3 mb-0">
                Passwords are stored using PHP password hashing, not reversible encryption.
            </p>

        </div>
    </div>

</div>