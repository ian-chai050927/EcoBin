<?php
namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\Announcement;

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
        $title = trim($title);
        $message = trim($message);
        if ($title === '' || $message === '') {
            throw new \InvalidArgumentException('Title and message are required.');
        }

        $a = new Announcement();
        $a->title = mb_substr($title, 0, 150);
        $a->message = mb_substr($message, 0, 4000);
        $a->createdBy = $createdBy;

        $this->em->persist($a);
        $this->em->flush();

        return $a;
    }
}