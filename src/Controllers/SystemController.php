<?php
namespace EcoBin\Controllers;

use EcoBin\Services\Security;
use EcoBin\Services\NotificationService;
use EcoBin\Services\AnnouncementService;
use EcoBin\Services\SystemConfigService;
use EcoBin\Services\AuditLogService;

class SystemController
{
    public function __construct(
        private NotificationService $notifications,
        private AnnouncementService $announcements,
        private SystemConfigService $config,
        private AuditLogService $auditLog,
        private $dispatcher
    ) {}

    public function notifications(): void
    {
        Security::requireLogin();
        $list = $this->notifications->listForUser((int)$_SESSION['user_id']);
        view('module5/notifications', ['title' => 'Notifications', 'notifications' => $list]);
    }

    public function markRead(): void
    {
        Security::requireLogin();
        Security::verifyCsrf();

        try {
            $this->notifications->markRead((int)($_POST['id'] ?? 0), (int)$_SESSION['user_id']);
        } catch (\RuntimeException $e) {
            Security::flash('error', $e->getMessage());
        }

        header('Location: index.php?page=notifications'); exit;
    }

    public function admin(): void
    {
        Security::requireRole(['Admin']);
        view('module5/admin', [
            'title' => 'Notification & System Administration',
            'announcements' => $this->announcements->listAll(),
            'configs' => $this->config->listAll(),
            'audits' => $this->auditLog->recentAuditLogs(),
            'activities' => $this->auditLog->recentActivityLogs(),
        ]);
    }

    public function announcement(): void
    {
        Security::requireRole(['Admin']);
        Security::verifyCsrf();

        try {
            $a = $this->announcements->create(
                $_POST['title'] ?? '',
                $_POST['message'] ?? '',
                (int)$_SESSION['user_id']
            );
            $this->dispatcher->dispatch('announcement.created', ['entity' => 'Announcement', 'entity_id' => $a->id]);
            Security::flash('success', 'Announcement created.');
        } catch (\InvalidArgumentException $e) {
            Security::flash('error', $e->getMessage());
        }

        header('Location: index.php?page=module5-admin'); exit;
    }

    public function config(): void
    {
        Security::requireRole(['Admin']);
        Security::verifyCsrf();

        try {
            $c = $this->config->set($_POST['key'] ?? '', $_POST['value'] ?? '');
            $this->dispatcher->dispatch('system.config_changed', ['entity' => 'SystemConfig', 'details' => $c->key]);
            Security::flash('success', 'Configuration saved.');
        } catch (\InvalidArgumentException $e) {
            Security::flash('error', $e->getMessage());
        }

        header('Location: index.php?page=module5-admin'); exit;
    }
}