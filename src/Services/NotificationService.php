<?php

namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\Notification;
use EcoBin\Entities\User;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }


    public function listForUser(int $userId): array
    {

        return $this->em->getRepository(Notification::class)
            ->findBy(['user' => $userId], ['id' => 'DESC']);
    }

    public function create(int $userId, string $title, string $message, string $type = 'System'): Notification
    {
        $n = new Notification();


        $n->user    = $this->em->getReference(User::class, $userId);
        $n->title   = mb_substr($title, 0, 120);
        $n->message = mb_substr($message, 0, 4000);
        $n->type    = mb_substr($type, 0, 50);

        $this->em->persist($n);
        $this->em->flush();

        return $n;
    }

    public function markRead(int $notificationId, int $requestingUserId): void
    {
        $n = $this->em->find(Notification::class, $notificationId);
        if (!$n) {
            throw new \RuntimeException('Notification not found.');
        }


        if ($n->user->id !== $requestingUserId) {
            throw new \RuntimeException('Forbidden: not your notification.');
        }
        $n->isRead = true;
        $this->em->flush();
    }
}