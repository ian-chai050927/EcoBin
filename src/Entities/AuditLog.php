<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_logs')]
class AuditLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: true)]
    public ?int $userId = null;

    #[ORM\Column(length: 80)]
    public string $action;

    #[ORM\Column(length: 80)]
    public string $entity;

    #[ORM\Column(name: 'entity_id', type: 'integer', nullable: true)]
    public ?int $entityId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $details = null;

    #[ORM\Column(name: 'ip_address', length: 60)]
    public string $ipAddress;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
