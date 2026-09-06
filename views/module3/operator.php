<div class="mb-4">
    <h2 class="eco-heading mb-1">Recycling Centre Operations</h2>
    <p class="eco-subheading mb-0">Manage centre availability, review incoming resident recycling submissions, and process drop-off appointments.</p>
</div>

<!-- Operator KPI Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Managed Centres', (int)$managedCentersCount, 'bi-buildings') ?>
    </div>
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Pending Submissions', (int)$pendingSubsCount, 'bi-inbox', $pendingSubsCount > 0 ? 'text-warning' : '') ?>
    </div>
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Pending Appointments', (int)$pendingApptsCount, 'bi-calendar-event', $pendingApptsCount > 0 ? 'text-primary' : '') ?>
    </div>
    <div class="col-sm-6 col-xl-3">
        <?= \EcoBin\Services\View::statCard('Recycled Total', number_format((float)$totalRecycledWeight, 2) . ' kg', 'bi-recycle', 'text-success') ?>
    </div>
</div>

<!-- My Managed Centres Table -->
<div class="eco-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0"><i class="bi bi-geo-alt me-1 text-success"></i> My Managed Recycling Centres</h4>
        <button class="btn btn-sm btn-outline-success" onclick="resetCenterForm()">
            <i class="bi bi-plus-circle me-1"></i> Add New Centre
        </button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Centre Name</th>
                    <th>Address</th>
                    <th>Accepted Materials</th>
                    <th>Operating Hours</th>
                    <th>Status</th>
                    <th>Quick Status &amp; Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($centers as $c): ?>
                <tr>
                    <td>#<?= $c->id ?></td>
                    <td><strong><?= \EcoBin\Services\Security::e($c->name) ?></strong></td>
                    <td class="small text-muted"><?= \EcoBin\Services\Security::e($c->address) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= \EcoBin\Services\Security::e($c->acceptedMaterials) ?></span></td>
                    <td class="small"><?= \EcoBin\Services\Security::e($c->operatingHours ?? 'N/A') ?></td>
                    <td><?= \EcoBin\Services\View::statusBadge($c->availability) ?></td>
                    <td>
                        <div class="d-inline-flex gap-1 flex-wrap align-items-center">
                            <!-- 1-Click Availability Toggle Buttons -->
                            <form method="post" action="index.php?page=module3-center-status" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                                <input type="hidden" name="center_id" value="<?= $c->id ?>">
                                <?php if ($c->availability !== 'Open'): ?>
                                    <button class="btn btn-sm btn-outline-success" name="availability" value="Open" title="Set Open">Open</button>
                                <?php endif; ?>
                                <?php if ($c->availability !== 'Full'): ?>
                                    <button class="btn btn-sm btn-outline-warning" name="availability" value="Full" title="Set Full">Full</button>
                                <?php endif; ?>
                                <?php if ($c->availability !== 'Closed'): ?>
                                    <button class="btn btn-sm btn-outline-danger" name="availability" value="Closed" title="Set Closed">Close</button>
                                <?php endif; ?>
                            </form>
                            <!-- Edit Button -->
                            <button class="btn btn-sm btn-primary" onclick='editCenter(<?= json_encode([
                                "id" => $c->id,
                                "name" => $c->name,
                                "address" => $c->address,
                                "acceptedMaterials" => $c->acceptedMaterials,
                                "operatingHours" => $c->operatingHours,
                                "availability" => $c->availability,
                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($centers)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">No centres managed yet. Use the form below to register your first centre.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Update Recycling Centre Form -->
