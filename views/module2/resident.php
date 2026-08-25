<h2>Waste Collection Service</h2>
<p class="text-muted">Submit waste reports, request collection and track collection history/status.</p>
<div class="card p-4 mb-4"><h4>Submit Waste Report</h4>
<form method="post" action="index.php?page=module2-submit" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category" required>
<option value="">Select</option><option>General Waste</option><option>Plastic</option><option>Electronic Waste</option><option>Bulky Waste</option><option>Organic Waste</option><option>Hazardous Waste</option></select></div>
<div class="col-md-6"><label class="form-label">Preferred Collection Date</label><input class="form-control" type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" required></div>
<div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" maxlength="1500" required></textarea></div>
<div class="col-md-6"><label class="form-label">Waste Image</label><input class="form-control" type="file" name="waste_image" accept="image/jpeg,image/png,image/webp"></div>
<div class="col-md-6"><label class="form-label">Address</label><input class="form-control" id="address" name="address" maxlength="500" required></div>
<div class="col-12"><div id="map"></div></div>
<input type="hidden" id="latitude" name="latitude"><input type="hidden" id="longitude" name="longitude">
<div class="col-12"><button class="btn btn-success">Submit Report & Collection Request</button></div>
</div></form></div>

<h4>Collection History</h4>
<div class="card p-3"><div class="table-responsive"><table class="table">
<thead><tr><th>ID</th><th>Report</th><th>Preferred</th><th>Scheduled</th><th>Status</th><th>Remarks</th></tr></thead>
<tbody><?php foreach($collections as $c): ?><tr>
<td><?= $c->id ?></td><td>#<?= $c->wasteReportId ?></td><td><?= $c->preferredDate->format('Y-m-d') ?></td>
<td><?= $c->scheduledDate?->format('Y-m-d') ?? '-' ?></td><td><span class="badge text-bg-secondary"><?= \EcoBin\Services\Security::e($c->status) ?></span></td>
<td><?= \EcoBin\Services\Security::e($c->remarks ?? '') ?></td></tr><?php endforeach; ?></tbody></table></div></div>

<script>
const map=L.map('map').setView([3.1390,101.6869],11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(map);
let marker;
async function setLoc(lat,lng){
 document.getElementById('latitude').value=lat; document.getElementById('longitude').value=lng;
 if(marker) marker.setLatLng([lat,lng]); else marker=L.marker([lat,lng],{draggable:true}).addTo(map);
 marker.off('dragend').on('dragend',e=>{const p=e.target.getLatLng();setLoc(p.lat,p.lng)});
 map.setView([lat,lng],16);
 try{const r=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`);
 const j=await r.json(); document.getElementById('address').value=j.display_name||`${lat}, ${lng}`;}catch(e){}
}
map.on('click',e=>setLoc(e.latlng.lat,e.latlng.lng));
if(navigator.geolocation){navigator.geolocation.getCurrentPosition(p=>setLoc(p.coords.latitude,p.coords.longitude),()=>{});}
</script>