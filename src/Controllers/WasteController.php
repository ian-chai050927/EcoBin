<?php
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\User;
use EcoBin\Services\Security;
use EcoBin\Services\InternalApiClient;

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
        $reports = $this->em->getRepository(WasteReport::class)->findBy(['residentId'=>$uid], ['id'=>'DESC']);
        $collections = $this->em->getRepository(CollectionRequest::class)->findBy(['residentId'=>$uid], ['id'=>'DESC']);
        view('module2/resident', ['title'=>'Waste Report & Collection','reports'=>$reports,'collections'=>$collections]);
    }

    public function submit(): void
    {
        Security::requireRole(['Resident']);
        Security::verifyCsrf();

        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $preferred = $_POST['preferred_date'] ?? '';

        $allowed = ['General Waste','Plastic','Electronic Waste','Bulky Waste','Organic Waste','Hazardous Waste'];
        if (!in_array($category, $allowed, true) || $description === '' || $address === '' || !$preferred) {
            exit('Invalid report data.');
        }

        $imagePath = $this->secureUpload($_FILES['waste_image'] ?? null);

        $report = new WasteReport();
        $report->residentId = (int)$_SESSION['user_id'];
        $report->category = $category;
        $report->description = mb_substr($description, 0, 1500);
        $report->image = $imagePath;
        $report->latitude = ($_POST['latitude'] ?? '') !== '' ? (string)$_POST['latitude'] : null;
        $report->longitude = ($_POST['longitude'] ?? '') !== '' ? (string)$_POST['longitude'] : null;
        $report->address = mb_substr($address, 0, 500);

        $collection = new CollectionRequest();
        $collection->residentId = (int)$_SESSION['user_id'];
        $collection->preferredDate = new \DateTime($preferred);

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($report);
            $this->em->flush();
            $collection->wasteReportId = $report->id;
            $this->em->persist($collection);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        $this->dispatcher->dispatch('waste.report_submitted', ['entity'=>'WasteReport','entity_id'=>$report->id]);
        Security::flash('success', 'Waste report and collection request submitted.');
        header('Location: index.php?page=module2'); exit;
    }

    public function admin(): void
    {
        Security::requireRole(['Admin']);
        $collections = $this->em->getRepository(CollectionRequest::class)->findBy([], ['id'=>'DESC']);
        $staff = $this->em->getRepository(User::class)->findBy(['role'=>'Collection Staff','status'=>'Active'], ['name'=>'ASC']);
        $reports = [];
        $residents = [];
        foreach ($collections as $c) {
            $reports[$c->wasteReportId] = $this->em->find(WasteReport::class, $c->wasteReportId);
            $residents[$c->residentId] = $this->em->find(User::class, $c->residentId);
        }
        view('module2/admin', compact('collections','staff','reports','residents') + ['title'=>'Collection Assignment']);
    }

    public function assign(): void
    {
        Security::requireRole(['Admin']);
        Security::verifyCsrf();

        $collection = $this->em->find(CollectionRequest::class, (int)($_POST['collection_id'] ?? 0));
        $staff = $this->em->find(User::class, (int)($_POST['staff_id'] ?? 0));
        if (!$collection || !$staff || $staff->role !== 'Collection Staff') exit('Invalid assignment.');

        $collection->collectionStaffId = $staff->id;
        $collection->scheduledDate = new \DateTime($_POST['scheduled_date']);
        $collection->status = 'Assigned';
        $this->em->flush();

        $this->dispatcher->dispatch('collection.assigned', [
            'entity'=>'CollectionRequest','entity_id'=>$collection->id,'user_id'=>$collection->residentId
        ]);

        // Web-service consumption: Module 2 -> Module 5 notification service.
        $client = new InternalApiClient($this->app['base_url'], $this->app['service_token']);
        $client->call('notification.create', [
            'user_id'=>$collection->residentId,
            'title'=>'Collection Assigned',
            'message'=>'Your collection has been scheduled for ' . $collection->scheduledDate->format('Y-m-d'),
            'type'=>'Collection'
        ]);

        Security::flash('success', 'Collection assigned and notification event triggered.');
        header('Location: index.php?page=module2-admin'); exit;
    }

    public function staff(): void
    {
        Security::requireRole(['Collection Staff']);
        $tasks = $this->em->getRepository(CollectionRequest::class)->findBy(
            ['collectionStaffId'=>(int)$_SESSION['user_id']], ['id'=>'DESC']
        );
        $reports = [];
        $residents = [];
        foreach ($tasks as $c) {
            $reports[$c->wasteReportId] = $this->em->find(WasteReport::class, $c->wasteReportId);
            $residents[$c->residentId] = $this->em->find(User::class, $c->residentId);
        }
        view('module2/staff', compact('tasks','reports','residents') + ['title'=>'Assigned Collections']);
    }

    public function status(): void
    {
        Security::requireRole(['Collection Staff']);
        Security::verifyCsrf();

        $c = $this->em->find(CollectionRequest::class, (int)($_POST['collection_id'] ?? 0));
        if (!$c || $c->collectionStaffId !== (int)$_SESSION['user_id']) exit('Task not assigned to you.');

        $new = $_POST['status'] ?? '';
        $valid = [
            'Assigned' => ['In Progress'],
            'In Progress' => ['Completed'],
            'Completed' => [],
        ];
        if (!in_array($new, $valid[$c->status] ?? [], true)) exit('Invalid status transition.');

        $c->status = $new;
        $c->remarks = mb_substr(trim($_POST['remarks'] ?? ''), 0, 1000);
        $this->em->flush();

        $event = $new === 'In Progress' ? 'collection.in_progress' : 'collection.completed';
        $this->dispatcher->dispatch($event, [
            'entity'=>'CollectionRequest','entity_id'=>$c->id,'user_id'=>$c->residentId
        ]);

        Security::flash('success', 'Collection status updated.');
        header('Location: index.php?page=module2-staff'); exit;
    }

    private function secureUpload(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) exit('Upload failed.');
        if ($file['size'] > 5 * 1024 * 1024) exit('Image must be 5MB or less.');

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $map = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($map[$mime])) exit('Only JPG, PNG or WEBP images are allowed.');

        $filename = bin2hex(random_bytes(18)) . '.' . $map[$mime];
        $dir = __DIR__ . '/../../uploads/waste/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) exit('Unable to save image.');
        return 'uploads/waste/' . $filename;
    }
}
