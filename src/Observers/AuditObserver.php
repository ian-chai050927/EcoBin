<?php
/*
 * @author EcoBin Team — Module 5 (Notifications & System)
 * Observer that writes AuditLog and ActivityLog records on every system event.
 * Uses $em->getReference() to assign the ORM User association without
 * issuing extra SELECT queries.
 */

namespace EcoBin\Observers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Contracts\EventObserver;
use EcoBin\Entities\AuditLog;
use EcoBin\Entities\ActivityLog;
use EcoBin\Entities\User;

class AuditObserver implements EventObserver
{
    public function __construct(private EntityManagerInterface $em) {}

    public function update(string $event, array $data): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        $audit = new AuditLog();

        /*
         * ORM RELATIONSHIP USAGE:
         * Assign the User association via getReference() when a user ID is
         * available. This creates a proxy without a SELECT, satisfying the
         * ORM relationship requirement efficiently.
         */
        $audit->user     = $userId ? $this->em->getReference(User::class, (int)$userId) : null;
        $audit->action   = $event;
        $audit->entity   = $data['entity'] ?? 'System';
        $audit->entityId = isset($data['entity_id']) ? (int)$data['entity_id'] : null;
        $audit->details  = json_encode($data, JSON_UNESCAPED_UNICODE);
        $audit->ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

        $activity = new ActivityLog();
        $activity->user     = $userId ? $this->em->getReference(User::class, (int)$userId) : null;
        $activity->activity = $event;

        $this->em->persist($audit);
        $this->em->persist($activity);
        $this->em->flush();
    }
}
