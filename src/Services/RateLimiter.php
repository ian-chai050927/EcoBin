<?php
namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\ActivityLog;
use RuntimeException;

class RateLimiter
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Check if the user has exceeded the rate limit.
     * Logs the activity if under the limit.
     *
     * @param int $userId
     * @param string $action
     * @param int $maxAttempts
     * @param int $timeframeSeconds
     * @throws RuntimeException
     */
    public function checkAndLog(int $userId, string $action, int $maxAttempts, int $timeframeSeconds = 3600): void
    {
        $conn = $this->em->getConnection();
        
        // Count activities in the timeframe
        $sql = "SELECT COUNT(*) FROM activity_logs WHERE user_id = :user_id AND activity = :activity AND created_at >= DATE_SUB(NOW(), INTERVAL :seconds SECOND)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'user_id' => $userId,
            'activity' => $action,
            'seconds' => $timeframeSeconds
        ]);
        
        $count = (int)$result->fetchOne();
        
        if ($count >= $maxAttempts) {
            throw new RuntimeException("Rate limit exceeded for action: {$action}. Please try again later.");
        }
        
        // Log the activity
        $log = new ActivityLog();
        $log->userId = $userId;
        $log->activity = $action;
        $this->em->persist($log);
        $this->em->flush();
    }
}
