<?php
/*
 * @author EcoBin Team — Shared front controller / router
 * Routes every ?page= request to the correct Controller method.
 * Bootstraps Doctrine ORM, EventDispatcher and all services.
 */
declare(strict_types=1);

use EcoBin\Controllers\AuthController;
use EcoBin\Controllers\WasteController;
use EcoBin\Controllers\RecyclingController;
use EcoBin\Controllers\DashboardController;
use EcoBin\Controllers\SystemController;
use EcoBin\Services\Security;
use EcoBin\Services\NotificationService;
use EcoBin\Services\AnnouncementService;
use EcoBin\Services\SystemConfigService;
use EcoBin\Services\AuditLogService;
use EcoBin\Entities\Announcement;

$container = require __DIR__ . '/bootstrap.php';
$em = $container['em'];
$dispatcher = $container['dispatcher'];
$app = $container['app'];

function view(string $name, array $data = []): void {
    extract($data);
    require __DIR__ . '/views/shared/header.php';
    require __DIR__ . '/views/' . $name . '.php';
    require __DIR__ . '/views/shared/footer.php';
}

$auth = new AuthController($em, $app, $dispatcher);
$waste = new WasteController($em, $app, $dispatcher);
$recycling = new RecyclingController($em, $app, $dispatcher);
$dashboard = new DashboardController($em, $app);


$notificationService = new NotificationService($em);
$announcementService = new AnnouncementService($em);
$configService = new SystemConfigService($em);
$auditLogService = new AuditLogService($em);

$system = new SystemController(
    $notificationService,
    $announcementService,
    $configService,
    $auditLogService,
    $dispatcher,
    $app          // passed so SystemController can call InternalApiClient
);

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'login': $auth->login(); break;
    case 'register': $auth->register(); break;
    case 'verify': $auth->verify(); break;
    case 'forgot': $auth->forgot(); break;
    case 'reset': $auth->reset(); break;
    case 'logout': $auth->logout(); break;
    case 'profile': $auth->profile(); break;
    case 'users': $auth->users(); break;
    case 'user-status': $auth->changeStatus(); break;
    case 'user-create': $auth->createUser(); break;
    case 'user-update': $auth->updateUser(); break;
    case 'my-collections':$waste->myCollections();break;

    case 'module2':
        $waste->resident();
        break;

    case 'module2-submit':
        $waste->submit();
        break;

    case 'module2-cancel':
        $waste->cancel();
        break;

    case 'module2-admin':
        $waste->admin();
        break;

    case 'module2-assign':
        $waste->assign();
        break;

    case 'module2-staff':
        $waste->staff();
        break;

    case 'module2-status':
        $waste->status();
        break;

    case 'module3': $recycling->resident(); break;
    case 'module3-submit': $recycling->submit(); break;
    case 'module3-appointment': $recycling->appointment(); break;
    case 'module3-operator': $recycling->operator(); break;
    case 'module3-center-save': $recycling->centerSave(); break;
    case 'module3-review-submission': $recycling->reviewSubmission(); break;
    case 'module3-review-appointment': $recycling->reviewAppointment(); break;
    case 'module3-redeem': $recycling->redeem(); break;

    case 'module4': $dashboard->index(); break;
    case 'module4-report': $dashboard->report(); break;
    case 'module4-csv': $dashboard->csv(); break;
    case 'module4-pdf': $dashboard->pdf(); break;

    case 'notifications': $system->notifications(); break;
    case 'notification-read': $system->markRead(); break;
    case 'module5-admin': $system->admin(); break;
    case 'module5-announcement': $system->announcement(); break;
    case 'module5-config': $system->config(); break;

    default:
        $announcements = $em->getRepository(Announcement::class)->findBy([], ['id'=>'DESC'], 5);
        view('home', ['title'=>'EcoBin Home','announcements'=>$announcements]);
}