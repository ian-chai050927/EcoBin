<?php
namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\RecyclingAppointment;

class ReminderService
{
    public function __construct(
        private EntityManagerInterface $em,
        private $dispatcher
    ) {}


    public function sendCollectionReminders(int $daysAhead = 1): int
    {
        $windowEnd = (new \DateTime())->modify("+{$daysAhead} days")->setTime(23, 59, 59);
        $now = new \DateTime();

        $collections = $this->em->getRepository(CollectionRequest::class)->findBy([
            'status' => 'Assigned',
        ]);

        $count = 0;
        foreach ($collections as $c) {
            if (!$c->scheduledDate) {
                continue;
            }
            if ($c->scheduledDate >= $now && $c->scheduledDate <= $windowEnd) {
                $this->dispatcher->dispatch('collection.reminder', [
                    'entity' => 'CollectionRequest',
                    'entity_id' => $c->id,
                    'user_id' => $c->residentId,
                    'message' => 'Reminder: your waste collection is scheduled for '
                        . $c->scheduledDate->format('d M Y') . '.',
                ]);
                $count++;
            }
        }

        return $count;
    }


    public function sendAppointmentReminders(int $hoursAhead = 24): int
    {
        $windowEnd = (new \DateTime())->modify("+{$hoursAhead} hours");
        $now = new \DateTime();

        $appointments = $this->em->getRepository(RecyclingAppointment::class)->findBy([
            'status' => 'Confirmed',
        ]);

        $count = 0;
        foreach ($appointments as $a) {
            if ($a->appointmentAt >= $now && $a->appointmentAt <= $windowEnd) {
                $this->dispatcher->dispatch('appointment.reminder', [
                    'entity' => 'RecyclingAppointment',
                    'entity_id' => $a->id,
                    'user_id' => $a->residentId,
                    'message' => 'Reminder: your recycling appointment is at '
                        . $a->appointmentAt->format('d M Y, H:i') . '.',
                ]);
                $count++;
            }
        }

        return $count;
    }
}