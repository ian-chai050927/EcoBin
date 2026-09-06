<?php
/**
 * Module 3: Recycling & Reward Management
 * @author Ho Ze-Yang
 */
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\RecyclingCenter;
use EcoBin\Entities\RecyclingSubmission;
use EcoBin\Entities\RecyclingAppointment;
use EcoBin\Entities\RewardTransaction;
use EcoBin\Entities\User;
use EcoBin\Services\Security;
use EcoBin\Services\InternalApiClient;

class RecyclingController
{
    /**
     * Authoritative server-side reward catalog to prevent client-side points tampering.
     */
    public const REWARD_CATALOG = [
        'voucher_5' => [
            'name'   => '$5 Shopping Voucher',
            'points' => 500,
            'icon'   => 'bi-ticket-perforated',
            'desc'   => 'Redeemable at participating supermarkets',
        ],
        'voucher_10' => [
            'name'   => '$10 Shopping Voucher',
            'points' => 900,
            'icon'   => 'bi-ticket-perforated-fill',
            'desc'   => 'Redeemable at participating supermarkets',
        ],
        'voucher_20' => [
            'name'   => '$20 Shopping Voucher',
            'points' => 1700,
            'icon'   => 'bi-gift',
            'desc'   => 'Best value voucher for grocery shopping',
        ],
        'eco_bag' => [
            'name'   => 'Exclusive Eco-Friendly Tote Bag',
            'points' => 1200,
            'icon'   => 'bi-bag-check',
            'desc'   => 'High-durability recycled canvas tote',
        ],
    ];

    /**
     * Curated accepted material options for recycling centre operators.
     */
    public const ACCEPTED_MATERIAL_OPTIONS = [
        'All Materials (Plastic, Paper, Metal, Glass)' => 'All Materials',
        'Plastic, Paper, Metal'                        => 'Plastic, Paper, Metal',
        'Plastic, Paper, Glass'                        => 'Plastic, Paper, Glass',
        'Plastic & Metal Only'                         => 'Plastic & Metal Only',
        'Paper & Cardboard Only'                       => 'Paper & Cardboard Only',
        'Plastic Only'                                 => 'Plastic Only',
        'Metal Only'                                   => 'Metal Only',
        'Glass Only'                                   => 'Glass Only',
        'E-Waste & Electronics'                        => 'E-Waste & Electronics',
        'General Recyclables'                          => 'General Recyclables',
    ];

    /**
     * Standard recyclable material types for resident submission with reward points guide.
     */
    public const SUBMISSION_MATERIAL_OPTIONS = [
        'Plastic'             => 'Plastic (Bottles, Containers, Packaging) — 15 pts/kg',
        'Metal'               => 'Metal (Aluminium Cans, Tins, Scrap) — 20 pts/kg',
        'Paper'               => 'Paper & Cardboard (Boxes, Books, Newspapers) — 10 pts/kg',
        'Glass'               => 'Glass (Bottles, Jars) — 5 pts/kg',
        'E-Waste'             => 'E-Waste (Old Electronics, Batteries) — 5 pts/kg',
        'General Recyclables' => 'General / Mixed Recyclables — 5 pts/kg',
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private array $app,
        private $dispatcher
    ) {}

