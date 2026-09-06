<?php
/**
 * Module 5 :Notification & System Administration
 * @author Tang Yik Hong
 */

namespace EcoBin\Observers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Contracts\EventObserver;
use EcoBin\Entities\Notification;
use EcoBin\Entities\User;

class NotificationObserver implements EventObserver
{
    public function __construct(private EntityManagerInterface $em) {}

    public function update(string $event, array $data): void
    {
        $map = [
            'collection.assigned'    => ['Collection Scheduled', 'Your waste collection has been assigned.', 'Collection'],
            'collection.in_progress' => ['Collection In Progress', 'The collector has started your collection.', 'Collection'],
            'collection.completed'   => ['Collection Completed', 'Your waste collection has been completed.', 'Collection'],
            'recycling.approved'     => ['Recycling Approved', 'Your recycling submission was approved and points were awarded.', 'Reward'],
            'appointment.updated'    => ['Appointment Updated', 'Your recycling appointment status has changed.', 'Recycling'],
            'reward.redeemed'        => ['Reward Redeemed', 'Your reward redemption has been processed.', 'Reward'],
            'collection.reminder'    => ['Collection Reminder', 'Your waste collection is coming up soon.', 'Reminder'],
            'appointment.reminder'   => ['Appointment Reminder', 'Your recycling appointment is coming up soon.', 'Reminder'],
        ];

        // Notify the resident / primary user (existing behaviour)
        if (isset($map[$event]) && !empty($data['user_id'])) {
            [$title, $message, $type] = $map[$event];
            $this->persistNotification(
                (int)$data['user_id'],
                $title,
                $data['message'] ?? $message,
                $type
            );
        }

        /*
         * STAFF NOTIFICATION (Module 5 only — no other module touched):
         * When a collection is assigned, look up the CollectionRequest by entity_id
         * and traverse the ORM association to find the assigned collector,
         * then notify them directly.
         */
        if ($event === 'collection.assigned' && !empty($data['entity_id'])) {
            $collection = $this->em->find(
                \EcoBin\Entities\CollectionRequest::class,
                (int)$data['entity_id']
            );
            if ($collection && $collection->collectionStaff) {
                $this->persistNotification(
                    $collection->collectionStaff->id,
                    'New Collection Assigned',
                    'You have been assigned to a waste collection scheduled for '
                        . ($collection->scheduledDate?->format('d M Y') ?? 'TBC') . '.',
                    'Collection'
                );
            }
        }

        /*
         * OPERATOR NOTIFICATION — New Recycling Submission:
         * When a resident submits a recycling drop-off, look up the submission
         * by entity_id, traverse center->operator ORM association, and notify
         * the operator that a new submission awaits their review.
         */
        if ($event === 'recycling.submitted' && !empty($data['entity_id'])) {
            $submission = $this->em->find(
                \EcoBin\Entities\RecyclingSubmission::class,
                (int)$data['entity_id']
            );
            if ($submission && $submission->center && $submission->center->operator) {
                $this->persistNotification(
                    $submission->center->operator->id,
                    'New Recycling Submission',
                    'A new recycling submission (' . $submission->material . ', '
                        . $submission->weightKg . ' kg) has been submitted to '
                        . $submission->center->name . ' and is awaiting your review.',
                    'Recycling'
                );
            }
        }

        /*
         * OPERATOR NOTIFICATION — New Appointment Booked:
         * When a resident books a recycling appointment, look up the appointment
         * by entity_id, traverse center->operator ORM association, and notify
         * the operator of the incoming drop-off booking.
         */
        if ($event === 'recycling.appointment_created' && !empty($data['entity_id'])) {
            $appointment = $this->em->find(
                \EcoBin\Entities\RecyclingAppointment::class,
                (int)$data['entity_id']
            );
            if ($appointment && $appointment->center && $appointment->center->operator) {
                $this->persistNotification(
                    $appointment->center->operator->id,
                    'New Drop-Off Appointment',
                    'A new recycling appointment has been booked at '
                        . $appointment->center->name . ' for '
                        . $appointment->appointmentAt->format('d M Y, H:i') . '.',
                    'Recycling'
                );
            }
        }
    }

    /**
     * Persists a single Notification entity for the given user.
     * Using getReference() avoids an extra SELECT — Doctrine writes
     * the user_id FK column automatically on flush.
     */
    private function persistNotification(
        int    $userId,
        string $title,
        string $message,
        string $type
    ): void {
        $n          = new Notification();
        $n->user    = $this->em->getReference(User::class, $userId);
        $n->title   = $title;
        $n->message = $message;
        $n->type    = $type;

        $this->em->persist($n);
        $this->em->flush();
    }
}
