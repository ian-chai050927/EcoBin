<?php
declare(strict_types=1);

use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\Notification;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\RecyclingSubmission;

$container = require __DIR__ . '/bootstrap.php';
$em = $container['em'];
$app = $container['app'];

header('Content-Type: application/json; charset=utf-8');

function respond(array $body, int $code = 200): never {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$token = $_SERVER['HTTP_X_SERVICE_TOKEN'] ?? '';
if (!hash_equals($app['service_token'], $token)) {
    respond([
        'requestID'=>null,'timestamp'=>date('c'),'status'=>'ERROR','error'=>'Unauthorized'
    ], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$requestID = $input['requestID'] ?? null;
$timestamp = $input['timestamp'] ?? null;
$service = $input['service'] ?? '';
$payload = $input['payload'] ?? [];

if (!$requestID || !$timestamp || !$service) {
    respond([
        'requestID'=>$requestID,'timestamp'=>date('c'),'status'=>'ERROR','error'=>'IFA fields missing'
    ], 400);
}

try {
    switch ($service) {
        case 'collection.status':
            $c = $em->find(CollectionRequest::class, (int)($payload['collection_id'] ?? 0));
            if (!$c) throw new RuntimeException('Collection not found');
            $data = [
                'collection_id'=>$c->id,
                'status'=>$c->status,
                'scheduled_date'=>$c->scheduledDate?->format('Y-m-d'),
                'staff_id'=>$c->collectionStaffId
            ];
            break;

        case 'notification.create':
            $n = new Notification();
            $n->userId = (int)($payload['user_id'] ?? 0);
            $n->title = mb_substr((string)($payload['title'] ?? 'Notification'),0,120);
            $n->message = mb_substr((string)($payload['message'] ?? ''),0,4000);
            $n->type = mb_substr((string)($payload['type'] ?? 'System'),0,50);
            if ($n->userId <= 0 || $n->message === '') throw new RuntimeException('Invalid notification payload');
            $em->persist($n); $em->flush();
            $data = ['notification_id'=>$n->id];
            break;

        case 'dashboard.stats':
            $collections = $em->getRepository(CollectionRequest::class)->findAll();
            $recycling = $em->getRepository(RecyclingSubmission::class)->findAll();
            $waste = $em->getRepository(WasteReport::class)->findAll();
            $data = [
                'waste_reports'=>count($waste),
                'collection_requests'=>count($collections),
                'collection_completed'=>count(array_filter($collections, fn($x)=>$x->status==='Completed')),
                'recycling_submissions'=>count($recycling),
                'recycling_approved'=>count(array_filter($recycling, fn($x)=>$x->status==='Approved')),
            ];
            break;

        default:
            throw new RuntimeException('Unknown service');
    }

    respond([
        'requestID'=>$requestID,
        'timestamp'=>date('c'),
        'status'=>'SUCCESS',
        'data'=>$data
    ]);
} catch (Throwable $e) {
    respond([
        'requestID'=>$requestID,
        'timestamp'=>date('c'),
        'status'=>'ERROR',
        'error'=>$e->getMessage()
    ], 400);
}