    public function resident(): void
    {
        Security::requireRole(['Resident']);
        $uid = (int)$_SESSION['user_id'];

        /*
         * ORM RELATIONSHIP USAGE:
         * findBy with 'resident' maps to the ManyToOne association; Doctrine
         * translates it to WHERE resident_id = :uid automatically.
         */
        $centers = $this->em->getRepository(RecyclingCenter::class)->findBy([], ['name' => 'ASC']);
        $subs    = $this->em->getRepository(RecyclingSubmission::class)->findBy(['resident' => $uid], ['id' => 'DESC']);
        $appts   = $this->em->getRepository(RecyclingAppointment::class)->findBy(['resident' => $uid], ['id' => 'DESC']);
        $rewards = $this->em->getRepository(RewardTransaction::class)->findBy(['user' => $uid], ['id' => 'DESC']);
        $balance = array_sum(array_map(fn($r) => $r->points, $rewards));

        // Environmental impact metrics & badges
        $totalWeight = array_sum(array_map(fn($s) => $s->status === 'Approved' ? (float)$s->weightKg : 0, $subs));
        $co2Offset   = round($totalWeight * 2.86, 2); // ~2.86 kg CO2 saved per kg of recycled waste
        $activeApptsCount = count(array_filter($appts, fn($a) => in_array($a->status, ['Pending', 'Confirmed'], true)));

        $badges = [];
        if (count(array_filter($subs, fn($s) => $s->status === 'Approved')) >= 1) $badges[] = 'Eco Starter';
        if ($totalWeight >= 10) $badges[] = 'Green Warrior';
        if ($totalWeight >= 50) $badges[] = 'Recycling Master';

        // Leaderboard (top 10 by points earned)
        $conn   = $this->em->getConnection();
        $sql    = "SELECT u.name, SUM(r.points) as total_earned FROM reward_transactions r JOIN users u ON r.user_id = u.id WHERE r.type = 'Earn' GROUP BY u.id ORDER BY total_earned DESC LIMIT 10";
        $stmt   = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $leaderboard = $result->fetchAllAssociative();

        $catalog         = self::REWARD_CATALOG;
        $materialOptions = self::SUBMISSION_MATERIAL_OPTIONS;

        view('module3/resident', compact(
            'centers', 'subs', 'appts', 'rewards', 'balance',
            'totalWeight', 'co2Offset', 'activeApptsCount', 'badges', 'leaderboard', 'catalog', 'materialOptions'
        ) + ['title' => 'Recycling & Rewards']);
    }

    public function submit(): void
    {
        Security::requireRole(['Resident']); Security::verifyCsrf();
        $uid = (int)$_SESSION['user_id'];

        $limiter = new \EcoBin\Services\RateLimiter($this->em);
        try {
            $limiter->checkAndLog($uid, 'module3.submit', 10, 3600);
        } catch (\RuntimeException $e) {
            Security::flash('danger', $e->getMessage());
            header('Location: index.php?page=module3'); exit;
        }

        $centerId = (int)($_POST['center_id'] ?? 0);
        $center   = $this->em->find(RecyclingCenter::class, $centerId);
        $material = mb_substr(trim($_POST['material'] ?? ''), 0, 80);
        $weight   = (float)($_POST['weight_kg'] ?? 0);

        if (!$center || $center->availability !== 'Open') {
            Security::flash('danger', 'Selected recycling centre is currently unavailable or does not exist.');
            header('Location: index.php?page=module3'); exit;
        }

        if ($material === '' || !array_key_exists($material, self::SUBMISSION_MATERIAL_OPTIONS)) {
            Security::flash('danger', 'Please choose a valid recyclable material type from the dropdown list.');
            header('Location: index.php?page=module3'); exit;
        }

        if ($weight < 0.01 || $weight > 500.0) {
            Security::flash('danger', 'Submission weight must be between 0.01 kg and 500.00 kg.');
            header('Location: index.php?page=module3'); exit;
        }

        /*
         * ORM RELATIONSHIP USAGE:
         * Assign $s->resident and $s->center as object associations.
         * Doctrine writes resident_id and center_id FK columns on flush.
         */
        $s           = new RecyclingSubmission();
        $s->resident = $this->em->getReference(User::class, $uid);
        $s->center   = $center;
        $s->material = $material;
        $s->weightKg = number_format($weight, 2, '.', '');
        $this->em->persist($s); $this->em->flush();

        $this->dispatcher->dispatch('recycling.submitted', ['entity' => 'RecyclingSubmission', 'entity_id' => $s->id]);
        Security::flash('success', 'Recycling submission of ' . $s->weightKg . ' kg ' . $s->material . ' recorded for operator review.');
        header('Location: index.php?page=module3'); exit;
    }

