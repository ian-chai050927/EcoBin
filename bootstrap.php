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

try {
    $entityManager->getConnection()->executeStatement(
        "ALTER TABLE recycling_centers ADD COLUMN IF NOT EXISTS operating_hours varchar(120) DEFAULT 'Mon - Fri: 9:00 AM - 5:00 PM'"
    );
} catch (\Throwable) {
    // Gracefully ignore if offline or not yet imported
}

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