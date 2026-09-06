<?php
/**
 * Module 5 :Notification & System Administration
 * @author Tang Yik Hong
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

        /*
         * For login events, use the ID from the event payload (session isn't
         * regenerated yet when auth.login fires on first login).
         */
        if ($event === 'auth.login' && isset($data['entity_id'])) {
            $userId = $data['entity_id'];
        }


        $role       = $data['role'] ?? ($_SESSION['role'] ?? null);
        $actionLabel = $role ? "{$event} [{$role}]" : $event;

        $audit = new AuditLog();


        $audit->user      = $userId ? $this->em->getReference(User::class, (int)$userId) : null;
        $audit->action    = $actionLabel;
        $audit->entity    = $data['entity'] ?? 'System';
        $audit->entityId  = isset($data['entity_id']) ? (int)$data['entity_id'] : null;
        $audit->details   = json_encode($data, JSON_UNESCAPED_UNICODE);
        $audit->ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'CLI';


        $activity = new ActivityLog();
        $activity->user     = $userId ? $this->em->getReference(User::class, (int)$userId) : null;
        $activity->activity = $this->buildActivityDescription($event, $data, $role);

        $this->em->persist($audit);
        $this->em->persist($activity);
        $this->em->flush();
    }

    /**
     * Build a human-readable activity description.
     * Login events get role-specific labels so the activity feed clearly
     * distinguishes between Resident, Admin, Collection Staff, and Operator logins.
     */
    private function buildActivityDescription(string $event, array $data, ?string $role): string
    {
        $name = $data['name'] ?? 'Unknown';

        return match ($event) {
            'auth.login'  => ($role ?? 'User') . ' login: ' . $name
                             . (isset($data['email']) ? ' (' . $data['email'] . ')' : ''),
            'auth.logout' => ($role ?? 'User') . ' logout: ' . ($data['name'] ?? 'Unknown'),
            default       => $event . (isset($data['entity_id']) ? ' #' . $data['entity_id'] : ''),
        };
    }
}