    public function appointment(): void
    {
        Security::requireRole(['Resident']); Security::verifyCsrf();

        $limiter = new \EcoBin\Services\RateLimiter($this->em);
        try {
            $limiter->checkAndLog((int)$_SESSION['user_id'], 'module3.appointment', 5, 3600);
        } catch (\RuntimeException $e) {
            Security::flash('danger', $e->getMessage());
            header('Location: index.php?page=module3'); exit;
        }

        $rawDate = trim($_POST['appointment_at'] ?? '');
        if ($rawDate === '') {
            Security::flash('danger', 'Please choose an appointment date and time.');
            header('Location: index.php?page=module3'); exit;
        }

        try {
            $apptDate = new \DateTime($rawDate);
        } catch (\Exception) {
            Security::flash('danger', 'Invalid appointment date format.');
            header('Location: index.php?page=module3'); exit;
        }

        $now = new \DateTime();
        if ($apptDate <= $now) {
            Security::flash('danger', 'Appointment date and time must be scheduled in the future.');
            header('Location: index.php?page=module3'); exit;
        }

        $center = $this->em->find(RecyclingCenter::class, (int)($_POST['center_id'] ?? 0));
        if (!$center || $center->availability !== 'Open') {
            Security::flash('danger', 'Selected recycling centre is currently unavailable.');
            header('Location: index.php?page=module3'); exit;
        }

        $uid = (int)$_SESSION['user_id'];
        $existingAppts = $this->em->getRepository(RecyclingAppointment::class)->findBy([
            'resident' => $uid,
            'center'   => $center->id,
            'status'   => ['Pending', 'Confirmed'],
        ]);

        foreach ($existingAppts as $existing) {
            $diffSeconds = abs($existing->appointmentAt->getTimestamp() - $apptDate->getTimestamp());
            if ($diffSeconds < 1800) { // within 30 minutes
                Security::flash('danger', 'You already have an appointment at this centre around this time slot (' . $existing->appointmentAt->format('Y-m-d H:i') . ').');
                header('Location: index.php?page=module3'); exit;
            }
        }

        /*
         * ORM RELATIONSHIP USAGE:
         * Assign $a->resident and $a->center as object associations.
         */
        $a                = new RecyclingAppointment();
        $a->resident      = $this->em->getReference(User::class, $uid);
        $a->center        = $center;
        $a->appointmentAt = $apptDate;
        $this->em->persist($a); $this->em->flush();

        $this->dispatcher->dispatch('recycling.appointment_created', ['entity' => 'RecyclingAppointment', 'entity_id' => $a->id]);
        Security::flash('success', 'Recycling appointment requested successfully for ' . $apptDate->format('Y-m-d H:i') . '.');
        header('Location: index.php?page=module3'); exit;
    }

    public function cancelAppointment(): void
    {
        Security::requireRole(['Resident']); Security::verifyCsrf();
        $id = (int)($_POST['appointment_id'] ?? 0);
        $a  = $this->em->find(RecyclingAppointment::class, $id);

        if (!$a) {
            Security::flash('danger', 'Appointment not found.');
            header('Location: index.php?page=module3'); exit;
        }

        // Object ownership verification (IDOR/BOLA prevention)
        if ($a->resident->id !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            exit('Forbidden: You can only cancel your own appointments.');
        }

        if (!in_array($a->status, ['Pending', 'Confirmed'], true)) {
            Security::flash('danger', 'Only Pending or Confirmed appointments can be cancelled.');
            header('Location: index.php?page=module3'); exit;
        }

        $a->status = 'Cancelled';
        $this->em->flush();

        $this->dispatcher->dispatch('appointment.cancelled', [
            'entity'    => 'RecyclingAppointment',
            'entity_id' => $a->id,
            'user_id'   => $a->resident->id,
            'message'   => 'Your recycling appointment on ' . $a->appointmentAt->format('Y-m-d H:i') . ' was cancelled.',
        ]);

        Security::flash('success', 'Appointment has been cancelled.');
        header('Location: index.php?page=module3'); exit;
    }

