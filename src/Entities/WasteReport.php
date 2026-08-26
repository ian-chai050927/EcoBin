<?php

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


    #[ORM\Column(
        name: 'resident_id',
        type: 'integer'
    )]
    public int $residentId;


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
}