<?php
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\WasteReport;
use EcoBin\Entities\CollectionRequest;
use EcoBin\Entities\RecyclingSubmission;
use EcoBin\Entities\RewardTransaction;
use EcoBin\Entities\User;
use EcoBin\Services\Security;
use EcoBin\Services\DashboardAnalyticsFacade;
use EcoBin\Services\InternalApiClient;

class DashboardController
{
    // Bounds for the CSV export 'range' parameter — caps how much
    // history a single export request can pull, regardless of what
    // value is requested.
    private const MIN_EXPORT_RANGE_DAYS = 1;
    private const MAX_EXPORT_RANGE_DAYS = 365;

    private DashboardAnalyticsFacade $facade;

    public function __construct(private EntityManagerInterface $em, private array $app)
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

        /*
         * WEB SERVICE (CONSUMED):
         *
         * Module 4 consumes Module 5's notification.create service
         * when a generated report reveals an operational problem —
         * here, a completion rate under 50% with a meaningful sample
         * size — rather than only rendering the number for whoever
         * happens to open this page.
         */
        if (
            $reportData['completionRate'] < 50
            && count($reportData['collections']) >= 5
        ) {
            $admins = $this->em->getRepository(User::class)->findBy([
                'role' => 'Admin',
                'status' => 'Active',
            ]);

            $client = new InternalApiClient(
                $this->app['base_url'],
                $this->app['service_token']
            );

            foreach ($admins as $admin) {
                $response = $client->call('notification.create', [
                    'user_id' => $admin->id,
                    'title' => 'Low Collection Completion Rate',
                    'message' => 'The ' . $period . ' report shows a completion rate of '
                        . $reportData['completionRate'] . '%, below the 50% threshold. '
                        . 'Review collection staff assignment and scheduling.',
                    'type' => 'System',
                ]);

                if (($response['status'] ?? null) === 'ERROR') {
                    // Failure handled: report generation still succeeds even
                    // if Module 5's service is unavailable — we just log it.
                    error_log('Module 5 notification.create unavailable for admin '
                        . $admin->id . ': ' . ($response['error'] ?? 'unknown error'));
                }
            }
        }

        // Information Disclosure Mitigation: Prevent browser caching of sensitive reports
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        view('module4/report', $reportData);
    }

    public function csv(): void
    {
        Security::requireRole(['Admin']);
        $type = $_GET['type'] ?? 'collections';

        /*
         * SECURE CODING: Application-Level DoS via Resource Exhaustion.
         *
         * Vulnerable entry point: this export previously called
         * findAll() with no limit at all — every row in the table
         * was loaded into memory and streamed out in a single
         * request, regardless of table size. A malicious or simply
         * careless request against a large table (or an attacker
         * scripting repeated calls) could exhaust server memory or
         * tie up a database connection for an extended period.
         *
         * Attack mechanism: no user-controlled bound existed to limit
         * how much data a single request could pull, so table growth
         * directly increases the blast radius of every export call.
         *
         * Mitigation: introduce an explicit, user-controlled 'range'
         * parameter (days of history to export) and strictly clamp
         * it to a safe minimum and maximum before it reaches the
         * query — an out-of-range or non-numeric value is coerced
         * into the nearest safe bound rather than rejected outright,
         * so the export never has to fetch more than
         * self::MAX_EXPORT_RANGE_DAYS worth of rows.
         */
        $requestedRange = (int)($_GET['range'] ?? self::MAX_EXPORT_RANGE_DAYS);
        $rangeDays = max(
            self::MIN_EXPORT_RANGE_DAYS,
            min(self::MAX_EXPORT_RANGE_DAYS, $requestedRange)
        );
        $sinceDate = (new \DateTime())->modify("-{$rangeDays} days");

        // Information Disclosure Mitigation: Prevent browser caching of exported sensitive CSVs
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        if ($type === 'recycling') {
            $recycling = $this->em->getRepository(RecyclingSubmission::class)
                ->createQueryBuilder('r')
                ->where('r.createdAt >= :since')
                ->setParameter('since', $sinceDate)
                ->getQuery()
                ->getResult();

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="ecobin_recycling_report.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Resident ID','Center ID','Material','Weight (kg)','Points','Status','Created At']);
            foreach ($recycling as $r) {
                fputcsv($out, [
                    $r->id, $r->resident->id, $r->center->id, $r->material,
                    $r->weightKg, $r->points, $r->status, $r->createdAt->format('Y-m-d H:i:s')
                ]);
            }
            fclose($out); exit;
        }

        $collections = $this->em->getRepository(CollectionRequest::class)
            ->createQueryBuilder('c')
            ->where('c.createdAt >= :since')
            ->setParameter('since', $sinceDate)
            ->getQuery()
            ->getResult();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ecobin_collection_report.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Resident ID','Preferred Date','Scheduled Date','Status','Staff ID','Created At']);
        foreach ($collections as $c) {
            fputcsv($out, [
                $c->id, $c->resident->id, $c->preferredDate->format('Y-m-d'),
                $c->scheduledDate?->format('Y-m-d'), $c->status, $c->collectionStaff?->id, $c->createdAt->format('Y-m-d H:i:s')
            ]);
        }
        fclose($out); exit;
    }
    public function pdf(): void
    {
        Security::requireRole(['Admin']);
        $period = $_GET['period'] ?? 'monthly';

        $reportData = $this->facade->getReportData($period);
        $reportData['title'] = 'Statistical Report';
        $reportData['skipToolbar'] = true;

        ob_start();
        extract($reportData);
        require __DIR__ . '/../../views/module4/report.php';
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $filename = 'ecobin_' . $period . '_report_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}