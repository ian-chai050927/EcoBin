<?php
declare(strict_types=1);

use EcoBin\Services\ReminderService;
use EcoBin\Services\SystemConfigService;

$container = require __DIR__ . '/bootstrap.php';
$em = $container['em'];
$dispatcher = $container['dispatcher'];

$config = new SystemConfigService($em);
$reminders = new ReminderService($em, $dispatcher);

$daysAhead = (int)($config->get('reminder.collection_days_ahead') ?? 1);
$hoursAhead = (int)($config->get('reminder.appointment_hours_ahead') ?? 24);

$collectionCount = $reminders->sendCollectionReminders($daysAhead);
$appointmentCount = $reminders->sendAppointmentReminders($hoursAhead);

echo "Collection reminders sent (window: {$daysAhead} day(s) ahead): {$collectionCount}\n";
echo "Appointment reminders sent (window: {$hoursAhead} hour(s) ahead): {$appointmentCount}\n";