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

            /*
             * ORM RELATIONSHIP USAGE:
             * Access wasteReport details through the ORM association directly.
             */
            $report = $collection->wasteReport;

            $haystack = strtolower(
                    $collection->id . ' '
                    . ($report?->category ?? '') . ' '
                    . ($report?->address ?? '') . ' '
                    . ($report?->description ?? '') . ' '
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

    <a href="index.php?page=my-collections" class="btn btn-eco-outline">
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

                   <!-- WASTE IMAGE -->
<div class="col-12">

    <label class="form-label">
        Waste Image
    </label>

    <div class="input-group">

        <input
            class="form-control"
            type="file"
            id="wasteImages"
            name="waste_images[]"
            accept="image/jpeg,image/png,image/webp"
        >

        <button
            class="btn btn-outline-danger"
            type="button"
            id="removeWasteImage"
            style="display:none;"
        >
            <i class="bi bi-x-circle"></i>
            Remove
        </button>

    </div>

    <div class="form-text">
        Upload a JPG, PNG or WEBP image. Maximum 5MB.
    </div>

    <div
        id="imagePreview"
        class="mt-3"
    ></div>

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
                        <div
    id="map"
    style="
        height: 320px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #dee2e6;
    "
></div>
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

document
    .getElementById('currentLocationButton')
    .addEventListener('click', function () {

        if (!navigator.geolocation) {

            alert(
                'Your browser does not support location services. ' +
                'Please select your location manually on the map.'
            );

            return;
        }


        const button = this;

        const originalText =
            button.innerHTML;


        button.disabled = true;

        button.innerHTML =
            '<i class="bi bi-hourglass-split"></i> Locating...';


        navigator.geolocation.getCurrentPosition(

            function (position) {

                const latitude =
                    position.coords.latitude;

                const longitude =
                    position.coords.longitude;


                setLocation(
                    latitude,
                    longitude
                );


                button.disabled = false;

                button.innerHTML =
                    '<i class="bi bi-check-circle"></i> Location Found';

            },


            function (error) {

                button.disabled = false;

                button.innerHTML =
                    originalText;


                let message =
                    'Unable to get your current location. ';


                switch (error.code) {

                    case error.PERMISSION_DENIED:

                        message +=
                            'Location permission was denied. ' +
                            'Please allow location access in your browser, ' +
                            'or select the location manually on the map.';

                        break;


                    case error.POSITION_UNAVAILABLE:

                        message +=
                            'Your location is currently unavailable. ' +
                            'Please select it manually on the map.';

                        break;


                    case error.TIMEOUT:

                        message +=
                            'Location request timed out. ' +
                            'Please try again or select the location manually.';

                        break;


                    default:

                        message +=
                            'Please select the location manually on the map.';

                }


                alert(message);

            },


            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }

        );

    });


    /*
|--------------------------------------------------------------------------
| IMAGE PREVIEW + REMOVE
|--------------------------------------------------------------------------
*/

const wasteImages =
    document.getElementById('wasteImages');

const imagePreview =
    document.getElementById('imagePreview');

const removeWasteImage =
    document.getElementById('removeWasteImage');


wasteImages.addEventListener(
    'change',
    function () {

        imagePreview.innerHTML = '';

        if (this.files.length === 0) {

            removeWasteImage.style.display =
                'none';

            return;
        }

        const file =
            this.files[0];


        if (
            ![
                'image/jpeg',
                'image/png',
                'image/webp'
            ].includes(file.type)
        ) {

            alert(
                'Only JPG, PNG or WEBP images are allowed.'
            );

            this.value = '';

            removeWasteImage.style.display =
                'none';

            return;
        }


        if (
            file.size >
            5 * 1024 * 1024
        ) {

            alert(
                'Image must be 5MB or smaller.'
            );

            this.value = '';

            removeWasteImage.style.display =
                'none';

            return;
        }


        const reader =
            new FileReader();


        reader.onload =
            function (event) {

                imagePreview.innerHTML = `
                    <img
                        src="${event.target.result}"
                        alt="Waste image preview"
                        style="
                            width:140px;
                            height:140px;
                            object-fit:cover;
                            border-radius:12px;
                            border:1px solid #dee2e6;
                        "
                    >
                `;

            };


        reader.readAsDataURL(file);


        removeWasteImage.style.display =
            'block';
    }
);


removeWasteImage.addEventListener(
    'click',
    function () {

        wasteImages.value = '';

        imagePreview.innerHTML = '';

        this.style.display =
            'none';
    }
);

</script>