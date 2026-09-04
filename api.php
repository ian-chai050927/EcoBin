<?php
/*
 * @author EcoBin Team — Shared API Gateway (all modules expose services here)
 * REST/JSON web-service endpoint protected by X-Service-Token.
 * Each named 'service' corresponds to one module's exposed function.
 * IFA-compliant: every request requires requestID + timestamp;
 * every response includes status + timestamp.
 */
declare(strict_types=1);

use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\Notification;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\RecyclingSubmission;
use EcoBin\Entities\User;

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

        case 'user.status':
            // Module 1 (Auth): exposed so other modules can re-verify
            // a user's current role/status at the moment of use,
            // rather than trusting a possibly-stale local read.
            $email = trim((string)($payload['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Invalid or missing email address.');
            }
            $u = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            $data = [
                'found' => (bool) $u,
            ];
            if ($u) {
                $data['userDetails'] = [
                    'name' => $u->name,
                    'role' => $u->role,
                    'status' => $u->status,
                ];
            }
            break;

        case 'notification.create':
            $userId = (int)($payload['user_id'] ?? 0);
            $notifMsg = mb_substr((string)($payload['message'] ?? ''), 0, 4000);
            if ($userId <= 0 || $notifMsg === '') throw new RuntimeException('Invalid notification payload');
            $n = new Notification();
            /*
             * ORM RELATIONSHIP USAGE:
             * Assign the user association via getReference() — Doctrine writes
             * the user_id FK column on flush without loading the User entity.
             */
            $n->user    = $em->getReference(\EcoBin\Entities\User::class, $userId);
            $n->title   = mb_substr((string)($payload['title'] ?? 'Notification'), 0, 120);
            $n->message = $notifMsg;
            $n->type    = mb_substr((string)($payload['type'] ?? 'System'), 0, 50);
            $em->persist($n); $em->flush();
            $data = ['notification_id' => $n->id];
            break;

        /*
         * MODULE 3 — Recycling & Rewards
         * Exposed service: recycling.status
         * Returns the current status, material, weight and points for a
         * recycling submission. Other modules can call this to confirm
         * that a submission was processed and points awarded.
         *
         * IFA Request fields:
         *   payload.submission_id  int  mandatory  The RecyclingSubmission ID
         *
         * IFA Response data fields:
         *   submission_id  int     — the submission's ID
         *   material       string  — recycled material type
         *   weight_kg      string  — weight in kilograms
         *   points         int     — points awarded (0 if not yet approved)
         *   status         string  — Pending | Approved | Rejected
         */
        case 'recycling.status':
            $submissionId = (int)($payload['submission_id'] ?? 0);
            if ($submissionId <= 0) throw new RuntimeException('submission_id is required and must be a positive integer.');
            $sub = $em->find(RecyclingSubmission::class, $submissionId);
            if (!$sub) throw new RuntimeException('Recycling submission not found.');
            $data = [
                'submission_id' => $sub->id,
                'material'      => $sub->material,
                'weight_kg'     => $sub->weightKg,
                'points'        => $sub->points,
                'status'        => $sub->status,
            ];
            break;

        case 'dashboard.stats':
            // Use the Facade Pattern to hide complex aggregation logic
            require_once __DIR__ . '/src/Services/DashboardAnalyticsFacade.php';
            $facade = new \EcoBin\Services\DashboardAnalyticsFacade($em);

            // The getDashboardStats method has built-in Date Bounding to mitigate Application-Level DoS
            $data = $facade->getDashboardStats();
            break;
        case 'notification.email':
            $email = trim((string)($payload['email'] ?? ''));
            $subject = trim((string)($payload['subject'] ?? ''));
            $message = (string)($payload['message'] ?? '');
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Invalid or missing email address.');
            }
            if ($subject === '' || $message === '') {
                throw new RuntimeException('Subject and message are required.');
            }
            $mailer = new \EcoBin\Services\Mailer($app['mail'] ?? []);
            $sent = $mailer->send($email, $subject, $message);
            if (!$sent) {
                throw new RuntimeException('Email dispatch failed.');
            }
            $data = ['email' => $email, 'delivered' => true];
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
