<?php

$loggedIn =
    !empty(
        $_SESSION[
            'user_id'
        ]
    );

?>


<?php if (
    $loggedIn
): ?>


</div>


<footer
class="
px-4
pb-4
text-muted
small
">


<div
class="
border-top
pt-3
d-flex
justify-content-between
flex-wrap
gap-2
">


<span>

© <?= date('Y') ?>
EcoBin Smart Waste Management

</span>


<span>

Supporting SDG 12:
Responsible Consumption
and Production

</span>


</div>


</footer>


</div>


<?php else: ?>


</div>


<?php endif; ?>



<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>


</body>

</html>