<?php
namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\RecyclingSubmission;

class DashboardAnalyticsFacade
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Aggregates stats for the dashboard overview and charts.
     * Implements DoS protection by limiting the trend calculation to a maximum of 6 months.
     */
    public function getDashboardStats(): array
    {
        // Limit data fetching to the last 6 months to prevent Application-Level DoS (Resource Exhaustion)
        // If an attacker forces huge date ranges, it would crash the system. We bound it here.
        $sixMonthsAgo = new \DateTime('-6 months');
        
        $qbCol = $this->em->createQueryBuilder();
        $collections = $qbCol->select('c')
            ->from(CollectionRequest::class, 'c')
            ->where('c.createdAt >= :date')
            ->setParameter('date', $sixMonthsAgo)
            ->getQuery()
            ->getResult();

        $qbRec = $this->em->createQueryBuilder();
        $recycling = $qbRec->select('r')
            ->from(RecyclingSubmission::class, 'r')
            ->where('r.createdAt >= :date')
            ->setParameter('date', $sixMonthsAgo)
            ->getQuery()
            ->getResult();
            
        $wasteCount = $this->em->getRepository(WasteReport::class)->count([]);

        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $trends[$month] = ['collections' => 0, 'recycling' => 0];
        }

        $collection_status = ['Pending'=>0, 'Assigned'=>0, 'In Progress'=>0, 'Completed'=>0, 'Cancelled'=>0];
        $total_time = 0; $completed_count = 0;
        $total_collections = 0;

        foreach ($collections as $c) {
            $total_collections++;
            $m = $c->createdAt->format('Y-m');
            if (isset($trends[$m])) $trends[$m]['collections']++;
            if (isset($collection_status[$c->status])) $collection_status[$c->status]++;
            
            if ($c->status === 'Completed' && $c->scheduledDate) {
                $total_time += $c->scheduledDate->getTimestamp() - $c->createdAt->getTimestamp();
                $completed_count++;
            }
        }

        $recycling_status = ['Pending'=>0, 'Approved'=>0, 'Rejected'=>0];
        $total_recycling = 0;
        
        foreach ($recycling as $r) {
            $total_recycling++;
            $m = $r->createdAt->format('Y-m');
            if (isset($trends[$m])) $trends[$m]['recycling']++;
            if (isset($recycling_status[$r->status])) $recycling_status[$r->status]++;
        }

        $avg_time_days = $completed_count > 0 ? round(($total_time / $completed_count) / 86400, 1) : 0;

        return [
            'waste_reports' => $wasteCount,
            'collection_requests' => $total_collections,
            'collection_completed' => $collection_status['Completed'] ?? 0,
            'recycling_submissions' => $total_recycling,
            'recycling_approved' => $recycling_status['Approved'] ?? 0,
            'trends' => $trends,
            'collection_status' => $collection_status,
            'recycling_status' => $recycling_status,
            'avg_completion_days' => $avg_time_days
        ];
    }

    /**
     * Generates comprehensive report data for a specific period.
     */
    public function getReportData(string $period): array
    {
        // Enforce strict data bounding (Allowlist)
        if (!in_array($period, ['monthly', 'annual'])) {
            $period = 'monthly';
        }

        $startDate = $period === 'annual' 
            ? new \DateTime(date('Y-01-01 00:00:00')) 
            : new \DateTime(date('Y-m-01 00:00:00'));

        $qbCol = $this->em->createQueryBuilder();
        $filteredCollections = $qbCol->select('c')
            ->from(CollectionRequest::class, 'c')
            ->where('c.createdAt >= :date')
            ->setParameter('date', $startDate)
            ->getQuery()
            ->getResult();

        $qbRec = $this->em->createQueryBuilder();
        $filteredRecycling = $qbRec->select('r')
            ->from(RecyclingSubmission::class, 'r')
            ->where('r.createdAt >= :date')
            ->setParameter('date', $startDate)
            ->getQuery()
            ->getResult();

        $totalWeight = array_reduce($filteredRecycling, fn($carry, $r) => $carry + ($r->status === 'Approved' ? (float)$r->weightKg : 0), 0);
        
        $completedCols = array_filter($filteredCollections, fn($c) => $c->status === 'Completed');
        $completionRate = count($filteredCollections) > 0 ? round((count($completedCols) / count($filteredCollections)) * 100, 1) : 0;
        
        $totalTime = 0; $completedCount = 0;
        foreach ($completedCols as $c) {
            if ($c->scheduledDate) {
                $totalTime += $c->scheduledDate->getTimestamp() - $c->createdAt->getTimestamp();
                $completedCount++;
            }
        }
        $avgProcessingDays = $completedCount > 0 ? round(($totalTime / $completedCount) / 86400, 1) : 0;

        return [
            'period' => $period,
            'collections' => $filteredCollections,
            'recycling' => $filteredRecycling,
            'totalWeight' => $totalWeight,
            'completionRate' => $completionRate,
            'avgProcessingDays' => $avgProcessingDays
        ];
    }
}
