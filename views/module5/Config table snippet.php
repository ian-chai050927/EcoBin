<div class="card p-3 mt-3">
    <h5>Current Configuration</h5>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead><tr><th>Key</th><th>Value</th><th>Updated</th></tr></thead>
            <tbody>
            <?php foreach ($configs as $c): ?>
                <tr>
                    <td><code><?= \EcoBin\Services\Security::e($c->key) ?></code></td>
                    <td><?= \EcoBin\Services\Security::e($c->value) ?></td>
                    <td><?= $c->updatedAt->format('Y-m-d H:i') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>