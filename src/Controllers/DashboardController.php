<?php
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\RecyclingSubmission;
use EcoBin\Entities\RewardTransaction;
use EcoBin\Services\Security;
use EcoBin\Services\DashboardAnalyticsFacade;

class DashboardController
{
    private DashboardAnalyticsFacade $facade;

    public function __construct(private EntityManagerInterface $em) 
    {
        // Instantiate the Facade for the Design Pattern requirement
        $this->facade = new DashboardAnalyticsFacade($em);
    }

    public function index(): void
    {
        Security::requireRole(['Admin']);
        view('module4/dashboard', ['title'=>'Dashboard & Reporting']);
    }

    public function report(): void
    {
        Security::requireRole(['Admin']);
        $period = $_GET['period'] ?? 'monthly';
        
        // Use the Facade pattern to get all report data
        $reportData = $this->facade->getReportData($period);
        $reportData['title'] = 'Statistical Report';

        // Information Disclosure Mitigation: Prevent browser caching of sensitive reports
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        view('module4/report', $reportData);
    }

    public function csv(): void
    {
        Security::requireRole(['Admin']);
        $type = $_GET['type'] ?? 'collections';
        
        // Information Disclosure Mitigation: Prevent browser caching of exported sensitive CSVs
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        
        if ($type === 'recycling') {
            $recycling = $this->em->getRepository(RecyclingSubmission::class)->findAll();
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="ecobin_recycling_report.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Resident ID','Center ID','Material','Weight (kg)','Points','Status','Created At']);
            foreach ($recycling as $r) {
                fputcsv($out, [
                    $r->id, $r->residentId, $r->centerId, $r->material, 
                    $r->weightKg, $r->points, $r->status, $r->createdAt->format('Y-m-d H:i:s')
                ]);
            }
            fclose($out); exit;
        }

        $collections = $this->em->getRepository(CollectionRequest::class)->findAll();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ecobin_collection_report.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Resident ID','Preferred Date','Scheduled Date','Status','Staff ID','Created At']);
        foreach ($collections as $c) {
            fputcsv($out, [
                $c->id, $c->residentId, $c->preferredDate->format('Y-m-d'),
                $c->scheduledDate?->format('Y-m-d'), $c->status, $c->collectionStaffId, $c->createdAt->format('Y-m-d H:i:s')
            ]);
        }
        fclose($out); exit;
    }
}
