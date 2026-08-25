<?php
declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use EcoBin\Services\EventDispatcher;
use EcoBin\Observers\NotificationObserver;
use EcoBin\Observers\AuditObserver;

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

$dispatcher = new EventDispatcher();
$dispatcher->attach(new NotificationObserver($entityManager));
$dispatcher->attach(new AuditObserver($entityManager));

return [
    'em' => $entityManager,
    'dispatcher' => $dispatcher,
    'app' => $app,
];