    public function operator(): void
    {
        Security::requireRole(['Recycling Center Operator']);
        $uid = (int)$_SESSION['user_id'];

        $centers   = $this->em->getRepository(RecyclingCenter::class)->findBy(['operator' => $uid], ['id' => 'ASC']);
        $centerIds = array_map(fn($c) => $c->id, $centers);

        $subs  = [];
        $appts = [];
        foreach ($centerIds as $id) {
            $subs  = array_merge($subs,  $this->em->getRepository(RecyclingSubmission::class)->findBy(['center' => $id], ['id' => 'DESC']));
            $appts = array_merge($appts, $this->em->getRepository(RecyclingAppointment::class)->findBy(['center' => $id], ['id' => 'DESC']));
        }

        // Operator KPI Metrics
        $managedCentersCount = count($centers);
        $pendingSubsCount    = count(array_filter($subs, fn($s) => $s->status === 'Pending'));
        $pendingApptsCount   = count(array_filter($appts, fn($a) => $a->status === 'Pending'));
        $totalRecycledWeight = array_sum(array_map(fn($s) => $s->status === 'Approved' ? (float)$s->weightKg : 0, $subs));
        $acceptedOptions     = self::ACCEPTED_MATERIAL_OPTIONS;

        view('module3/operator', compact(
            'centers', 'subs', 'appts',
            'managedCentersCount', 'pendingSubsCount', 'pendingApptsCount', 'totalRecycledWeight', 'acceptedOptions'
        ) + ['title' => 'Recycling Centre Operator']);
    }

