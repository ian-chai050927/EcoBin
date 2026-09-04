<?php

namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\User;
use EcoBin\Services\Security;
use EcoBin\Services\InternalApiClient;
use EcoBin\Services\CollectionWorkflow;
use EcoBin\Services\CollectionAuthorization;
use EcoBin\Services\SecureImageUploader;

class WasteController
{
    public function __construct(
        private EntityManagerInterface $em,
        private array $app,
        private $dispatcher
    ) {}

    public function resident(): void
    {
        Security::requireRole(['Resident']);
        $uid = (int)$_SESSION['user_id'];


        $reports     = $this->em->getRepository(WasteReport::class)->findBy(['resident' => $uid], ['id' => 'DESC']);
        $collections = $this->em->getRepository(CollectionRequest::class)->findBy(['resident' => $uid], ['id' => 'DESC']);
        view('module2/resident', ['title' => 'Waste Report & Collection', 'reports' => $reports, 'collections' => $collections]);
    }

    public function submit(): void
    {
        Security::requireRole(['Resident']);
        Security::verifyCsrf();

        $category    = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $preferred   = $_POST['preferred_date'] ?? '';
        $priority    = $_POST['priority'] ?? 'Normal';
        $wasteSize   = $_POST['waste_size'] ?? 'Medium';

        $allowedPriorities  = ['Low', 'Normal', 'High', 'Urgent'];
        $allowedWasteSizes  = ['Small', 'Medium', 'Large', 'Extra Large'];
        $allowedCategories  = ['General Waste', 'Plastic', 'Electronic Waste', 'Bulky Waste', 'Organic Waste', 'Hazardous Waste'];

        if (!in_array($priority, $allowedPriorities, true))   { exit('Invalid priority.'); }
        if (!in_array($wasteSize, $allowedWasteSizes, true))  { exit('Invalid waste size.'); }
        if (!in_array($category, $allowedCategories, true) || $description === '' || $address === '' || !$preferred) {
            exit('Invalid report data.');
        }

        $uploader   = new SecureImageUploader();
        $imagePaths = $uploader->uploadMultiple($_FILES['waste_images'] ?? [], 'waste', 5);
        $imagePath  = $imagePaths[0] ?? null;


        $currentUser = $this->em->getReference(User::class, (int)$_SESSION['user_id']);

        $report              = new WasteReport();
        $report->resident    = $currentUser;
        $report->category    = $category;
        $report->description = mb_substr($description, 0, 1500);
        $report->image       = $imagePath;
        $report->priority    = $priority;
        $report->wasteSize   = $wasteSize;
        $report->latitude    = ($_POST['latitude'] ?? '') !== '' ? (string)$_POST['latitude'] : null;
        $report->longitude   = ($_POST['longitude'] ?? '') !== '' ? (string)$_POST['longitude'] : null;
        $report->address     = mb_substr($address, 0, 500);

        $collection           = new CollectionRequest();
        $collection->resident = $currentUser;

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($report);
            $this->em->flush();

            /*
             * ORM RELATIONSHIP USAGE:
             * Assign the ORM association $collection->wasteReport (not a raw int).
             * Doctrine resolves waste_report_id FK automatically on flush.
             */
            $collection->wasteReport    = $report;
            $collection->preferredDate  = new \DateTime($preferred);
            $this->em->persist($collection);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        $this->dispatcher->dispatch('waste.report_submitted', ['entity' => 'WasteReport', 'entity_id' => $report->id]);
        Security::flash('success', 'Waste report and collection request submitted.');
        header('Location: index.php?page=module2'); exit;
    }

