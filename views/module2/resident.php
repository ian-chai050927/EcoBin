<?php

use EcoBin\Services\Security;


/*
|--------------------------------------------------------------------------
| Resident Module 2 View Helpers
|--------------------------------------------------------------------------
*/

function collectionStatusClass(string $status): string
{
    return match ($status) {
        'Pending'     => 'eco-status-pending',
        'Assigned'    => 'eco-status-assigned',
        'In Progress' => 'eco-status-progress',
        'Completed'   => 'eco-status-completed',
        'Cancelled'   => 'eco-status-cancelled',
        default       => 'eco-status-cancelled'
    };
}

function collectionProgress(string $status): int
{
    return match ($status) {
        'Pending'     => 25,
        'Assigned'    => 50,
        'In Progress' => 75,
        'Completed'   => 100,
        default       => 0
    };
}


/*
|--------------------------------------------------------------------------
| Search / Filter
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$filteredCollections = array_filter(
        $collections,
        function ($collection) use ($search, $statusFilter, $reports) {

            if ($statusFilter !== '' && $collection->status !== $statusFilter) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            $report = null;
            foreach ($reports as $r) {
                if ($r->id === $collection->wasteReportId) {
                    $report = $r;
                    break;
                }
            }

            $haystack = strtolower(
                    $collection->id . ' '
                    . ($report->category ?? '') . ' '
                    . ($report->address ?? '') . ' '
                    . ($report->description ?? '') . ' '
                    . $collection->status
            );

            return str_contains($haystack, strtolower($search));
        }
);

?>


<!-- =========================================================
     PAGE HEADING
========================================================= -->

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

    <div>
        <h2 class="eco-heading mb-1">Waste Collection</h2>
        <p class="eco-subheading mb-0">
            Report waste issues, request collection services and track your collection progress.
        </p>
    </div>

    <a href="#collectionHistory" class="btn btn-eco-outline">
        <i class="bi bi-clock-history me-1"></i> View My Collections
    </a>

</div>


<!-- =========================================================
     SERVICE SUMMARY
========================================================= -->

<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="eco-stat-card">
            <div class="eco-stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="eco-stat-label">Total Requests</div>
            <div class="eco-stat-number"><?= count($collections) ?></div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- This is the number worth watching day-to-day, so it gets a
             visual accent the other two (record-keeping) numbers don't. -->
        <div class="eco-stat-card eco-stat-card-highlight">
            <div class="eco-stat-icon"><i class="bi bi-truck"></i></div>
            <div class="eco-stat-label">Active Collections</div>
            <div class="eco-stat-number">
                <?php
                echo count(array_filter(
                        $collections,
                        fn($c) => in_array($c->status, ['Pending', 'Assigned', 'In Progress'], true)
                ));
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="eco-stat-card">
            <div class="eco-stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="eco-stat-label">Completed</div>
            <div class="eco-stat-number">
                <?php
                echo count(array_filter(
                        $collections,
                        fn($c) => $c->status === 'Completed'
                ));
                ?>
            </div>
        </div>
    </div>

</div>


<!-- =========================================================
     REPORT WASTE FORM
========================================================= -->

<div class="row g-4 mb-5">

    <div class="col-xl-8">
        <div class="eco-card">

            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="eco-stat-icon mb-0"><i class="bi bi-trash3"></i></div>
                <div>
                    <h4 class="mb-1">Submit Waste Report</h4>
                    <div class="text-muted">
                        Provide enough information for EcoBin to arrange an appropriate collection.
                    </div>
                </div>
            </div>

            <form method="post" action="index.php?page=module2-submit" enctype="multipart/form-data">

                <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

                <div class="row g-4">

                    <!-- CATEGORY -->
                    <div class="col-md-6">
                        <label class="form-label">Waste Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                            <option value="">Select category</option>
                            <option>General Waste</option>
                            <option>Plastic</option>
                            <option>Electronic Waste</option>
                            <option>Bulky Waste</option>
                            <option>Organic Waste</option>
                            <option>Hazardous Waste</option>
                        </select>
                    </div>

                    <!-- PREFERRED DATE -->
                    <div class="col-md-6">
                        <label class="form-label">Preferred Collection Date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- WASTE SIZE -->
                    <div class="col-md-6">
                        <label class="form-label">Estimated Waste Size</label>
                        <select class="form-select" name="waste_size">
                            <option value="Small">Small</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Large">Large</option>
                            <option value="Extra Large">Extra Large</option>
                        </select>
                        <div class="form-text">Helps collection staff prepare suitable equipment.</div>
                    </div>

                    <!-- PRIORITY -->
                    <div class="col-md-6">
                        <label class="form-label">Priority Level</label>
                        <select class="form-select" name="priority">
                            <option value="Low">Low</option>
                            <option value="Normal" selected>Normal</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                        <div class="form-text">Use High/Urgent only when collection genuinely requires faster attention.</div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="col-12">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="4" maxlength="1500"
                                  placeholder="Example: Two damaged chairs and one broken table located beside the apartment refuse area."
                                  required></textarea>
                        <div class="form-text d-flex justify-content-between">
                            <span>Describe the waste and accessibility clearly.</span>
                            <span>Maximum 1500 characters</span>
                        </div>
                    </div>

                    <!-- MULTIPLE IMAGES -->
                    <div class="col-12">
                        <label class="form-label">Waste Images</label>
                        <input class="form-control" type="file" id="wasteImages" name="waste_images[]"
                               accept="image/jpeg, image/png, image/webp" multiple>
                        <div class="form-text">Upload up to 5 JPG, PNG or WEBP images. Maximum 5MB per image.</div>
                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                    </div>

                    <!-- ADDRESS -->
                    <div class="col-12">
                        <label class="form-label">Collection Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                            <input class="form-control" id="address" name="address" maxlength="500"
                                   placeholder="Select your location on the map or enter the address" required>
                            <button class="btn btn-outline-success" type="button" id="currentLocationButton">
                                <i class="bi bi-crosshair"></i> Current Location
                            </button>
                        </div>
                    </div>

                    <!-- MAP -->
                    <div class="col-12">
                        <label class="form-label">Select Location</label>
                        <div id="map"></div>
                        <div class="form-text mt-2">Click the map or drag the marker to adjust the collection point.</div>
                    </div>

                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                    <!-- SUBMIT -->
                    <div class="col-12">

                        <!-- Swapped from a boxed Bootstrap alert-light to the same
                             quiet tinted-note style used for "Assigned" collections
                             elsewhere in the module — consistent tone, not a
                             generic gray alert box. -->
                        <div class="eco-info-note mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Please make sure the waste is accessible on the selected collection date.
                        </div>

                        <button type="submit" class="btn btn-eco btn-lg">
                            <i class="bi bi-send me-1"></i> Submit Collection Request
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>


    <!-- =====================================================
         HOW IT WORKS
    ====================================================== -->

    <div class="col-xl-4">

        <div class="eco-card mb-4">

            <h4 class="mb-4">How Collection Works</h4>

            <div class="d-flex gap-3 mb-4">
                <div class="eco-stat-icon mb-0"><i class="bi bi-file-earmark-plus"></i></div>
                <div>
                    <strong>1. Submit Request</strong>
                    <div class="text-muted small">Provide waste, image and location details.</div>
                </div>
            </div>

            <div class="d-flex gap-3 mb-4">
                <div class="eco-stat-icon mb-0"><i class="bi bi-person-check"></i></div>
                <div>
                    <strong>2. Staff Assignment</strong>
                    <div class="text-muted small">EcoBin assigns an available collection staff member.</div>
                </div>
            </div>

            <div class="d-flex gap-3 mb-4">
                <div class="eco-stat-icon mb-0"><i class="bi bi-truck"></i></div>
                <div>
                    <strong>3. Collection</strong>
                    <div class="text-muted small">Track the job while collection is in progress.</div>
                </div>
            </div>

            <div class="d-flex gap-3">
                <div class="eco-stat-icon mb-0"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <strong>4. Completion</strong>
                    <div class="text-muted small">Collection staff confirms completion and records remarks.</div>
                </div>
            </div>

        </div>

        <div class="eco-card">

            <h5>Collection Status Guide</h5>

            <div class="mt-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Awaiting assignment</span>
                    <span class="eco-status eco-status-pending">Pending</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Staff assigned</span>
                    <span class="eco-status eco-status-assigned">Assigned</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Staff collecting</span>
                    <span class="eco-status eco-status-progress">In Progress</span>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Service finished</span>
                    <span class="eco-status eco-status-completed">Completed</span>
                </div>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     COLLECTION HISTORY
========================================================= -->

<section id="collectionHistory">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h3 class="section-title mb-1">My Collections</h3>
            <p class="text-muted mb-0">Search and track your submitted collection requests.</p>
        </div>
        <span class="badge text-bg-success"><?= count($collections) ?> Total</span>
    </div>

    <!-- SEARCH / FILTER -->
    <div class="eco-card mb-4">
        <form method="get" class="row g-3">

            <input type="hidden" name="page" value="module2">

            <div class="col-lg-6">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input class="form-control" name="search" value="<?= Security::e($search) ?>"
                           placeholder="Search request ID, category or location">
                </div>
            </div>

            <div class="col-lg-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach (['Pending', 'Assigned', 'In Progress', 'Completed', 'Cancelled'] as $status): ?>
                        <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-2 d-flex align-items-end">
                <button class="btn btn-eco w-100">Filter</button>
            </div>

        </form>
    </div>

    <!-- NO RESULT -->
    <?php if (count($filteredCollections) === 0): ?>
        <div class="eco-card text-center py-5">
            <div style="font-size:50px;">🗑️</div>
            <h4 class="mt-3">No Collection Requests Found</h4>
            <p class="text-muted mb-0">Submit a new waste report or change your search filters.</p>
        </div>
    <?php endif; ?>

    <!-- COLLECTION CARDS -->
    <div class="row g-4">

        <?php foreach ($filteredCollections as $c):

            $report = null;
            foreach ($reports as $r) {
                if ($r->id === $c->wasteReportId) {
                    $report = $r;
                    break;
                }
            }

            $progress = collectionProgress($c->status);
            ?>

            <div class="col-12">
                <div class="eco-card">

                    <!-- TOP -->
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                        <div>
                            <div class="text-muted small text-uppercase fw-semibold">Collection Request</div>
                            <h4 class="mt-1 mb-1">
                                #<?= $c->id ?>
                                <?php if ($report): ?> · <?= Security::e($report->category) ?><?php endif; ?>
                            </h4>
                            <div class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= Security::e($report->address ?? 'No location available') ?>
                            </div>
                        </div>

                        <div class="text-end">
                            <span class="eco-status <?= collectionStatusClass($c->status) ?>"><?= Security::e($c->status) ?></span>

                            <?php if ($report): ?>
                                <div class="mt-2">
                                    <?php if (property_exists($report, 'priority')): ?>
                                        <span class="badge text-bg-light"><?= Security::e($report->priority) ?> Priority</span>
                                    <?php endif; ?>
                                    <?php if (property_exists($report, 'wasteSize')): ?>
                                        <span class="badge text-bg-light"><?= Security::e($report->wasteSize) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <hr>

                    <!-- INFORMATION -->
                    <div class="row g-4">

                        <div class="col-lg-4">
                            <h6>Request Details</h6>
                            <div class="small">
                                <div class="mb-2">
                                    <span class="text-muted">Submitted</span><br>
                                    <strong><?= isset($c->createdAt) ? $c->createdAt->format('d M Y, h:i A') : '-' ?></strong>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted">Preferred Date</span><br>
                                    <strong><?= $c->preferredDate->format('d M Y') ?></strong>
                                </div>
                                <div>
                                    <span class="text-muted">Scheduled Date</span><br>
                                    <strong><?= $c->scheduledDate ? $c->scheduledDate->format('d M Y') : 'Not scheduled yet' ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <h6>Waste Details</h6>
                            <?php if ($report): ?>
                                <div class="small">
                                    <div class="mb-2">
                                        <strong>Description</strong>
                                        <div class="text-muted"><?= Security::e($report->description) ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">Waste report information unavailable.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-4">
                            <h6>Collection Remarks</h6>
                            <div class="text-muted small">
                                <?= $c->remarks ? nl2br(Security::e($c->remarks)) : 'No collection remarks yet.' ?>
                            </div>
                        </div>

                    </div>

                    <!-- PROGRESS -->
                    <?php if ($c->status !== 'Cancelled'): ?>
                        <hr>
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Collection Progress</strong>
                                <span class="text-muted small"><?= $progress ?>%</span>
                            </div>

                            <div class="progress" style="height:9px;">
                                <div class="progress-bar bg-success" style="width: <?= $progress ?>%;"></div>
                            </div>

                            <div class="row text-center mt-3 small">

                                <div class="col">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <div>Submitted</div>
                                </div>

                                <div class="col">
                                    <i class="bi <?= in_array($c->status, ['Assigned', 'In Progress', 'Completed'], true) ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i>
                                    <div>Assigned</div>
                                </div>

                                <div class="col">
                                    <i class="bi <?= in_array($c->status, ['In Progress', 'Completed'], true) ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i>
                                    <div>In Progress</div>
                                </div>

                                <div class="col">
                                    <i class="bi <?= $c->status === 'Completed' ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i>
                                    <div>Completed</div>
                                </div>

                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- ACTION -->
                    <?php if ($c->status === 'Pending'): ?>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="text-muted small">You may cancel this request before collection staff is assigned.</span>
                            <form method="post" action="index.php?page=module2-cancel"
                                  onsubmit="return confirm('Are you sure you want to cancel this collection request?');">
                                <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                                <input type="hidden" name="collection_id" value="<?= $c->id ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle me-1"></i> Cancel Request
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        <?php endforeach; ?>

    </div>

</section>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

    /*
    |--------------------------------------------------------------------------
    | LEAFLET MAP
    |--------------------------------------------------------------------------
    */

    const map = L.map('map').setView([3.1390, 101.6869], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker;

    async function setLocation(lat, lng) {

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        }

        marker.off('dragend');
        marker.on('dragend', function (event) {
            const position = event.target.getLatLng();
            setLocation(position.lat, position.lng);
        });

        map.setView([lat, lng], 16);

        /*
         * External Web Service: OpenStreetMap Nominatim reverse geocoding.
         */
        try {
            const response = await fetch(
                'https://nominatim.openstreetmap.org/reverse'
                + '?format=jsonv2'
                + '&lat=' + encodeURIComponent(lat)
                + '&lon=' + encodeURIComponent(lng)
            );

            const data = await response.json();

            if (data.display_name) {
                document.getElementById('address').value = data.display_name;
            }
        } catch (error) {
            console.log('Reverse geocoding unavailable.');
        }
    }

    map.on('click', function (event) {
        setLocation(event.latlng.lat, event.latlng.lng);
    });

    document.getElementById('currentLocationButton').addEventListener('click', function () {

        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                setLocation(position.coords.latitude, position.coords.longitude);
            },
            function () {
                alert('Unable to access your current location.');
            }
        );
    });


    /*
    |--------------------------------------------------------------------------
    | IMAGE PREVIEW
    |--------------------------------------------------------------------------
    */

    const wasteImages = document.getElementById('wasteImages');
    const preview = document.getElementById('imagePreview');

    wasteImages.addEventListener('change', function () {

        preview.innerHTML = '';

        const files = Array.from(this.files);

        if (files.length > 5) {
            alert('Maximum 5 images are allowed.');
            this.value = '';
            return;
        }

        files.forEach(function (file) {

            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                const image = document.createElement('img');
                image.src = event.target.result;
                image.style.width = '90px';
                image.style.height = '90px';
                image.style.objectFit = 'cover';
                image.style.borderRadius = '12px';
                image.style.border = '1px solid #e5ebe7';
                preview.appendChild(image);
            };

            reader.readAsDataURL(file);
        });

    });

</script>