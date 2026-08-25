<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'collection_requests')]
class CollectionRequest
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'waste_report_id', type: 'integer', unique: true)]
    public int $wasteReportId;

    #[ORM\Column(name: 'resident_id', type: 'integer')]
    public int $residentId;

    #[ORM\Column(name: 'preferred_date', type: 'date')]
    public \DateTime $preferredDate;

    #[ORM\Column(name: 'scheduled_date', type: 'date', nullable: true)]
    public ?\DateTime $scheduledDate = null;

    #[ORM\Column(name: 'collection_staff_id', type: 'integer', nullable: true)]
    public ?int $collectionStaffId = null;

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