    public function cancel(): void
    {
        // Only Resident can cancel a collection request
        Security::requireRole(['Resident']);

        // Prevent CSRF attacks
        Security::verifyCsrf();

        $collectionId = (int)($_POST['collection_id'] ?? 0);

        $collection = $this->em->find(
            CollectionRequest::class,
            $collectionId
        );

        if (!$collection) {
            http_response_code(404);
            exit('Collection request not found.');
        }

        /*
         * SECURE CODING: Broken Access Control / IDOR protection.
         * CollectionAuthorization now uses $collection->resident->id (ORM).
         */
        CollectionAuthorization::ensureResidentOwns($collection);

        try {

            /*
             * DESIGN PATTERN: State Pattern
             * Only a valid state is allowed to cancel.
             * Pending -> Cancelled = allowed
             * Assigned -> Cancelled = blocked
             * Completed -> Cancelled = blocked
             */
            $workflow = new CollectionWorkflow($collection->status);
            $collection->status = $workflow->cancel();

            /*
             * Also update the related waste report via ORM association.
             */
            if ($collection->wasteReport) {
                $collection->wasteReport->status = 'Cancelled';
            }

            $this->em->flush();

            $this->dispatcher->dispatch(
                'collection.cancelled',
                [
                    'entity'    => 'CollectionRequest',
                    'entity_id' => $collection->id,
                    'user_id'   => $collection->resident->id,
                ]
            );

            Security::flash('success', 'Collection request cancelled successfully.');

        } catch (\RuntimeException $e) {
            Security::flash('error', $e->getMessage());
        }

        header('Location: index.php?page=module2');
        exit;
    }

    public function admin(): void
    {
        Security::requireRole(['Admin']);

        /*
         * ORM RELATIONSHIP USAGE:
         * Collections are loaded with their resident and wasteReport associations.
         * Doctrine lazy-loads them on first access — no manual lookup maps needed.
         */
        $collections = $this->em->getRepository(CollectionRequest::class)->findBy([], ['id' => 'DESC']);
        $staff       = $this->em->getRepository(User::class)->findBy(['role' => 'Collection Staff', 'status' => 'Active'], ['name' => 'ASC']);

        view('module2/admin', compact('collections', 'staff') + ['title' => 'Collection Assignment']);
    }

    public function assign(): void
    {
        // Only Admin can assign collection staff
        Security::requireRole(['Admin']);

        // Prevent CSRF attacks
        Security::verifyCsrf();

        $collectionId = (int)($_POST['collection_id'] ?? 0);
        $staffId      = (int)($_POST['staff_id'] ?? 0);
        $scheduledDate = $_POST['scheduled_date'] ?? '';

        /*
         * ORM RELATIONSHIP USAGE:
         * Retrieve entities through Doctrine find() — returns full entity objects.
         */
        $collection = $this->em->find(CollectionRequest::class, $collectionId);
        $staff      = $this->em->find(User::class, $staffId);

        if (!$collection) {
            Security::flash('error', 'Collection request not found.');
            header('Location: index.php?page=module2-admin');
            exit;
        }

        if (!$staff) {
            Security::flash('error', 'Collection staff not found.');
            header('Location: index.php?page=module2-admin');
            exit;
        }

        /*
         * SECURE CODING:
         * Validate that selected account is actually Collection Staff.
         */
        if ($staff->role !== 'Collection Staff') {
            Security::flash('error', 'Selected user is not collection staff.');
            header('Location: index.php?page=module2-admin');
            exit;
        }

        /*
         * Do not assign suspended staff.
         */
        if ($staff->status !== 'Active') {
            Security::flash('error', 'Selected collection staff account is not active.');
            header('Location: index.php?page=module2-admin');
            exit;
        }

        if (empty($scheduledDate)) {
            Security::flash('error', 'Please select a collection date.');
            header('Location: index.php?page=module2-admin');
            exit;
        }

        /*
         * Prevent scheduling in the past.
         */
        $schedule = new \DateTime($scheduledDate);
        $today    = new \DateTime('today');

        if ($schedule < $today) {
            Security::flash('error', 'Collection date cannot be in the past.');
            header('Location: index.php?page=module2-admin');
            exit;
        }

        /*
         * WEB SERVICE (CONSUMED):
         * Module 2 consumes Module 1's user.status service to re-verify the
         * staff member's status at the moment of assignment, rather than
         * trusting only the local read above (which could be stale).
         */
        $statusClient = new InternalApiClient(
            $this->app['base_url'],
            $this->app['service_token']
        );

        $statusResponse = $statusClient->call(
            'user.status',
            ['email' => $staff->email]
        );

        if (($statusResponse['status'] ?? null) === 'ERROR') {
            error_log(
                'Module 1 user-status service unavailable during assignment: '
                . ($statusResponse['error'] ?? 'unknown error')
            );
        } elseif (
            isset($statusResponse['userDetails']['status'])
            && $statusResponse['userDetails']['status'] !== 'Active'
        ) {
            Security::flash('error', 'Selected collection staff account is no longer active (verified via Module 1 service).');
            header('Location: index.php?page=module2-admin');
            exit;
        }

        try {

            /*
             * DESIGN PATTERN: State Pattern
             * The workflow decides whether the current state can be assigned.
             */
            $workflow  = new CollectionWorkflow($collection->status);
            $newStatus = $workflow->assign();

            /*
             * ORM RELATIONSHIP USAGE:
             * Assign the collectionStaff association (User object) instead of
             * writing a raw staff_id integer.
             */
            $collection->collectionStaff = $staff;
            $collection->scheduledDate   = $schedule;
            $collection->status          = $newStatus;

            /*
             * Keep waste report status synchronized via ORM association.
             */
            if ($collection->wasteReport) {
                $collection->wasteReport->status = $newStatus;
            }

            /*
             * Doctrine ORM writes changes to MySQL.
             */
            $this->em->flush();

            $this->dispatcher->dispatch(
                'collection.assigned',
                [
                    'entity'    => 'CollectionRequest',
                    'entity_id' => $collection->id,
                    'user_id'   => $collection->resident->id,
                ]
            );

            /*
             * WEB SERVICE: Module 2 consumes Module 5 notification service.
             */
            $client = new InternalApiClient(
                $this->app['base_url'],
                $this->app['service_token']
            );

            $client->call('notification.create', [
                'user_id' => $collection->resident->id,
                'title'   => 'Collection Assigned',
                'message' => 'Your waste collection has been assigned to '
                    . $staff->name
                    . ' and scheduled for '
                    . $schedule->format('d M Y') . '.',
                'type' => 'Collection'
            ]);

            Security::flash('success', 'Collection staff assigned successfully.');

        } catch (\RuntimeException $e) {
            /*
             * State Pattern rejects invalid transitions.
             */
            Security::flash('error', $e->getMessage());
        }

        header('Location: index.php?page=module2-admin');
        exit;
    }

