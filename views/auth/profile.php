<?php
/**
 * Module 1: User Authentication and Role Management
 * @author Chai Ee Yuan
 */
?>
<div class="profile-hero">
    <div class="profile-hero-avatar">
        <?= strtoupper(substr($user->name, 0, 1)) ?>
    </div>
    <div>
        <p class="profile-hero-name"><?= \EcoBin\Services\Security::e($user->name) ?></p>
        <div class="profile-hero-role"><?= \EcoBin\Services\Security::e($user->role) ?> · <?= \EcoBin\Services\View::statusBadge($user->status) ?></div>
    </div>
</div>

<div class="row g-4">

    <div class="col-lg-8">
        <div class="eco-card">

            <h2 class="eco-heading">Account Details</h2>
            <p class="eco-subheading">Manage your personal EcoBin profile.</p>

            <form method="post">

                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

                <label class="form-label">Full Name</label>
                <input class="form-control mb-3" name="name" value="<?= \EcoBin\Services\Security::e($user->name) ?>" required>

                <label class="form-label">Email Address</label>
                <input class="form-control mb-3" value="<?= \EcoBin\Services\Security::e($user->email) ?>" disabled>

                <button class="btn-eco">Update Profile</button>

            </form>

        </div>
    </div>


    <div class="col-lg-4">
        <div class="eco-card-flat">

            <h4>Your Access</h4>

            <?php if ($user->role === 'Resident'): ?>

                <ul>
                    <li>Submit waste reports</li>
                    <li>Track own collections</li>
                    <li>Use recycling & rewards</li>
                    <li>View own notifications</li>
                </ul>

            <?php elseif ($user->role === 'Admin'): ?>

                <ul>
                    <li>Create and manage users</li>
                    <li>Change roles/status</li>
                    <li>Assign collection staff</li>
                    <li>Access reports and system administration</li>
                </ul>

            <?php elseif ($user->role === 'Collection Staff'): ?>

                <ul>
                    <li>View assigned collection jobs</li>
                    <li>Update assigned job status</li>
                    <li>View notifications</li>
                </ul>

            <?php elseif ($user->role === 'Recycling Center Operator'): ?>

                <ul>
                    <li>Maintain recycling centre information</li>
                    <li>Review recycling submissions</li>
                    <li>Manage recycling appointments</li>
                </ul>

            <?php endif; ?>

        </div>
    </div>

</div>