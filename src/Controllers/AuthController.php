<?php
/**
 * Module 1: User Authentication and Role Management
 * @author Chai Ee Yuan
 */
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\User;
use EcoBin\Services\Security;
use EcoBin\Services\Mailer;
use EcoBin\Services\InternalApiClient;
use EcoBin\Factories\UserFactory;

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

            if (!$user->emailVerifiedAt) {
                Security::flash('error', 'Please verify your email before logging in. Check your inbox for the verification link.');
                header('Location: index.php?page=login');
                exit;
            }

            if ($user->status !== 'Active') {
                Security::flash('error', 'Your account has been ' . strtolower($user->status) . '. Please contact an administrator.');
                header('Location: index.php?page=login');
                exit;
            }

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user->id;
            $_SESSION['name'] = $user->name;
            $_SESSION['role'] = $user->role;

            $this->dispatcher->dispatch('auth.login', [
                'entity'    => 'User',
                'entity_id' => $user->id,
                'role'      => $user->role,   // e.g. Resident / Admin / Collection Staff / Operator
                'name'      => $user->name,
                'email'     => $user->email,
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

            $u = UserFactory::createUser('Resident', $name, $email, $password);

            $this->em->persist($u);
            $this->em->flush();

            $verifyUrl = $this->app['base_url']
                . '/index.php?page=verify&token='
                . urlencode($u->verificationToken);

            $request = [
                'requestID' => uniqid('req_', true),
                'timestamp' => (new \DateTime())->format('c'),
                'service'   => 'notification.email',
                'payload'   => [
                    'email'   => $u->email,
                    'subject' => 'Verify your EcoBin email',
                    'message' => '<p>Click to verify your account: <a href="' . htmlspecialchars($verifyUrl) . '">' . htmlspecialchars($verifyUrl) . '</a></p>',
                ],
            ];
            $payload = json_encode($request);

            $ch = curl_init($this->app['base_url'] . '/api.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Service-Token: ' . $this->app['service_token'],
                'Content-Length: ' . strlen($payload),
            ]);

            curl_exec($ch);
            curl_close($ch);

            $this->dispatcher->dispatch('auth.register', [
                'entity' => 'User',
                'entity_id' => $u->id
            ]);

            Security::flash(
                'success',
                'Registration successful. Please check your email to verify your account.'
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            view('auth/forgot', ['title' => 'Forgot Password']);
            return;
        }

        Security::verifyCsrf();

        $email = strtolower(trim($_POST['email'] ?? ''));

        // Always show success to prevent email enumeration
        $successMsg = 'If that email is registered, a password reset link has been sent.';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Security::flash('success', $successMsg);
            header('Location: index.php?page=forgot'); exit;
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {

            $token   = bin2hex(random_bytes(32));
            $expires = new \DateTime('+1 hour');

            $user->resetToken     = $token;
            $user->resetExpiresAt = $expires;
            $this->em->flush();

            // Build the reset URL
            $resetUrl = ($this->app['base_url'] ?? '')
                . '/index.php?page=reset&token=' . urlencode($token);


            $html = '
                <div style="font-family:Inter,sans-serif;max-width:520px;margin:auto;padding:32px;background:#fff;border-radius:12px;border:1px solid #e5ebe7;">
                    <div style="font-size:28px;font-weight:700;color:#1b7f4f;margin-bottom:8px;">&#127807; EcoBin</div>
                    <h2 style="color:#1a1a2e;margin-top:0;">Password Reset Request</h2>
                    <p style="color:#555;">Hi <strong>' . htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
                    <p style="color:#555;">We received a request to reset your EcoBin password. Click the button below to choose a new one. This link expires in <strong>1 hour</strong>.</p>
                    <div style="text-align:center;margin:32px 0;">
                        <a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '"
                           style="background:#1b7f4f;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:600;font-size:16px;display:inline-block;">
                            Reset My Password
                        </a>
                    </div>
                    <p style="color:#888;font-size:13px;">If you did not request this, you can safely ignore this email.</p>
                    <hr style="border:none;border-top:1px solid #e5ebe7;margin:24px 0;">
                    <p style="color:#aaa;font-size:12px;text-align:center;">EcoBin &mdash; Waste Collection &amp; Recycling Management</p>
                </div>';

            // Send reset email via Mailer service (primary)
            // Falls back gracefully — on local XAMPP, mail goes to storage/mail.log
            $mailer = new Mailer($this->app['mail'] ?? []);
            $mailer->send($user->email, 'EcoBin — Reset Your Password', $html);

            // Also notify via Module 5 web service (best-effort, non-blocking)
            try {
                $client = new InternalApiClient($this->app['base_url'], $this->app['service_token']);
                $client->call('notification.email', [
                    'email'   => $user->email,
                    'subject' => 'EcoBin Reset Your Password',
                    'message' => $html,
                ]);
            } catch (\Throwable $e) {
                error_log('notification.email service unavailable during password reset: ' . $e->getMessage());
            }

            $this->dispatcher->dispatch('auth.password_reset_requested', [
                'entity' => 'User', 'entity_id' => $user->id,
            ]);
        }

        // Email enumeration prevention: same message either way
        Security::flash('success', $successMsg);
        header('Location: index.php?page=forgot'); exit;
    }

    // -------------------------------------------------------------------------
    // Reset Password — Step 2
    // GET  : validate token, show new-password form
    // POST : verify token + expiry, hash new password, clear token (single-use)
    //
    // SECURITY:
    //   Token validated against DB — not just any string.
    //   Expiry enforced (1 hour). Expired tokens cleared for hygiene.
    //   Token is single-use: nulled immediately after success.
    //   Confirm-password match validated server-side.
    // -------------------------------------------------------------------------
    public function reset(): void
    {
        $token = trim($_GET['token'] ?? $_POST['token'] ?? '');

        if ($token === '') {
            Security::flash('error', 'Invalid or missing reset token.');
            header('Location: index.php?page=forgot'); exit;
        }

        // ORM lookup by token — no raw SQL
        $user = $this->em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            Security::flash('error', 'This reset link is invalid. Please request a new one.');
            header('Location: index.php?page=forgot'); exit;
        }

        // Check expiry
        if ($user->resetExpiresAt === null || $user->resetExpiresAt < new \DateTime()) {
            $user->resetToken     = null;
            $user->resetExpiresAt = null;
            $this->em->flush();
            Security::flash('error', 'This reset link has expired. Please request a new one.');
            header('Location: index.php?page=forgot'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            view('auth/reset', ['title' => 'Reset Password', 'token' => $token]);
            return;
        }

        Security::verifyCsrf();

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 8) {
            Security::flash('error', 'Password must be at least 8 characters.');
            header('Location: index.php?page=reset&token=' . urlencode($token)); exit;
        }

        if ($password !== $confirm) {
            Security::flash('error', 'Passwords do not match.');
            header('Location: index.php?page=reset&token=' . urlencode($token)); exit;
        }

        // ORM UPDATE: hash new password and clear the single-use token
        $user->passwordHash   = password_hash($password, PASSWORD_DEFAULT);
        $user->resetToken     = null;
        $user->resetExpiresAt = null;
        $this->em->flush();

        $this->dispatcher->dispatch('auth.password_reset_completed', [
            'entity' => 'User', 'entity_id' => $user->id,
        ]);

        Security::flash('success', 'Your password has been reset. You can now log in.');
        header('Location: index.php?page=login'); exit;
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

        
        $u = UserFactory::createUser($role, $name, $email, $password);

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
            'Suspended',
            'Deactivated'
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

        if ($user->id === (int)$_SESSION['user_id'] && $status !== 'Active') {
            Security::flash('error', 'You cannot suspend or deactivate your own logged-in account.');
            header('Location: index.php?page=users');
            exit;
        }

        $user->name   = $name;
        $user->role   = $role;
        $user->status = $status;

        $this->em->flush();

        $this->dispatcher->dispatch('user.account_updated', [
            'entity'    => 'User',
            'entity_id' => $user->id,
            'role'      => $role,
            'status'    => $status,
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