    public function staff(): void
    {
        Security::requireRole(['Collection Staff']);
        $tasks = $this->em->getRepository(CollectionRequest::class)->findBy(
            ['collectionStaff' => (int)$_SESSION['user_id']], ['id' => 'DESC']
        );
        view('module2/staff', compact('tasks') + ['title' => 'Assigned Collections']);
    }

    public function status(): void
    {
        Security::requireRole(['Collection Staff']);
        Security::verifyCsrf();

        $c = $this->em->find(CollectionRequest::class, (int)($_POST['collection_id'] ?? 0));
        if (!$c) {
            http_response_code(404);
            exit('Task not found.');
        }

        CollectionAuthorization::ensureAssignedStaff($c);

        $new   = $_POST['status'] ?? '';
        $valid = [
            'Assigned'    => ['In Progress'],
            'In Progress' => ['Completed'],
            'Completed'   => [],
        ];
        if (!in_array($new, $valid[$c->status] ?? [], true)) exit('Invalid status transition.');

        $c->status  = $new;
        $c->remarks = mb_substr(trim($_POST['remarks'] ?? ''), 0, 1000);
        $this->em->flush();

        $event = $new === 'In Progress' ? 'collection.in_progress' : 'collection.completed';
        $this->dispatcher->dispatch($event, [
            'entity'    => 'CollectionRequest',
            'entity_id' => $c->id,
            'user_id'   => $c->resident->id,
        ]);

        Security::flash('success', 'Collection status updated.');
        header('Location: index.php?page=module2-staff'); exit;
    }

    public function myCollections(): void
    {
        Security::requireRole(['Resident']);
        $uid         = (int)$_SESSION['user_id'];
        $collections = $this->em->getRepository(CollectionRequest::class)->findBy(['resident' => $uid], ['id' => 'DESC']);

        view('module2/my-collections', [
            'title'       => 'My Collections',
            'collections' => $collections,
        ]);
    }
}