<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'waste_reports')]
class WasteReport
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'resident_id', type: 'integer')]
    public int $residentId;

    #[ORM\Column(length: 100)]
    public string $category;

    #[ORM\Column(type: 'text')]
    public string $description;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $image = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    public ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    public ?string $longitude = null;

    #[ORM\Column(length: 500)]
    public string $address;

    #[ORM\Column(length: 40)]
    public string $status = 'Pending';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
}
