<?php $role = $_SESSION['role'] ?? null; ?>

<?php if (!$role): ?>

    <div class="eco-hero eco-hero-photo">

        <h1>Smarter waste services for cleaner communities.</h1>

        <p>Report waste, arrange collection, recycle responsibly and stay updated through one community platform.</p>

        <div class="d-flex gap-2 flex-wrap mt-3">
            <a class="btn-eco" href="index.php?page=register">Create Account</a>
            <a class="btn-eco-outline" href="index.php?page=login">Login</a>
        </div>
    </div>


<?php elseif ($role === 'Resident'): ?>

    <div class="eco-hero mb-4">
        <h1>Welcome back, <?= \EcoBin\Services\Security::e($_SESSION['name']) ?>.</h1>
        <p>Manage your waste collection and recycling activities.</p>

        <div class="d-flex gap-2 flex-wrap mt-3">
            <a class="btn-eco" href="index.php?page=module2">Report Waste</a>
            <a class="btn-eco-outline" href="index.php?page=module3">Recycle &amp; Earn</a>
            <a class="btn-eco-outline" href="index.php?page=notifications">View Notifications</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <a class="eco-card-link" href="index.php?page=module2">
                <div class="eco-card h-100">
                    <h4>Waste Collection</h4>
                    <p class="eco-subheading" style="margin-bottom: 0;">
                        Submit a geo-tagged waste report, choose a collection date and track the collection process.
                    </p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a class="eco-card-link" href="index.php?page=module3">
                <div class="eco-card h-100">
                    <h4>Recycling Rewards</h4>
                    <p class="eco-subheading" style="margin-bottom: 0;">
                        Submit recyclable materials, book centre appointments and earn reward points.
                    </p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a class="eco-card-link" href="index.php?page=profile">
                <div class="eco-card h-100">
                    <h4>My Account</h4>
                    <p class="eco-subheading" style="margin-bottom: 0;">
                        Update your own profile and view your assigned account role.
                    </p>
                </div>
            </a>
        </div>
    </div>


<?php elseif ($role === 'Admin'): ?>

    <div class="eco-hero mb-4">
        <span class="badge mb-3" style="background: #10261d; color: white; font-weight: 600;">
            Administration Console
        </span>

        <h1>EcoBin Operations Dashboard</h1>
        <p>Manage user accounts, collection operations, reports and system administration.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <a class="eco-card-link" href="index.php?page=users">
                <div class="eco-card h-100">
                    <h4>User Management</h4>
                    <p class="eco-subheading" style="margin-bottom: 0;">Create accounts, change roles and activate or suspend users.</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="eco-card-link" href="index.php?page=module2-admin">
                <div class="eco-card h-100">
                    <h4>Collection Operations</h4>
                    <p class="eco-subheading" style="margin-bottom: 0;">Assign collectors and manage waste collection requests.</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="eco-card-link" href="index.php?page=module4">
                <div class="eco-card h-100">
                    <h4>Analytics</h4>
                    <p class="eco-subheading" style="margin-bottom: 0;">Review service performance and generate reports.</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="eco-card-link" href="index.php?page=module5-admin">
                <div class="eco-card h-100">
                    <h4>System Administration</h4>
                    <p class="eco-subheading" style="margin-bottom: 0;">Announcements, configuration and audit/activity logs.</p>
                </div>
            </a>
        </div>
    </div>


<?php elseif ($role === 'Collection Staff'): ?>

    <div class="eco-hero">
        <h1>Collection Staff Workspace</h1>
        <p>View jobs assigned specifically to you and update collection progress.</p>
        <a class="btn-eco mt-2" href="index.php?page=module2-staff">Open My Collection Jobs</a>
    </div>


<?php elseif ($role === 'Recycling Center Operator'): ?>

    <div class="eco-hero">
        <h1>Recycling Centre Workspace</h1>
        <p>Maintain centre information and review recycling submissions and appointments.</p>
        <a class="btn-eco mt-2" href="index.php?page=module3-operator">Open Centre Operations</a>
    </div>

<?php endif; ?>


<?php if (!empty($announcements)): ?>

    <div class="mt-5">
        <h3 class="eco-heading" style="font-size: 22px;">Latest Announcements</h3>

        <?php foreach ($announcements as $a): ?>
            <div class="eco-card mb-2">
                <strong><?= \EcoBin\Services\Security::e($a->title) ?></strong>
                <div class="mt-1"><?= nl2br(\EcoBin\Services\Security::e($a->message)) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>