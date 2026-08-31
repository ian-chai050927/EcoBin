<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'recycling_submissions')]
class RecyclingSubmission
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'resident_id', type: 'integer')]
    public int $residentId;

    #[ORM\Column(name: 'center_id', type: 'integer')]
    public int $centerId;

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
