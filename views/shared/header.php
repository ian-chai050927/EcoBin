<?php

use EcoBin\Services\Security;

$role =
    $_SESSION['role']
    ?? null;

$currentPage =
    $_GET['page']
    ?? 'home';


function navActive(
    string $page
): string {

    global $currentPage;

    return
        $currentPage === $page
        ? 'active'
        : '';

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,
initial-scale=1.0">

<title>
<?= Security::e(
    $title ?? 'EcoBin'
) ?>
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<link
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">


<link
rel="stylesheet"
href="assets/css/ecobin.css">

</head>


<body>


<?php if (
    !empty(
        $_SESSION['user_id']
    )
): ?>


<!-- =================================
     SIDEBAR
================================== -->

<aside class="eco-sidebar">


<a
href="index.php"
class="eco-logo">


<div class="eco-logo-icon">

<i class="bi bi-recycle"></i>

</div>


<div>

<div class="eco-logo-text">

EcoBin

</div>


<div
style="
font-size:11px;
color:#8fa99d;
">

SMART WASTE MANAGEMENT

</div>

</div>


</a>



<?php if (
    $role === 'Resident'
): ?>


<div class="eco-sidebar-label">

Resident Services

</div>


<a
href="index.php"
class="eco-nav-link
<?= navActive('home') ?>">

<i class="bi bi-grid"></i>

Home

</a>


<a
href="index.php?page=module2"
class="eco-nav-link
<?= navActive('module2') ?>">

<i class="bi bi-trash3"></i>

Report Waste

</a>


<a
href="index.php?page=my-collections"
class="eco-nav-link
<?= navActive('my-collections') ?>">

<i class="bi bi-truck"></i>

My Collections

</a>


<a
href="index.php?page=module3"
class="eco-nav-link
<?= navActive('module3') ?>">

<i class="bi bi-recycle"></i>

Recycling & Rewards

</a>


<a
href="index.php?page=notifications"
class="eco-nav-link
<?= navActive('notifications') ?>">

<i class="bi bi-bell"></i>

Notifications

</a>



<?php elseif (
    $role === 'Admin'
): ?>


<div class="eco-sidebar-label">

Administration

</div>


<a
href="index.php"
class="eco-nav-link">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>


<a
href="index.php?page=users"
class="eco-nav-link
<?= navActive('users') ?>">

<i class="bi bi-people"></i>

User Management

</a>


<a
href="index.php?page=module2-admin"
class="eco-nav-link
<?= navActive(
    'module2-admin'
) ?>">

<i class="bi bi-truck"></i>

Collection Operations

</a>


<a
href="index.php?page=module4"
class="eco-nav-link
<?= navActive(
    'module4'
) ?>">

<i class="bi bi-bar-chart"></i>

Analytics & Reports

</a>


<a
href="index.php?page=module5-admin"
class="eco-nav-link
<?= navActive(
    'module5-admin'
) ?>">

<i class="bi bi-gear"></i>

System Administration

</a>



<?php elseif (
    $role ===
    'Collection Staff'
): ?>


<div class="eco-sidebar-label">

Collection Services

</div>


<a
href="index.php"
class="eco-nav-link">

<i class="bi bi-house"></i>

Home

</a>


<a
href="index.php?page=module2-staff"
class="eco-nav-link
<?= navActive(
    'module2-staff'
) ?>">

<i class="bi bi-truck-front"></i>

My Collection Jobs

</a>


<a
href="index.php?page=notifications"
class="eco-nav-link">

<i class="bi bi-bell"></i>

Notifications

</a>



<?php elseif (
    $role ===
    'Recycling Center Operator'
): ?>


<div class="eco-sidebar-label">

Centre Operations

</div>


<a
href="index.php"
class="eco-nav-link">

<i class="bi bi-house"></i>

Home

</a>


<a
href="index.php?page=module3-operator"
class="eco-nav-link
<?= navActive(
    'module3-operator'
) ?>">

<i class="bi bi-building"></i>

Centre Operations

</a>


<a
href="index.php?page=notifications"
class="eco-nav-link">

<i class="bi bi-bell"></i>

Notifications

</a>


<?php endif; ?>



<div class="eco-sidebar-label">

Account

</div>


<a
href="index.php?page=profile"
class="eco-nav-link
<?= navActive(
    'profile'
) ?>">

<i class="bi bi-person"></i>

My Account

</a>


<a
href="index.php?page=logout"
class="eco-nav-link">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>


</aside>



<!-- =================================
     MAIN
================================== -->

<div class="eco-main">


<header class="eco-topbar">


<div>


<h1 class="eco-page-title">

<?= Security::e(
    $title
    ?? 'EcoBin'
) ?>

</h1>


</div>



<div class="eco-user-area">


<a
href="index.php?page=notifications"
class="
btn
btn-light
position-relative
">

<i class="bi bi-bell"></i>

</a>



<div class="eco-avatar">

<?= strtoupper(
    substr(
        $_SESSION['name']
        ?? 'U',
        0,
        1
    )
) ?>

</div>



<div class="eco-user-info">


<div class="eco-user-name">

<?= Security::e(
    $_SESSION['name']
    ?? ''
) ?>

</div>


<div class="eco-user-role">

<?= Security::e(
    $role
    ?? ''
) ?>

</div>


</div>


</div>


</header>



<div class="eco-content">


<?php else: ?>


<div class="container py-5">


<?php endif; ?>



<?php

if (
    $message =
        Security::flash(
            'success'
        )
):

?>


<div
class="
alert
alert-success
border-0
shadow-sm
">

<i
class="
bi
bi-check-circle
me-2
"></i>

<?= Security::e(
    $message
) ?>

</div>


<?php endif; ?>



<?php

if (
    $message =
        Security::flash(
            'error'
        )
):

?>


<div
class="
alert
alert-danger
border-0
shadow-sm
">

<i
class="
bi
bi-exclamation-circle
me-2
"></i>

<?= Security::e(
    $message
) ?>

</div>


<?php endif; ?>