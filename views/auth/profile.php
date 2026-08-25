<div class="row g-4">

<div class="col-lg-8">

<div class="card p-4">

<h2 class="section-title">
My Account
</h2>

<p class="muted">
Manage your personal EcoBin profile.
</p>

<form method="post">

<input
type="hidden"
name="csrf_token"
value="<?= \EcoBin\Services\Security::csrfToken() ?>">


<label class="form-label">
Full Name
</label>

<input
class="form-control mb-3"
name="name"
value="<?= \EcoBin\Services\Security::e($user->name) ?>"
required>


<label class="form-label">
Email Address
</label>

<input
class="form-control mb-3"
value="<?= \EcoBin\Services\Security::e($user->email) ?>"
disabled>


<label class="form-label">
Account Role
</label>

<input
class="form-control mb-3"
value="<?= \EcoBin\Services\Security::e($user->role) ?>"
disabled>


<label class="form-label">
Account Status
</label>

<input
class="form-control mb-3"
value="<?= \EcoBin\Services\Security::e($user->status) ?>"
disabled>


<button class="btn btn-success">
Update Profile
</button>

</form>

</div>
</div>


<div class="col-lg-4">

<div class="card p-4">

<h4>
Your Access
</h4>

<p class="muted">
Available functions are determined by your role.
</p>


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
