<?php
declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use EcoBin\Services\EventDispatcher;
use EcoBin\Services\Mailer;
use EcoBin\Observers\NotificationObserver;
use EcoBin\Observers\AuditObserver;
use EcoBin\Observers\EmailObserver;

require __DIR__ . '/vendor/autoload.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$app = require __DIR__ . '/config/app.php';
$db = require __DIR__ . '/config/database.php';

$config = Setup::createAttributeMetadataConfiguration(
    [__DIR__ . '/src/Entities'],
    true
);

$entityManager = EntityManager::create($db, $config);

$mailer = new Mailer($app['mail'] ?? []);

$dispatcher = new EventDispatcher();
$dispatcher->attach(new NotificationObserver($entityManager));
$dispatcher->attach(new AuditObserver($entityManager));
$dispatcher->attach(new EmailObserver($entityManager, $mailer));

return [
    'em' => $entityManager,
    'dispatcher' => $dispatcher,
    'app' => $app,
    'mailer' => $mailer,
];