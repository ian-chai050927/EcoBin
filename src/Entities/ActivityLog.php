<?php
/*
 * @author EcoBin Team — Module 5 (Notifications & System)
 * Entity mapped to activity_logs table via Doctrine ORM.
 * Nullable ManyToOne association to User (can be CLI/system).
 */

namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activity_logs')]
class ActivityLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: ActivityLog optionally belongs to one User
    | Nullable because system/CLI events have no associated user.
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    public ?User $user = null;

    #[ORM\Column(length: 100)]
    public string $activity;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
