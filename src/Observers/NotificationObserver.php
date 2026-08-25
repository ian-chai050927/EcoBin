<?php
namespace EcoBin\Observers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Contracts\EventObserver;
use EcoBin\Entities\Notification;

class NotificationObserver implements EventObserver
{
    public function __construct(private EntityManagerInterface $em) {}

    public function update(string $event, array $data): void
    {
        $map = [
            'collection.assigned' => ['Collection Scheduled', 'Your waste collection has been assigned.', 'Collection'],
            'collection.in_progress' => ['Collection In Progress', 'The collector has started your collection.', 'Collection'],
            'collection.completed' => ['Collection Completed', 'Your waste collection has been completed.', 'Collection'],
            'recycling.approved' => ['Recycling Approved', 'Your recycling submission was approved and points were awarded.', 'Reward'],
            'appointment.updated' => ['Appointment Updated', 'Your recycling appointment status has changed.', 'Recycling'],
        ];

        if (!isset($map[$event]) || empty($data['user_id'])) return;

        [$title, $message, $type] = $map[$event];
        $n = new Notification();
        $n->userId = (int)$data['user_id'];
        $n->title = $title;
        $n->message = $data['message'] ?? $message;
        $n->type = $type;

        $this->em->persist($n);
        $this->em->flush();
    }
}
