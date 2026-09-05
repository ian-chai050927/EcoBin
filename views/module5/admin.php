<h2 class="eco-heading">System Administration</h2>
<p class="eco-subheading">
    No user CRUD/status duplication. Module 1 owns accounts; Module 5 focuses on
    notifications, announcements, configuration and logs.
</p>

<div class="row g-4">

    <!-- Create Announcement -->
    <div class="col-lg-6">
        <div class="eco-card">
            <h4>Create Announcement</h4>

            <form method="post" action="index.php?page=module5-announcement">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

                <input class="form-control mb-2" name="title" maxlength="150" placeholder="Title" required>

                <textarea class="form-control mb-2" name="message" maxlength="4000" placeholder="Announcement" required></textarea>

                <button class="btn-eco">Publish</button>
            </form>
        </div>
    </div>

    <!-- System Configuration -->
    <div class="col-lg-6">
        <div class="eco-card">
            <h4>System Configuration</h4>

            <form method="post" action="index.php?page=module5-config">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">

                <label class="form-label">Setting</label>
                <select class="form-select mb-2" name="key" required>
                    <option value="">Choose a setting</option>
                    <option value="collection.max_daily">collection.max_daily — Max collections scheduled per day</option>
                    <option value="recycling.points_per_kg">recycling.points_per_kg — Default reward rate (fallback)</option>
                    <option value="reminder.collection_days_ahead">reminder.collection_days_ahead — Days ahead to send collection reminders</option>
                    <option value="reminder.appointment_hours_ahead">reminder.appointment_hours_ahead — Hours ahead to send appointment reminders</option>
                </select>

                <label class="form-label">Value</label>
                <input class="form-control mb-2" name="value" placeholder="e.g. 1" required>

                <button class="btn-eco">Save Config</button>
            </form>

            <div class="mt-3">
                <h5 class="mb-2" style="font-size: 15px;">Current Configuration</h5>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Updated</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($configs as $c): ?>
                            <tr>
                                <td><code><?= \EcoBin\Services\Security::e($c->key) ?></code></td>
                                <td><?= \EcoBin\Services\Security::e($c->value) ?></td>
                                <td class="eco-subheading" style="margin-bottom: 0;"><?= $c->updatedAt->format('Y-m-d H:i') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>

<div class="row g-4 mt-1">

    <!-- Audit Logs -->
    <div class="col-lg-6">
        <div class="eco-card">
            <h4>Audit Logs</h4>

            <div class="table-responsive" style="max-height:430px">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($audits as $a): ?>
                        <tr>
                            <td class="eco-subheading" style="margin-bottom: 0;"><?= $a->createdAt->format('m-d H:i') ?></td>
                            <td><?= $a->user?->id ?? '-' ?></td>
                            <td><?= \EcoBin\Services\Security::e($a->action) ?></td>
                            <td><?= \EcoBin\Services\Security::e($a->entity) ?> #<?= $a->entityId ?? '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="col-lg-6">
        <div class="eco-card">
            <h4>Activity Logs</h4>

            <div style="max-height:430px;overflow:auto">
                <?php foreach ($activities as $a): ?>
                    <div class="border-bottom py-2">
                        <span class="eco-subheading" style="margin-bottom: 0;"><?= $a->createdAt->format('Y-m-d H:i:s') ?></span>
                        · User <?= $a->user?->id ?? '-' ?>
                        · <?= \EcoBin\Services\Security::e($a->activity) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>