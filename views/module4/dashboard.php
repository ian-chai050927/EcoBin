<h2>Analytics & Reporting</h2>
<p class="text-muted">Focused on analytics, dashboard presentation and report generation; logging stays under Module 5.</p>
<div class="row g-3" id="stats"><div class="col-12"><div class="alert alert-info">Loading statistics from EcoBin JSON web service...</div></div></div>
<div class="mt-4"><a class="btn btn-success" href="index.php?page=module4-report&period=monthly">Monthly Report</a>
<a class="btn btn-outline-success" href="index.php?page=module4-report&period=annual">Annual Report</a>
<a class="btn btn-outline-primary" href="index.php?page=module4-csv">Export CSV / Excel-compatible</a></div>
<script>
(async()=>{
 const req={requestID:crypto.randomUUID(),timestamp:new Date().toISOString(),service:'dashboard.stats',payload:{}};
 const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json','X-Service-Token':'<?= \EcoBin\Services\Security::e((require __DIR__ . '/../../config/app.php')['service_token']) ?>'},body:JSON.stringify(req)});
 const j=await r.json(); const d=j.data||{};
 document.getElementById('stats').innerHTML=[
 ['Waste Reports',d.waste_reports],['Collection Requests',d.collection_requests],['Collections Completed',d.collection_completed],['Recycling Submissions',d.recycling_submissions],['Recycling Approved',d.recycling_approved]
 ].map(x=>`<div class="col-md"><div class="card p-4"><div class="small-muted">${x[0]}</div><div class="stat">${x[1]??0}</div></div></div>`).join('');
})();
</script>