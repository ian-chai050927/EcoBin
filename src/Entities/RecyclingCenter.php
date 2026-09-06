<?php
/**
 * Module 3: Recycling & Reward Management
 * @author Ho Ze-Yang
 */

namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'recycling_centers')]
class RecyclingCenter
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: RecyclingCenter is managed by one User (Operator)
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'operator_id', referencedColumnName: 'id', nullable: false)]
    public User $operator;

    #[ORM\Column(length: 120)]
    public string $name;

    #[ORM\Column(length: 500)]
    public string $address;

    #[ORM\Column(name: 'accepted_materials', length: 255)]
    public string $acceptedMaterials;

    #[ORM\Column(length: 30)]
    public string $availability = 'Open';

    #[ORM\Column(name: 'operating_hours', length: 120, nullable: true)]
    public ?string $operatingHours = 'Mon - Fri: 9:00 AM - 5:00 PM';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
