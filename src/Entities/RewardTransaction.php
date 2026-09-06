<?php
/**
 * Module 3: Recycling & Reward Management
 * @author Ho Ze-Yang
 */

namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'reward_transactions')]
class RewardTransaction
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: RewardTransaction belongs to one User
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    public User $user;

    #[ORM\Column(type: 'integer')]
    public int $points;

    #[ORM\Column(length: 40)]
    public string $type;

    #[ORM\Column(length: 255)]
    public string $description;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
