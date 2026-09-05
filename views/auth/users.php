<?php
/**
 * Module 1: User Authentication and Role Management
 * @author Chai Ee Yuan
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="eco-heading mb-1">User Management</h2>
        <p class="eco-subheading mb-0">Create and manage EcoBin user accounts and role-based access.</p>
    </div>

    <button class="btn-eco" data-bs-toggle="collapse" data-bs-target="#createUser">
        + Create User
    </button>

</div>


<div id="createUser" class="collapse mb-4">
    <div class="eco-card">

        <h4>Create User Account</h4>

        <form method="post" action="index.php?page=user-create" class="row g-3">

            <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

            <div class="col-md-3">
                <input class="form-control" name="name" placeholder="Full name" required>
            </div>

            <div class="col-md-3">
                <input class="form-control" type="email" name="email" placeholder="Email" required>
            </div>

            <div class="col-md-2">
                <input class="form-control" type="password" name="password" minlength="8" placeholder="Temporary password" required>
            </div>

            <div class="col-md-3">
                <select class="form-select" name="role" required>
                    <option>Resident</option>
                    <option>Collection Staff</option>
                    <option>Recycling Center Operator</option>
                    <option>Admin</option>
                </select>
            </div>

            <div class="col-md-1">
                <button class="btn-eco w-100">Create</button>
            </div>

        </form>

    </div>
</div>


<div class="d-flex gap-2 mb-3">

    <form class="d-flex gap-2 flex-grow-1" method="get">

        <input type="hidden" name="page" value="users">

        <input class="form-control" name="q" value="<?= \EcoBin\Services\Security::e($query ?? '') ?>" placeholder="Search by name or email">

        <select class="form-select" name="role" style="max-width: 220px;">
            <option value="">All roles</option>
            <?php foreach (['Resident', 'Admin', 'Collection Staff', 'Recycling Center Operator'] as $r): ?>
                <option value="<?= $r ?>" <?= ($roleFilter ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
            <?php endforeach; ?>
        </select>

        <button class="btn-eco-outline">Search</button>

    </form>

</div>


<div class="eco-card-sharp">

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>

            <tbody>

            <?php foreach ($users as $u): ?>

                <tr>

                    <td>#<?= $u->id ?></td>
                    <td><?= \EcoBin\Services\Security::e($u->name) ?></td>
                    <td><?= \EcoBin\Services\Security::e($u->email) ?></td>

                    <td>
                        <span class="badge text-bg-light"><?= \EcoBin\Services\Security::e($u->role) ?></span>
                    </td>

                    <td>
                        <?= \EcoBin\Services\View::statusBadge($u->status) ?>
                    </td>

                    <td>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#edit<?= $u->id ?>">
                            Edit
                        </button>
                    </td>

                </tr>


                <tr class="collapse" id="edit<?= $u->id ?>">
                    <td colspan="6">

                        <form method="post" action="index.php?page=user-update" class="row g-2 p-2">

                            <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $u->id ?>">

                            <div class="col-md-4">
                                <input class="form-control" name="name" value="<?= \EcoBin\Services\Security::e($u->name) ?>" required>
                            </div>

                            <div class="col-md-3">
                                <select class="form-select" name="role">
                                    <?php foreach (['Resident', 'Admin', 'Collection Staff', 'Recycling Center Operator'] as $r): ?>
                                        <option <?= $u->role === $r ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option <?= $u->status === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option <?= $u->status === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button class="btn-eco w-100">Save Changes</button>
                            </div>

                        </form>

                    </td>
                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>
</div>