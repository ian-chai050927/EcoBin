<?php
/*
 * @author  Jordan Liew Yi Xiang
 * @module  Module 2 — Waste Report & Collection Management
 */

namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'collection_requests')]
class CollectionRequest
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;


    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: CollectionRequest belongs to one WasteReport
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: WasteReport::class)]
    #[ORM\JoinColumn(name: 'waste_report_id', referencedColumnName: 'id', nullable: false, unique: true)]
    public WasteReport $wasteReport;


    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: CollectionRequest belongs to one User (Resident)
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'resident_id', referencedColumnName: 'id', nullable: false)]
    public User $resident;


    #[ORM\Column(name: 'preferred_date', type: 'date')]
    public \DateTime $preferredDate;

    #[ORM\Column(name: 'scheduled_date', type: 'date', nullable: true)]
    public ?\DateTime $scheduledDate = null;


    /*
    |--------------------------------------------------------------------------
    | ORM Relationship: CollectionRequest optionally assigned to one User (Staff)
    |--------------------------------------------------------------------------
    */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'collection_staff_id', referencedColumnName: 'id', nullable: true)]
    public ?User $collectionStaff = null;


    #[ORM\Column(length: 40)]
    public string $status = 'Pending';

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $remarks = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
}
