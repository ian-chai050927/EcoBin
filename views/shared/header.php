<?php
use EcoBin\Services\Security;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= Security::e($title ?? 'EcoBin') ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
body{background:#f5f7f6}
.navbar-brand{font-weight:800}
.card{border:0;border-radius:18px;box-shadow:0 8px 28px rgba(0,0,0,.06)}
.hero-card{background:linear-gradient(135deg,#ffffff,#eef8f2)}
.stat{font-size:2rem;font-weight:800}
.muted{color:#6c757d}
.section-title{font-weight:800}
#map{height:330px;border-radius:16px}
</style>
</head>

<body>

<nav class="navbar navbar-expand-xl navbar-dark bg-success">
<div class="container-fluid px-4">

<a class="navbar-brand" href="index.php">♻ EcoBin</a>

<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
<span class="navbar-toggler-icon"></span>
</button>

<div id="nav" class="collapse navbar-collapse">

<ul class="navbar-nav me-auto">

<?php if (!empty($_SESSION['user_id'])): ?>

<?php if (($_SESSION['role'] ?? '') === 'Resident'): ?>

<li class="nav-item">
<a class="nav-link" href="index.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=module2">Waste Collection</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=module3">Recycling & Rewards</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=notifications">Notifications</a>
</li>


<?php elseif (($_SESSION['role'] ?? '') === 'Admin'): ?>

<li class="nav-item">
<a class="nav-link" href="index.php">Admin Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=users">User Management</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=module2-admin">Collection Operations</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=module4">Analytics & Reports</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=module5-admin">System Administration</a>
</li>


<?php elseif (($_SESSION['role'] ?? '') === 'Collection Staff'): ?>

<li class="nav-item">
<a class="nav-link" href="index.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=module2-staff">My Collection Jobs</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=notifications">Notifications</a>
</li>


<?php elseif (($_SESSION['role'] ?? '') === 'Recycling Center Operator'): ?>

<li class="nav-item">
<a class="nav-link" href="index.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=module3-operator">Centre Operations</a>
</li>

<li class="nav-item">
<a class="nav-link" href="index.php?page=notifications">Notifications</a>
</li>

<?php endif; ?>

<?php endif; ?>

</ul>


<div class="d-flex align-items-center gap-2 text-white">

<?php if (!empty($_SESSION['user_id'])): ?>

<span>
<?= Security::e($_SESSION['name'] ?? '') ?>
·
<?= Security::e($_SESSION['role'] ?? '') ?>
</span>

<a class="btn btn-sm btn-light" href="index.php?page=profile">
My Account
</a>

<a class="btn btn-sm btn-outline-light" href="index.php?page=logout">
Logout
</a>

<?php else: ?>

<a class="btn btn-sm btn-light" href="index.php?page=login">
Login
</a>

<a class="btn btn-sm btn-outline-light" href="index.php?page=register">
Register
</a>

<?php endif; ?>

</div>

</div>
</div>
</nav>


<main class="container py-4">

<?php if ($m = Security::flash('success')): ?>

<div class="alert alert-success">
<?= Security::e($m) ?>
</div>

<?php endif; ?>


<?php if ($m = Security::flash('error')): ?>

<div class="alert alert-danger">
<?= Security::e($m) ?>
</div>

<?php endif; ?>
