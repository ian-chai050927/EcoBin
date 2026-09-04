<?php
/*
 * @author EcoBin Team — Module 5 (Notifications & System)
 * Observer that creates in-app Notification records for relevant events.
 * Uses $em->getReference() to assign the ORM User association without
 * issuing an extra SELECT query.
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

        if (!isset($map[$event]) || empty($data['user_id'])) return;

        [$title, $message, $type] = $map[$event];
        $n = new Notification();

        /*
         * ORM RELATIONSHIP USAGE:
         * Instead of writing $n->userId = 123 (raw FK integer), we assign a
         * Doctrine proxy object via getReference(). Doctrine resolves the FK
         * column automatically when persisting — no extra SELECT is executed.
         */
        $n->user    = $this->em->getReference(User::class, (int)$data['user_id']);
        $n->title   = $title;
        $n->message = $data['message'] ?? $message;
        $n->type    = $type;

        $this->em->persist($n);
        $this->em->flush();
    }
}