    public function centerSave(): void
    {
        Security::requireRole(['Recycling Center Operator']); Security::verifyCsrf();
        $uid = (int)$_SESSION['user_id'];
        $id  = (int)($_POST['id'] ?? 0);
        $c   = $id ? $this->em->find(RecyclingCenter::class, $id) : new RecyclingCenter();

        if ($id && (!$c || $c->operator->id !== $uid)) {
            Security::flash('danger', 'Unauthorized or centre not found.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $name     = mb_substr(trim($_POST['name'] ?? ''), 0, 120);
        $address  = mb_substr(trim($_POST['address'] ?? ''), 0, 500);
        $accepted = mb_substr(trim($_POST['accepted_materials'] ?? ''), 0, 255);

        if ($name === '' || $address === '') {
            Security::flash('danger', 'Please provide Centre Name and Address.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        if ($accepted === '' || (!array_key_exists($accepted, self::ACCEPTED_MATERIAL_OPTIONS) && mb_strlen($accepted) < 2)) {
            Security::flash('danger', 'Please choose a valid accepted materials option from the dropdown list.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $c->operator          = $this->em->getReference(User::class, $uid);
        $c->name              = $name;
        $c->address           = $address;
        $c->acceptedMaterials = $accepted;
        $c->availability      = in_array($_POST['availability'] ?? '', ['Open', 'Full', 'Closed'], true) ? $_POST['availability'] : 'Open';
        $c->operatingHours    = mb_substr(trim($_POST['operating_hours'] ?? 'Mon - Fri: 9:00 AM - 5:00 PM'), 0, 120);
        $this->em->persist($c); $this->em->flush();

        $this->dispatcher->dispatch('recycling.center_saved', ['entity' => 'RecyclingCenter', 'entity_id' => $c->id]);
        $action = $id ? 'updated' : 'created';
        Security::flash('success', 'Recycling centre "' . $c->name . '" ' . $action . ' successfully.');
        header('Location: index.php?page=module3-operator'); exit;
    }

    public function centerStatus(): void
    {
        Security::requireRole(['Recycling Center Operator']); Security::verifyCsrf();
        $uid    = (int)$_SESSION['user_id'];
        $id     = (int)($_POST['center_id'] ?? 0);
        $status = trim($_POST['availability'] ?? '');

        if (!in_array($status, ['Open', 'Full', 'Closed'], true)) {
            Security::flash('danger', 'Invalid availability status.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $c = $this->em->find(RecyclingCenter::class, $id);
        if (!$c || $c->operator->id !== $uid) {
            Security::flash('danger', 'Unauthorized or centre not found.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $c->availability = $status;
        $this->em->flush();

        Security::flash('success', 'Centre "' . $c->name . '" availability changed to ' . $status . '.');
        header('Location: index.php?page=module3-operator'); exit;
    }

    public function reviewSubmission(): void
    {
        Security::requireRole(['Recycling Center Operator']); Security::verifyCsrf();
        $uid = (int)$_SESSION['user_id'];
        $s   = $this->em->find(RecyclingSubmission::class, (int)($_POST['submission_id'] ?? 0));

        if (!$s) {
            Security::flash('danger', 'Recycling submission not found.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        if (!$s->center || $s->center->operator->id !== $uid) {
            Security::flash('danger', 'Unauthorized: You can only review submissions for your centres.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        if ($s->status !== 'Pending') {
            Security::flash('danger', 'This submission has already been reviewed (Status: ' . $s->status . ').');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['Approved', 'Rejected'], true)) {
            Security::flash('danger', 'Invalid review status.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $s->status = $status;
        if ($status === 'Approved' && $s->points === 0) {
            $configService = new \EcoBin\Services\SystemConfigService($this->em);
            $defaultRate   = (int)($configService->get('recycling.points_per_kg') ?? 5);

            $strategy  = \EcoBin\Services\RewardStrategy\RewardContext::getStrategy($s->material, $defaultRate);
            $s->points = $strategy->calculate((float)$s->weightKg);

            $r              = new RewardTransaction();
            $r->user        = $s->resident;
            $r->points      = $s->points;
            $r->type        = 'Earn';
            $r->description = 'Recycling submission #' . $s->id . ' (' . $s->material . ', ' . $s->weightKg . ' kg)';
            $this->em->persist($r);
        }
        $this->em->flush();

        if ($status === 'Approved') {
            $this->dispatcher->dispatch('recycling.approved', [
                'entity'    => 'RecyclingSubmission',
                'entity_id' => $s->id,
                'user_id'   => $s->resident->id,
                'points'    => $s->points,
            ]);
            Security::flash('success', 'Submission #' . $s->id . ' approved! ' . $s->points . ' points awarded to ' . $s->resident->name . '.');
        } else {
            $this->dispatcher->dispatch('recycling.rejected', [
                'entity'    => 'RecyclingSubmission',
                'entity_id' => $s->id,
                'user_id'   => $s->resident->id,
            ]);
            Security::flash('success', 'Submission #' . $s->id . ' rejected.');
        }

        header('Location: index.php?page=module3-operator'); exit;
    }

    public function reviewAppointment(): void
    {
        Security::requireRole(['Recycling Center Operator']); Security::verifyCsrf();
        $uid = (int)$_SESSION['user_id'];
        $a   = $this->em->find(RecyclingAppointment::class, (int)($_POST['appointment_id'] ?? 0));

        if (!$a) {
            Security::flash('danger', 'Appointment not found.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        if (!$a->center || $a->center->operator->id !== $uid) {
            Security::flash('danger', 'Unauthorized: You can only manage appointments for your centres.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['Confirmed', 'Completed', 'Cancelled'], true)) {
            Security::flash('danger', 'Invalid appointment status.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        if ($a->status === 'Cancelled' && $status !== 'Cancelled') {
            Security::flash('danger', 'A cancelled appointment cannot be reactivated.');
            header('Location: index.php?page=module3-operator'); exit;
        }

        $a->status = $status;
        $this->em->flush();

        $this->dispatcher->dispatch('appointment.updated', [
            'entity'    => 'RecyclingAppointment',
            'entity_id' => $a->id,
            'user_id'   => $a->resident->id,
            'message'   => 'Your recycling appointment on ' . $a->appointmentAt->format('Y-m-d H:i') . ' is now ' . $status . '.',
        ]);
        Security::flash('success', 'Appointment #' . $a->id . ' is now ' . $status . '.');
        header('Location: index.php?page=module3-operator'); exit;
    }

    public function redeem(): void
    {
        Security::requireRole(['Resident']); Security::verifyCsrf();
        $uid = (int)$_SESSION['user_id'];

        $limiter = new \EcoBin\Services\RateLimiter($this->em);
        try {
            $limiter->checkAndLog($uid, 'module3.redeem', 5, 3600);
        } catch (\RuntimeException $e) {
            Security::flash('danger', $e->getMessage());
            header('Location: index.php?page=module3'); exit;
        }

        $rewardKey = trim($_POST['reward_id'] ?? '');
        if (!isset(self::REWARD_CATALOG[$rewardKey])) {
            Security::flash('danger', 'Invalid reward item selected.');
            header('Location: index.php?page=module3'); exit;
        }

        $rewardItem     = self::REWARD_CATALOG[$rewardKey];
        $pointsToRedeem = (int)$rewardItem['points'];
        $rewardName     = (string)$rewardItem['name'];

        $this->em->beginTransaction();
        try {
            $conn = $this->em->getConnection();
            // Pessimistic write lock to prevent race conditions & double-spending
            $sql    = "SELECT SUM(points) FROM reward_transactions WHERE user_id = :uid FOR UPDATE";
            $stmt   = $conn->prepare($sql);
            $result = $stmt->executeQuery(['uid' => $uid]);
            $balance = (int)$result->fetchOne();

            if ($balance < $pointsToRedeem) {
                throw new \Exception('Insufficient points. Required: ' . $pointsToRedeem . ' pts, Current Balance: ' . $balance . ' pts.');
            }

            $r              = new RewardTransaction();
            $r->user        = $this->em->getReference(User::class, $uid);
            $r->points      = -$pointsToRedeem;
            $r->type        = 'Redeem';
            $r->description = 'Redeemed: ' . mb_substr($rewardName, 0, 200);
            $this->em->persist($r);
            $this->em->flush();
            $this->em->commit();

            if (isset($this->dispatcher)) {
                $this->dispatcher->dispatch('reward.redeemed', [
                    'user_id' => $uid,
                    'points'  => $pointsToRedeem,
                    'reward'  => $rewardName,
                ]);
            }

            $user = $this->em->find(User::class, $uid);
            if ($user && $user->email) {
                $client = new InternalApiClient(
                    $this->app['base_url'],
                    $this->app['service_token']
                );

                $emailResponse = $client->call('notification.email', [
                    'email'   => $user->email,
                    'subject' => 'EcoBin: Reward redemption receipt',
                    'message' => '<p>Dear ' . htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') . ',</p>'
                        . '<p>You successfully redeemed <strong>' . $pointsToRedeem
                        . ' points</strong> for "<strong>' . htmlspecialchars($rewardName, ENT_QUOTES, 'UTF-8')
                        . '</strong>".</p>'
                        . '<p>Your updated reward balance is: <strong>' . ($balance - $pointsToRedeem) . ' points</strong>.</p>'
                        . '<p>Thank you for recycling with EcoBin!</p>',
                ]);

                if (($emailResponse['status'] ?? null) === 'ERROR') {
                    error_log('Module 5 notification.email unavailable for redemption receipt: '
                        . ($emailResponse['error'] ?? 'unknown error'));
                }
            }

            Security::flash('success', 'Redemption successful! ' . $pointsToRedeem . ' points redeemed for ' . $rewardName . '.');
        } catch (\Exception $e) {
            $this->em->rollback();
            Security::flash('danger', $e->getMessage());
        }

        header('Location: index.php?page=module3'); exit;
    }
}
