<?php
/*
 * @author EcoBin Team — Module 1 (User Authentication & Management)
 * Entity class mapped to the users table via Doctrine ORM.
 * Inverse OneToMany associations are declared here for Doctrine metadata
 * completeness and for demonstrating ORM relationship navigation.
 */

namespace EcoBin\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /*
    |--------------------------------------------------------------------------
    | ORM Inverse Relationship: One User has many CollectionRequests (Resident)
    |--------------------------------------------------------------------------
    | This is the inverse side of CollectionRequest::$resident.
    | Used to navigate from a User to all their submitted collection requests.
    */
    #[ORM\OneToMany(mappedBy: 'resident', targetEntity: CollectionRequest::class)]
    public Collection $collectionRequests;

    /*
    |--------------------------------------------------------------------------
    | ORM Inverse Relationship: One User has many RecyclingSubmissions
    |--------------------------------------------------------------------------
    */
    #[ORM\OneToMany(mappedBy: 'resident', targetEntity: RecyclingSubmission::class)]
    public Collection $recyclingSubmissions;

    /*
    |--------------------------------------------------------------------------
    | ORM Inverse Relationship: One User (Operator) has many RecyclingCenters
    |--------------------------------------------------------------------------
    */
    #[ORM\OneToMany(mappedBy: 'operator', targetEntity: RecyclingCenter::class)]
    public Collection $recyclingCenters;

    /*
    |--------------------------------------------------------------------------
    | ORM Inverse Relationship: One User has many Notifications
    |--------------------------------------------------------------------------
    */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class)]
    public Collection $notifications;

    public function __construct()
    {
        $this->createdAt           = new \DateTime();
        $this->collectionRequests  = new ArrayCollection();
        $this->recyclingSubmissions = new ArrayCollection();
        $this->recyclingCenters    = new ArrayCollection();
        $this->notifications       = new ArrayCollection();
    }
}
