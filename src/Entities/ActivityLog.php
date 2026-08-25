<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activity_logs')]
class ActivityLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: true)]
    public ?int $userId = null;

    #[ORM\Column(length: 100)]
    public string $activity;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
