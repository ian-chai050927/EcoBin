<?php
declare(strict_types=1);

use EcoBin\Services\ReminderService;

$container = require __DIR__ . '/bootstrap.php';
$em = $container['em'];
$dispatcher = $container['dispatcher'];

$reminders = new ReminderService($em, $dispatcher);

$collectionCount = $reminders->sendCollectionReminders(1);   // due within 1 day
$appointmentCount = $reminders->sendAppointmentReminders(24); // due within 24 hours

echo "Collection reminders sent: {$collectionCount}\n";
echo "Appointment reminders sent: {$appointmentCount}\n";