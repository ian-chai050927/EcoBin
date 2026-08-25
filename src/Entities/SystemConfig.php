<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'system_config')]
class SystemConfig
{
    #[ORM\Id, ORM\Column(name: 'config_key', length: 80)]
    public string $key;

    #[ORM\Column(name: 'config_value', type: 'text')]
    public string $value;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    public \DateTime $updatedAt;

    public function __construct() { $this->updatedAt = new \DateTime(); }
}
