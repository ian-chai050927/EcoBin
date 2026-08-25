<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notifications')]
class Notification
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    public int $userId;

    #[ORM\Column(length: 120)]
    public string $title;

    #[ORM\Column(type: 'text')]
    public string $message;

    #[ORM\Column(length: 50)]
    public string $type = 'System';

    #[ORM\Column(name: 'is_read', type: 'boolean')]
    public bool $isRead = false;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
