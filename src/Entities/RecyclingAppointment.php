<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'recycling_appointments')]
class RecyclingAppointment
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'resident_id', type: 'integer')]
    public int $residentId;

    #[ORM\Column(name: 'center_id', type: 'integer')]
    public int $centerId;

    #[ORM\Column(name: 'appointment_at', type: 'datetime')]
    public \DateTime $appointmentAt;

    #[ORM\Column(length: 30)]
    public string $status = 'Pending';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct() { $this->createdAt = new \DateTime(); }
}
