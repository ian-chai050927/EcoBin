<?php
namespace EcoBin\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(length: 100)]
    public string $name;

    #[ORM\Column(length: 150, unique: true)]
    public string $email;

    #[ORM\Column(name: 'password_hash', length: 255)]
    public string $passwordHash;

    #[ORM\Column(length: 40)]
    public string $role = 'Resident';

    #[ORM\Column(length: 20)]
    public string $status = 'Active';

    #[ORM\Column(name: 'email_verified_at', type: 'datetime', nullable: true)]
    public ?\DateTime $emailVerifiedAt = null;

    #[ORM\Column(name: 'verification_token', length: 100, nullable: true)]
    public ?string $verificationToken = null;

    #[ORM\Column(name: 'reset_token', length: 100, nullable: true)]
    public ?string $resetToken = null;

    #[ORM\Column(name: 'reset_expires_at', type: 'datetime', nullable: true)]
    public ?\DateTime $resetExpiresAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
}
