<?php
/*
 * @author EcoBin Team — Module 5 (Notifications & System)
 * Service for managing Announcement records.
 * Uses the ORM $author association instead of a raw createdBy integer.
 */

namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\Announcement;
use EcoBin\Entities\User;

class AnnouncementService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function listAll(int $limit = 0): array
    {
        return $limit > 0
            ? $this->em->getRepository(Announcement::class)->findBy([], ['id' => 'DESC'], $limit)
            : $this->em->getRepository(Announcement::class)->findBy([], ['id' => 'DESC']);
    }

    public function create(string $title, string $message, int $createdBy): Announcement
    {
        $title   = trim($title);
        $message = trim($message);
        if ($title === '' || $message === '') {
            throw new \InvalidArgumentException('Title and message are required.');
        }

        $a = new Announcement();
        $a->title   = mb_substr($title, 0, 150);
        $a->message = mb_substr($message, 0, 4000);

        /*
         * ORM RELATIONSHIP USAGE:
         * Assign the author (User) via getReference() so Doctrine writes the
         * created_by FK column on flush without loading the full User entity.
         */
        $a->author = $this->em->getReference(User::class, $createdBy);

        $this->em->persist($a);
        $this->em->flush();

        return $a;
    }
}