<?php
namespace EcoBin\Factories;

use EcoBin\Entities\User;

class UserFactory
{
    public static function createUser(string $role, string $name, string $email, string $password): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user->role = $role;
        
        // Set role-specific defaults
        if ($role === 'Resident') {
            $user->status = 'Active';
            $user->verificationToken = bin2hex(random_bytes(24));
        } else {
            $user->status = 'Active';
            $user->emailVerifiedAt = new \DateTime();
        }

        return $user;
    }
}