<div class="eco-card mb-4" id="center-form-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 id="form-title" class="mb-0"><i class="bi bi-plus-circle me-1 text-success"></i> Add New Recycling Centre</h4>
        <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btn-cancel-edit" onclick="resetCenterForm()">
            <i class="bi bi-x-circle me-1"></i> Cancel Edit
        </button>
    </div>
    <p class="eco-subheading small mb-3">Provide public location details, accepted waste categories, and working hours.</p>

    <form method="post" action="index.php?page=module3-center-save" id="center-form">
        <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
        <input type="hidden" name="id" id="center_id" value="">

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Centre Name</label>
                <input class="form-control" name="name" id="center_name" placeholder="e.g. EcoBin Hub Central" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Address / Location</label>
                <input class="form-control" name="address" id="center_address" placeholder="e.g. Lot 12, Green Avenue, Subang" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Accepted Materials</label>
                <select class="form-select" name="accepted_materials" id="center_materials" required>
                    <option value="">-- Select Accepted Materials --</option>
                    <?php foreach ($acceptedOptions as $optKey => $optLabel): ?>
                        <option value="<?= \EcoBin\Services\Security::e($optKey) ?>">
                            <?= \EcoBin\Services\Security::e($optLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Operating Hours</label>
                <input class="form-control" name="operating_hours" id="center_hours" placeholder="e.g. Mon - Fri: 9:00 AM - 5:00 PM" value="Mon - Fri: 9:00 AM - 5:00 PM">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Availability Status</label>
                <select class="form-select" name="availability" id="center_availability">
                    <option value="Open">Open</option>
                    <option value="Full">Full</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn-eco w-100" id="btn-save-center">Save Centre</button>
            </div>
        </div>
    </form>
</div>

<!-- Reviews Section (Submissions and Appointments) -->
<div class="row g-4">

    <!-- Review Submissions -->
    <div class="col-12">
        <div class="eco-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="bi bi-inbox me-1 text-success"></i> Review Recycling Submissions</h4>
                <span class="badge bg-light text-dark border">Pending: <strong><?= (int)$pendingSubsCount ?></strong></span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resident</th>
                        <th>Material</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($subs as $s): ?>
                        <tr>
                            <td>#<?= $s->id ?></td>
                            <td>
                                <strong><?= \EcoBin\Services\Security::e($s->resident->name ?? 'Resident') ?></strong>
                                <div class="small text-muted"><?= \EcoBin\Services\Security::e($s->resident->email ?? '') ?></div>
                            </td>
                            <td><?= \EcoBin\Services\Security::e($s->material) ?></td>
                            <td><strong><?= $s->weightKg ?></strong> kg</td>
                            <td><?= \EcoBin\Services\View::statusBadge($s->status) ?></td>
                            <td>
                                <?= $s->points > 0 ? '<span class="text-success fw-bold">+' . $s->points . ' pts</span>' : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td class="small text-muted"><?= $s->createdAt ? $s->createdAt->format('d M Y, h:i A') : 'N/A' ?></td>
                            <td>
                                <?php if ($s->status === 'Pending'): ?>
                                    <form method="post" action="index.php?page=module3-review-submission" class="d-inline-flex gap-1">
                                        <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                                        <input type="hidden" name="submission_id" value="<?= $s->id ?>">
                                        <button class="btn btn-sm btn-success" name="status" value="Approved">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" name="status" value="Rejected">
                                            <i class="bi bi-x-lg me-1"></i> Reject
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Reviewed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($subs)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">No recycling submissions found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Review Appointments -->
    <div class="col-12">
        <div class="eco-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="bi bi-calendar-check me-1 text-success"></i> Review Appointments</h4>
                <span class="badge bg-light text-dark border">Pending: <strong><?= (int)$pendingApptsCount ?></strong></span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resident</th>
                        <th>Centre</th>
                        <th>Date &amp; Time</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($appts as $a): ?>
                        <tr>
                            <td>#<?= $a->id ?></td>
                            <td>
                                <strong><?= \EcoBin\Services\Security::e($a->resident->name ?? 'Resident') ?></strong>
                                <div class="small text-muted"><?= \EcoBin\Services\Security::e($a->resident->email ?? '') ?></div>
                            </td>
                            <td><?= \EcoBin\Services\Security::e($a->center->name ?? 'Centre') ?></td>
                            <td><strong><?= $a->appointmentAt ? $a->appointmentAt->format('d M Y, h:i A') : 'N/A' ?></strong></td>
                            <td><?= \EcoBin\Services\View::statusBadge($a->status) ?></td>
                            <td class="small text-muted"><?= $a->createdAt ? $a->createdAt->format('d M Y, h:i A') : 'N/A' ?></td>
                            <td>
                                <?php if ($a->status === 'Pending'): ?>
                                    <form method="post" action="index.php?page=module3-review-appointment" class="d-inline-flex gap-1">
                                        <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                                        <input type="hidden" name="appointment_id" value="<?= $a->id ?>">
                                        <button class="btn btn-sm btn-success" name="status" value="Confirmed">
                                            <i class="bi bi-check me-1"></i> Confirm
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" name="status" value="Cancelled">
                                            <i class="bi bi-x me-1"></i> Cancel
                                        </button>
                                    </form>
                                <?php elseif ($a->status === 'Confirmed'): ?>
                                    <form method="post" action="index.php?page=module3-review-appointment" class="d-inline-flex gap-1">
                                        <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                                        <input type="hidden" name="appointment_id" value="<?= $a->id ?>">
                                        <button class="btn btn-sm btn-primary" name="status" value="Completed">
                                            <i class="bi bi-check2-all me-1"></i> Complete
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" name="status" value="Cancelled">
                                            <i class="bi bi-x me-1"></i> Cancel
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">No actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($appts)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No appointments found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function editCenter(c) {
    document.getElementById('center_id').value = c.id;
    document.getElementById('center_name').value = c.name;
    document.getElementById('center_address').value = c.address;
    
    const matSelect = document.getElementById('center_materials');
    matSelect.value = c.acceptedMaterials;
    // If existing value is not in standard options, dynamically add it so it remains selected
    if (matSelect.selectedIndex === -1 && c.acceptedMaterials) {
        const customOpt = document.createElement('option');
        customOpt.value = c.acceptedMaterials;
        customOpt.textContent = c.acceptedMaterials;
        matSelect.appendChild(customOpt);
        matSelect.value = c.acceptedMaterials;
    }

    document.getElementById('center_hours').value = c.operatingHours || 'Mon - Fri: 9:00 AM - 5:00 PM';
    document.getElementById('center_availability').value = c.availability;

    document.getElementById('form-title').innerHTML = '<i class="bi bi-pencil-square me-1 text-primary"></i> Edit Recycling Centre #' + c.id;
    document.getElementById('btn-save-center').textContent = 'Update Centre Information';
    document.getElementById('btn-cancel-edit').classList.remove('d-none');

    document.getElementById('center-form-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetCenterForm() {
    document.getElementById('center_id').value = '';
    document.getElementById('center-form').reset();
    document.getElementById('center_materials').value = '';
    document.getElementById('center_hours').value = 'Mon - Fri: 9:00 AM - 5:00 PM';
    document.getElementById('center_availability').value = 'Open';

    document.getElementById('form-title').innerHTML = '<i class="bi bi-plus-circle me-1 text-success"></i> Add New Recycling Centre';
    document.getElementById('btn-save-center').textContent = 'Save Centre';
    document.getElementById('btn-cancel-edit').classList.add('d-none');
}
</script>