<?php
$role = $_SESSION['role'] ?? null;
?>

<?php if (!$role): ?>

<div class="card hero-card p-5">
<div class="row align-items-center">

<div class="col-lg-8">

<span class="badge text-bg-success mb-3">
SDG 12 · Responsible Consumption and Production
</span>

<h1 class="display-4 fw-bold">
Smarter waste services for cleaner communities.
</h1>

<p class="lead muted">
Report waste, arrange collection, recycle responsibly and stay updated through one community platform.
</p>

<a class="btn btn-success btn-lg" href="index.php?page=register">
Create Account
</a>

<a class="btn btn-outline-success btn-lg" href="index.php?page=login">
Login
</a>

</div>

<div class="col-lg-4 text-center display-1">
♻️
</div>

</div>
</div>


<?php elseif ($role === 'Resident'): ?>

<div class="card hero-card p-5 mb-4">

<h1 class="fw-bold">
Welcome back, <?= \EcoBin\Services\Security::e($_SESSION['name']) ?>.
</h1>

<p class="lead muted">
Manage your waste collection and recycling activities.
</p>

<div class="d-flex gap-2 flex-wrap">

<a class="btn btn-success btn-lg" href="index.php?page=module2">
Report Waste
</a>

<a class="btn btn-outline-success btn-lg" href="index.php?page=module3">
Recycle & Earn
</a>

<a class="btn btn-outline-secondary btn-lg" href="index.php?page=notifications">
View Notifications
</a>

</div>

</div>


<div class="row g-4">

<div class="col-md-4">
<div class="card p-4 h-100">
<h4>Waste Collection</h4>
<p class="muted">
Submit a geo-tagged waste report, choose a collection date and track the collection process.
</p>
</div>
</div>

<div class="col-md-4">
<div class="card p-4 h-100">
<h4>Recycling Rewards</h4>
<p class="muted">
Submit recyclable materials, book centre appointments and earn reward points.
</p>
</div>
</div>

<div class="col-md-4">
<div class="card p-4 h-100">
<h4>My Account</h4>
<p class="muted">
Update your own profile and view your assigned account role.
</p>
</div>
</div>

</div>


<?php elseif ($role === 'Admin'): ?>

<div class="card hero-card p-5 mb-4">

<span class="badge text-bg-dark mb-3">
Administration Console
</span>

<h1 class="fw-bold">
EcoBin Operations Dashboard
</h1>

<p class="lead muted">
Manage user accounts, collection operations, reports and system administration.
</p>

</div>


<div class="row g-4">

<div class="col-md-3">
<a class="text-decoration-none text-dark" href="index.php?page=users">
<div class="card p-4 h-100">
<h4>User Management</h4>
<p class="muted">
Create accounts, change roles and activate or suspend users.
</p>
</div>
</a>
</div>

<div class="col-md-3">
<a class="text-decoration-none text-dark" href="index.php?page=module2-admin">
<div class="card p-4 h-100">
<h4>Collection Operations</h4>
<p class="muted">
Assign collectors and manage waste collection requests.
</p>
</div>
</a>
</div>

<div class="col-md-3">
<a class="text-decoration-none text-dark" href="index.php?page=module4">
<div class="card p-4 h-100">
<h4>Analytics</h4>
<p class="muted">
Review service performance and generate reports.
</p>
</div>
</a>
</div>

<div class="col-md-3">
<a class="text-decoration-none text-dark" href="index.php?page=module5-admin">
<div class="card p-4 h-100">
<h4>System Administration</h4>
<p class="muted">
Announcements, configuration and audit/activity logs.
</p>
</div>
</a>
</div>

</div>


<?php elseif ($role === 'Collection Staff'): ?>

<div class="card hero-card p-5">

<h1 class="fw-bold">
Collection Staff Workspace
</h1>

<p class="lead muted">
View jobs assigned specifically to you and update collection progress.
</p>

<a class="btn btn-success btn-lg" href="index.php?page=module2-staff">
Open My Collection Jobs
</a>

</div>


<?php elseif ($role === 'Recycling Center Operator'): ?>

<div class="card hero-card p-5">

<h1 class="fw-bold">
Recycling Centre Workspace
</h1>

<p class="lead muted">
Maintain centre information and review recycling submissions and appointments.
</p>

<a class="btn btn-success btn-lg" href="index.php?page=module3-operator">
Open Centre Operations
</a>

</div>

<?php endif; ?>


<?php if (!empty($announcements)): ?>

<div class="mt-5">

<h3 class="section-title">
Latest Announcements
</h3>

<?php foreach ($announcements as $a): ?>

<div class="card p-3 mb-2">

<strong>
<?= \EcoBin\Services\Security::e($a->title) ?>
</strong>

<div>
<?= nl2br(\EcoBin\Services\Security::e($a->message)) ?>
</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>
