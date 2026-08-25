<?php
namespace EcoBin\Services;

class Security
{
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
            http_response_code(403);
            exit('403 Forbidden: insufficient permission.');
        }
    }

    public static function flash(string $key, ?string $value = null): ?string
    {
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
            return null;
        }
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}
