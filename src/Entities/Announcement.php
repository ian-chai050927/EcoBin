<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'announcements')]
class Announcement
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(length: 150)]
    public string $title;

    #[ORM\Column(type: 'text')]
    public string $message;

    #[ORM\Column(name: 'created_by', type: 'integer')]
    public int $createdBy;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
