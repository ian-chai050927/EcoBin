<?php
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\User;
use EcoBin\Services\Security;
use EcoBin\Services\Mailer;

class AuthController
{
    public function __construct(
        private EntityManagerInterface $em,
        private array $app,
        private $dispatcher
    ) {}

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !password_verify($password, $user->passwordHash)) {
                Security::flash('error', 'Invalid email or password.');
                header('Location: index.php?page=login');
                exit;
            }

            if ($user->status !== 'Active') {
                Security::flash('error', 'This account is suspended.');
                header('Location: index.php?page=login');
                exit;
            }

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user->id;
            $_SESSION['name'] = $user->name;
            $_SESSION['role'] = $user->role;

            $this->dispatcher->dispatch('auth.login', [
                'entity' => 'User',
                'entity_id' => $user->id
            ]);

            header('Location: index.php');
            exit;
        }

        view('auth/login', ['title' => 'Login']);
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $name = trim($_POST['name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                Security::flash('error', 'Use a valid name/email and a password of at least 8 characters.');
                header('Location: index.php?page=register');
                exit;
            }

            if ($this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
                Security::flash('error', 'Email already registered.');
                header('Location: index.php?page=register');
                exit;
            }

            $u = new User();
            $u->name = $name;
            $u->email = $email;
            $u->passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $u->role = 'Resident';
            $u->verificationToken = bin2hex(random_bytes(24));

            $this->em->persist($u);
            $this->em->flush();

            $verifyUrl = $this->app['base_url']
                . '/index.php?page=verify&token='
                . urlencode($u->verificationToken);

            (new Mailer($this->app['mail']))->send(
                $u->email,
                'Verify your EcoBin email',
                'Verification link: ' . $verifyUrl
            );

            $this->dispatcher->dispatch('auth.register', [
                'entity' => 'User',
                'entity_id' => $u->id
            ]);

            Security::flash(
                'success',
                'Registration successful. For local demo, verification email is written to storage/mail.log.'
            );

            header('Location: index.php?page=login');
            exit;
        }

        view('auth/register', ['title' => 'Register']);
    }

    public function verify(): void
    {
        $token = $_GET['token'] ?? '';

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['verificationToken' => $token]);

        if ($user) {
            $user->emailVerifiedAt = new \DateTime();
            $user->verificationToken = null;
            $this->em->flush();
            Security::flash('success', 'Email verified successfully.');
        } else {
            Security::flash('error', 'Invalid verification token.');
        }

        header('Location: index.php?page=login');
        exit;
    }

    public function forgot(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $email = strtolower(trim($_POST['email'] ?? ''));

            $user = $this->em->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($user) {
                $user->resetToken = bin2hex(random_bytes(24));
                $user->resetExpiresAt = new \DateTime('+30 minutes');

                $this->em->flush();

                $url = $this->app['base_url']
                    . '/index.php?page=reset&token='
                    . urlencode($user->resetToken);

                (new Mailer($this->app['mail']))->send(
                    $user->email,
                    'EcoBin password reset',
                    'Reset link: ' . $url
                );
            }

            Security::flash('success', 'If the email exists, a reset message has been generated.');
            header('Location: index.php?page=login');
            exit;
        }

        view('auth/forgot', ['title' => 'Forgot Password']);
    }

    public function reset(): void
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['resetToken' => $token]);

        if (!$user || !$user->resetExpiresAt || $user->resetExpiresAt < new \DateTime()) {
            exit('Reset token is invalid or expired.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $password = $_POST['password'] ?? '';

            if (strlen($password) < 8) {
                exit('Password must be at least 8 characters.');
            }

            $user->passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $user->resetToken = null;
            $user->resetExpiresAt = null;

            $this->em->flush();

            Security::flash('success', 'Password reset successful.');
            header('Location: index.php?page=login');
            exit;
        }

        view('auth/reset', [
            'title' => 'Reset Password',
            'token' => $token
        ]);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        header('Location: index.php?page=login');
        exit;
    }

    // CLIENT SIDE: each logged-in user manages only their own profile.
    public function profile(): void
    {
        Security::requireLogin();

        $user = $this->em->find(User::class, (int)$_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            $name = trim($_POST['name'] ?? '');

            if ($name !== '') {
                $user->name = $name;
                $_SESSION['name'] = $name;

                $this->em->flush();

                $this->dispatcher->dispatch('user.profile_updated', [
                    'entity' => 'User',
                    'entity_id' => $user->id
                ]);

                Security::flash('success', 'Profile updated.');
            }

            header('Location: index.php?page=profile');
            exit;
        }

        view('auth/profile', [
            'title' => 'My Account',
            'user' => $user
        ]);
    }

    // ADMIN SIDE: list/search/filter all accounts.
    public function users(): void
    {
        Security::requireRole(['Admin']);

        $q = trim($_GET['q'] ?? '');
        $role = trim($_GET['role'] ?? '');

        $users = $this->em->getRepository(User::class)
            ->findBy([], ['id' => 'ASC']);

        if ($q !== '') {
            $users = array_values(array_filter(
                $users,
                function (User $u) use ($q) {
                    $haystack = strtolower($u->name . ' ' . $u->email);
                    return str_contains($haystack, strtolower($q));
                }
            ));
        }

        if ($role !== '') {
            $users = array_values(array_filter(
                $users,
                fn(User $u) => $u->role === $role
            ));
        }

        view('auth/users', [
            'title' => 'User Management',
            'users' => $users,
            'query' => $q,
            'roleFilter' => $role
        ]);
    }

    // ADMIN SIDE: create operational/admin accounts.
    public function createUser(): void
    {
        Security::requireRole(['Admin']);
        Security::verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'Resident';

        $roles = [
            'Resident',
            'Admin',
            'Collection Staff',
            'Recycling Center Operator'
        ];

        if (
            $name === ''
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($password) < 8
            || !in_array($role, $roles, true)
        ) {
            Security::flash('error', 'Invalid user information.');
            header('Location: index.php?page=users');
            exit;
        }

        if ($this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
            Security::flash('error', 'Email already exists.');
            header('Location: index.php?page=users');
            exit;
        }

        $u = new User();
        $u->name = $name;
        $u->email = $email;
        $u->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $u->role = $role;
        $u->status = 'Active';
        $u->emailVerifiedAt = new \DateTime();

        $this->em->persist($u);
        $this->em->flush();

        $this->dispatcher->dispatch('user.created_by_admin', [
            'entity' => 'User',
            'entity_id' => $u->id,
            'role' => $u->role
        ]);

        Security::flash('success', 'User account created.');
        header('Location: index.php?page=users');
        exit;
    }

    // ADMIN SIDE: edit role and account status.
    public function updateUser(): void
    {
        Security::requireRole(['Admin']);
        Security::verifyCsrf();

        $id = (int)($_POST['id'] ?? 0);

        $user = $this->em->find(User::class, $id);

        if (!$user) {
            exit('User not found.');
        }

        $roles = [
            'Resident',
            'Admin',
            'Collection Staff',
            'Recycling Center Operator'
        ];

        $statuses = [
            'Active',
            'Suspended'
        ];

        $name = trim($_POST['name'] ?? '');
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? '';

        if (
            $name === ''
            || !in_array($role, $roles, true)
            || !in_array($status, $statuses, true)
        ) {
            exit('Invalid account update.');
        }

        if ($user->id === (int)$_SESSION['user_id'] && $status === 'Suspended') {
            Security::flash('error', 'You cannot suspend your own logged-in account.');
            header('Location: index.php?page=users');
            exit;
        }

        $user->name = $name;
        $user->role = $role;
        $user->status = $status;

        $this->em->flush();

        $this->dispatcher->dispatch('user.account_updated', [
            'entity' => 'User',
            'entity_id' => $user->id,
            'role' => $role,
            'status' => $status
        ]);

        Security::flash('success', 'User account updated.');
        header('Location: index.php?page=users');
        exit;
    }

    // Backward-compatible route.
    public function changeStatus(): void
    {
        $this->updateUser();
    }
}
