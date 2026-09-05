<h2 class="eco-heading">Notifications</h2>
<p class="eco-subheading">Module 5 owns notification creation/delivery. Other modules only trigger events or call its service.</p>

<?php foreach ($notifications as $n): ?>
    <div class="eco-card mb-3" style="<?= $n->isRead ? 'opacity: 0.65;' : 'border-left: 3px solid var(--eco-primary);' ?>">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <strong><?= \EcoBin\Services\Security::e($n->title) ?></strong>
                <div class="mt-1"><?= \EcoBin\Services\Security::e($n->message) ?></div>
                <small class="eco-subheading" style="margin-bottom: 0;"><?= $n->createdAt->format('Y-m-d H:i') ?></small>
            </div>
            <?php if (!$n->isRead): ?>
                <form method="post" action="index.php?page=notification-read">
                    <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $n->id ?>">
                    <button class="btn-eco-outline">Mark Read</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if (empty($notifications)): ?>
    <div class="eco-card text-center py-5 text-muted">
        <i class="bi bi-bell-slash" style="font-size:2rem;"></i>
        <div class="mt-2">No notifications yet.</div>
    </div>
<?php endif; ?>

<div class="eco-card mt-4">
    <h5>Module 5 → Module 2 Web-Service Status Check</h5>
    <p class="eco-subheading" style="margin-bottom: 12px;">Demonstrates reverse-direction service communication.</p>

    <div class="input-group">
        <input id="collectionId" class="form-control" type="number" min="1" placeholder="Collection request ID">
        <button class="btn-eco" onclick="checkStatus()">Check Status API</button>
    </div>
    <pre id="apiResult" class="mt-3 mb-0"></pre>
</div>

<?php

$serviceToken = \EcoBin\Services\Security::e($app['service_token'] ?? '');

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script  = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$apiUrl  = $scheme . '://' . $host . rtrim(dirname($script), '/\\') . '/api.php';
?>

<div id="api-config"
     data-token="<?= $serviceToken ?>"
     data-url="<?= \EcoBin\Services\Security::e($apiUrl) ?>"
     data-uid="<?= (int)($_SESSION['user_id'] ?? 0) ?>"
     style="display:none;"></div>

<script>
    function generateRequestId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    async function checkStatus() {
        const cfg     = document.getElementById('api-config');
        const token   = cfg.dataset.token;
        const apiUrl  = cfg.dataset.url;
        const id      = document.getElementById('collectionId').value;
        const result  = document.getElementById('apiResult');

        if (!id || isNaN(Number(id)) || Number(id) < 1) {
            result.textContent = 'Please enter a valid collection request ID.';
            return;
        }

        result.textContent = 'Calling API…';

        const req = {
            requestID : generateRequestId(),
            timestamp : new Date().toISOString(),
            service   : 'collection.status',
            payload   : {
                collection_id: Number(id),
                user_id      : Number(cfg.dataset.uid)  // IDOR check: server verifies ownership
            }
        };

        try {
            const r    = await fetch(apiUrl, {
                method  : 'POST',
                headers : {
                    'Content-Type'  : 'application/json',
                    'X-Service-Token': token
                },
                body: JSON.stringify(req)
            });

            // Guard against non-JSON responses (PHP errors, 404, etc.)
            const text = await r.text();
            try {
                const json = JSON.parse(text);
                result.textContent = JSON.stringify(json, null, 2);
            } catch (_) {
                result.textContent = 'API returned non-JSON response:\n' + text;
            }
        } catch (err) {
            result.textContent = 'Fetch error: ' + err.message;
        }
    }
</script>
