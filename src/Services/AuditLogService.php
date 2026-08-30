<?php
namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\AuditLog;
use EcoBin\Entities\ActivityLog;

class AuditLogService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function recentAuditLogs(int $limit = 100): array
    {
        return $this->em->getRepository(AuditLog::class)->findBy([], ['id' => 'DESC'], $limit);
    }

    public function recentActivityLogs(int $limit = 100): array
    {
        return $this->em->getRepository(ActivityLog::class)->findBy([], ['id' => 'DESC'], $limit);
    }
}