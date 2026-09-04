<?php
/*
 * @author EcoBin Team — Module 3 (Recycling & Rewards)
 * Entity mapped to recycling_submissions table via Doctrine ORM.
 * Uses ManyToOne associations for resident (User) and center (RecyclingCenter).
 */

namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'recycling_submissions')]
class RecyclingSubmission
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: RecyclingSubmission belongs to one User (Resident)
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'resident_id', referencedColumnName: 'id', nullable: false)]
    public User $resident;

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: RecyclingSubmission belongs to one RecyclingCenter
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: RecyclingCenter::class)]
    #[ORM\JoinColumn(name: 'center_id', referencedColumnName: 'id', nullable: false)]
    public RecyclingCenter $center;

    #[ORM\Column(length: 80)]
    public string $material;

    #[ORM\Column(name: 'weight_kg', type: 'decimal', precision: 8, scale: 2)]
    public string $weightKg;

    #[ORM\Column(type: 'integer')]
    public int $points = 0;

    #[ORM\Column(length: 30)]
    public string $status = 'Pending';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
