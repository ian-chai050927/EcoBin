<?php
/*
 * @author EcoBin Team — Module 2 (Waste Collection)
 * Entity class mapped to the waste_reports table via Doctrine ORM.
 */

namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'waste_reports')]
class WasteReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;


    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: WasteReport belongs to one User (Resident)
    |--------------------------------------------------------------------------
    | Doctrine maps this to the resident_id FK column via the JoinColumn.
    | The application works with the User object directly; Doctrine manages
    | the FK column transparently.
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'resident_id', referencedColumnName: 'id', nullable: false)]
    public User $resident;


    #[ORM\Column(
        length: 100
    )]
    public string $category;


    #[ORM\Column(
        type: 'text'
    )]
    public string $description;


    /*
    |--------------------------------------------------------------------------
    | First / Main Waste Image
    |--------------------------------------------------------------------------
    |
    | This is kept for compatibility with the existing EcoBin database
    | and existing code.
    |
    */
    #[ORM\Column(
        length: 255,
        nullable: true
    )]
    public ?string $image = null;


    /*
    |--------------------------------------------------------------------------
    | Waste Priority
    |--------------------------------------------------------------------------
    |
    | Used by Module 2 Admin Collection Operations.
    |
    | Low
    | Normal
    | High
    | Urgent
    |
    */
    #[ORM\Column(
        length: 20
    )]
    public string $priority = 'Normal';


    /*
    |--------------------------------------------------------------------------
    | Waste Size
    |--------------------------------------------------------------------------
    |
    | Helps Collection Staff prepare appropriate equipment.
    |
    | Small
    | Medium
    | Large
    | Extra Large
    |
    */
    #[ORM\Column(
        name: 'waste_size',
        length: 30
    )]
    public string $wasteSize = 'Medium';


    /*
    |--------------------------------------------------------------------------
    | GPS Latitude
    |--------------------------------------------------------------------------
    */
    #[ORM\Column(
        type: 'decimal',
        precision: 10,
        scale: 7,
        nullable: true
    )]
    public ?string $latitude = null;


    /*
    |--------------------------------------------------------------------------
    | GPS Longitude
    |--------------------------------------------------------------------------
    */
    #[ORM\Column(
        type: 'decimal',
        precision: 10,
        scale: 7,
        nullable: true
    )]
    public ?string $longitude = null;


    /*
    |--------------------------------------------------------------------------
    | Collection Address
    |--------------------------------------------------------------------------
    */
    #[ORM\Column(
        length: 500
    )]
    public string $address;


    /*
    |--------------------------------------------------------------------------
    | Waste Report Status
    |--------------------------------------------------------------------------
    |
    | Pending
    | Assigned
    | In Progress
    | Completed
    | Cancelled
    |
    */
    #[ORM\Column(
        length: 40
    )]
    public string $status = 'Pending';


    /*
    |--------------------------------------------------------------------------
    | Creation Timestamp
    |--------------------------------------------------------------------------
    */
    #[ORM\Column(
        name: 'created_at',
        type: 'datetime'
    )]
    public \DateTime $createdAt;


    public function __construct()
    {
        $this->createdAt =
            new \DateTime();
    }

    /**
     * Convenience getter — returns the resident's integer ID from the
     * ORM association. Avoids breaking code that needs the raw FK value.
     */
    public function getResidentId(): ?int
    {
        return $this->resident->id ?? null;
    }
}