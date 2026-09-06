<?php
/**
 * Module 3: Recycling & Reward Management
 * @author Ho Ze-Yang
 */

namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'recycling_appointments')]
class RecyclingAppointment
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: RecyclingAppointment belongs to one User (Resident)
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'resident_id', referencedColumnName: 'id', nullable: false)]
    public User $resident;

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: RecyclingAppointment belongs to one RecyclingCenter
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: RecyclingCenter::class)]
    #[ORM\JoinColumn(name: 'center_id', referencedColumnName: 'id', nullable: false)]
    public RecyclingCenter $center;

    #[ORM\Column(name: 'appointment_at', type: 'datetime')]
    public \DateTime $appointmentAt;

    #[ORM\Column(length: 30)]
    public string $status = 'Pending';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
