<?php
namespace EcoBin\Observers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Contracts\EventObserver;
use EcoBin\Entities\AuditLog;
use EcoBin\Entities\ActivityLog;

class AuditObserver implements EventObserver
{
    public function __construct(private EntityManagerInterface $em) {}

    public function update(string $event, array $data): void
    {
        $audit = new AuditLog();
        $audit->userId = $_SESSION['user_id'] ?? null;
        $audit->action = $event;
        $audit->entity = $data['entity'] ?? 'System';
        $audit->entityId = isset($data['entity_id']) ? (int)$data['entity_id'] : null;
        $audit->details = json_encode($data, JSON_UNESCAPED_UNICODE);
        $audit->ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

        $activity = new ActivityLog();
        $activity->userId = $_SESSION['user_id'] ?? null;
        $activity->activity = $event;

        $this->em->persist($audit);
        $this->em->persist($activity);
        $this->em->flush();
    }
}
