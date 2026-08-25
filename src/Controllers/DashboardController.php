<?php
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\RecyclingSubmission;
use EcoBin\Entities\RewardTransaction;
use EcoBin\Services\Security;

class DashboardController
{
    public function __construct(private EntityManagerInterface $em) {}

    public function index(): void
    {
        Security::requireRole(['Admin']);
        view('module4/dashboard', ['title'=>'Dashboard & Reporting']);
    }

    public function report(): void
    {
        Security::requireRole(['Admin']);
        $period = $_GET['period'] ?? 'monthly';
        $collections = $this->em->getRepository(CollectionRequest::class)->findAll();
        $recycling = $this->em->getRepository(RecyclingSubmission::class)->findAll();

        $filteredCollections = array_filter($collections, function($c) use ($period) {
            return $period === 'annual'
                ? $c->createdAt->format('Y') === date('Y')
                : $c->createdAt->format('Y-m') === date('Y-m');
        });
        $filteredRecycling = array_filter($recycling, function($r) use ($period) {
            return $period === 'annual'
                ? $r->createdAt->format('Y') === date('Y')
                : $r->createdAt->format('Y-m') === date('Y-m');
        });

        view('module4/report', [
            'title'=>'Statistical Report',
            'period'=>$period,
            'collections'=>$filteredCollections,
            'recycling'=>$filteredRecycling
        ]);
    }

    public function csv(): void
    {
        Security::requireRole(['Admin']);
        $collections = $this->em->getRepository(CollectionRequest::class)->findAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ecobin_collection_report.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Resident ID','Preferred Date','Scheduled Date','Status','Staff ID']);
        foreach ($collections as $c) {
            fputcsv($out, [
                $c->id, $c->residentId, $c->preferredDate->format('Y-m-d'),
                $c->scheduledDate?->format('Y-m-d'), $c->status, $c->collectionStaffId
            ]);
        }
        fclose($out); exit;
    }
}
