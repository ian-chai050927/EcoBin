<?php
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\Notification;
use EcoBin\Entities\Announcement;
use EcoBin\Entities\SystemConfig;
use EcoBin\Entities\AuditLog;
use EcoBin\Entities\ActivityLog;
use EcoBin\Services\Security;

class SystemController
{
    public function __construct(private EntityManagerInterface $em, private $dispatcher) {}

    public function notifications(): void
    {
        Security::requireLogin();
        $notifications = $this->em->getRepository(Notification::class)->findBy(
            ['userId'=>(int)$_SESSION['user_id']], ['id'=>'DESC']
        );
        view('module5/notifications', ['title'=>'Notifications','notifications'=>$notifications]);
    }

    public function markRead(): void
    {
        Security::requireLogin(); Security::verifyCsrf();
        $n = $this->em->find(Notification::class, (int)($_POST['id'] ?? 0));
        if (!$n || $n->userId !== (int)$_SESSION['user_id']) exit('Forbidden');
        $n->isRead = true; $this->em->flush();
        header('Location: index.php?page=notifications'); exit;
    }

    public function admin(): void
    {
        Security::requireRole(['Admin']);
        $announcements = $this->em->getRepository(Announcement::class)->findBy([], ['id'=>'DESC']);
        $configs = $this->em->getRepository(SystemConfig::class)->findAll();
        $audits = $this->em->getRepository(AuditLog::class)->findBy([], ['id'=>'DESC'], 100);
        $activities = $this->em->getRepository(ActivityLog::class)->findBy([], ['id'=>'DESC'], 100);
        view('module5/admin', compact('announcements','configs','audits','activities') + ['title'=>'Notification & System Administration']);
    }

    public function announcement(): void
    {
        Security::requireRole(['Admin']); Security::verifyCsrf();
        $a = new Announcement();
        $a->title = mb_substr(trim($_POST['title'] ?? ''),0,150);
        $a->message = mb_substr(trim($_POST['message'] ?? ''),0,4000);
        $a->createdBy = (int)$_SESSION['user_id'];
        if ($a->title === '' || $a->message === '') exit('Required');
        $this->em->persist($a); $this->em->flush();
        $this->dispatcher->dispatch('announcement.created', ['entity'=>'Announcement','entity_id'=>$a->id]);
        Security::flash('success','Announcement created.');
        header('Location: index.php?page=module5-admin'); exit;
    }

    public function config(): void
    {
        Security::requireRole(['Admin']); Security::verifyCsrf();
        $key = preg_replace('/[^A-Za-z0-9_.-]/','', $_POST['key'] ?? '');
        $value = trim($_POST['value'] ?? '');
        if ($key === '') exit('Invalid key');
        $c = $this->em->find(SystemConfig::class, $key) ?? new SystemConfig();
        $c->key = $key; $c->value = $value; $c->updatedAt = new \DateTime();
        $this->em->persist($c); $this->em->flush();
        $this->dispatcher->dispatch('system.config_changed', ['entity'=>'SystemConfig','details'=>$key]);
        Security::flash('success','Configuration saved.');
        header('Location: index.php?page=module5-admin'); exit;
    }
}
