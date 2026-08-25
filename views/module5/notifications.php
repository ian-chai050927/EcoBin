<h2>Notifications</h2>
<p class="text-muted">Module 5 owns notification creation/delivery. Other modules only trigger events or call its service.</p>
<?php foreach($notifications as $n): ?><div class="card p-3 mb-2 <?= $n->isRead?'opacity-75':'' ?>">
<div class="d-flex justify-content-between"><div><strong><?= \EcoBin\Services\Security::e($n->title) ?></strong><div><?= \EcoBin\Services\Security::e($n->message) ?></div><small><?= $n->createdAt->format('Y-m-d H:i') ?></small></div>
<?php if(!$n->isRead): ?><form method="post" action="index.php?page=notification-read"><input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>"><input type="hidden" name="id" value="<?= $n->id ?>"><button class="btn btn-sm btn-outline-success">Mark Read</button></form><?php endif; ?></div></div><?php endforeach; ?>

<div class="card p-4 mt-4"><h5>Module 5 → Module 2 Web-Service Status Check</h5><p class="small-muted">Demonstrates reverse-direction service communication.</p>
<div class="input-group"><input id="collectionId" class="form-control" type="number" min="1" placeholder="Collection request ID"><button class="btn btn-primary" onclick="checkStatus()">Check Status API</button></div><pre id="apiResult" class="mt-3 mb-0"></pre></div>
<script>
async function checkStatus(){
 const id=document.getElementById('collectionId').value;
 const req={requestID:crypto.randomUUID(),timestamp:new Date().toISOString(),service:'collection.status',payload:{collection_id:Number(id)}};
 const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json','X-Service-Token':'<?= \EcoBin\Services\Security::e((require __DIR__ . '/../../config/app.php')['service_token']) ?>'},body:JSON.stringify(req)});
 document.getElementById('apiResult').textContent=JSON.stringify(await r.json(),null,2);
}
</script>