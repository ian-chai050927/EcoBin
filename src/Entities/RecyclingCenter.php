<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'recycling_centers')]
class RecyclingCenter
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'operator_id', type: 'integer')]
    public int $operatorId;

    #[ORM\Column(length: 120)]
    public string $name;

    #[ORM\Column(length: 500)]
    public string $address;

    #[ORM\Column(name: 'accepted_materials', length: 255)]
    public string $acceptedMaterials;

    #[ORM\Column(length: 30)]
    public string $availability = 'Open';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
