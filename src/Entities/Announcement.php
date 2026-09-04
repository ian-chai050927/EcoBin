<?php
/*
 * @author EcoBin Team — Module 5 (Notifications & System)
 * Entity mapped to announcements table via Doctrine ORM.
 * The author relationship uses ManyToOne to User.
 */

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

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: Announcement is authored by one User (Admin)
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    public User $author;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
