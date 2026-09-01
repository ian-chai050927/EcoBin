<?php
namespace EcoBin\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\RecyclingCenter;
use EcoBin\Entities\RecyclingSubmission;
use EcoBin\Entities\RecyclingAppointment;
use EcoBin\Entities\RewardTransaction;
use EcoBin\Entities\User;
use EcoBin\Services\Security;

class RecyclingController
{
    public function __construct(private EntityManagerInterface $em, private $dispatcher) {}

    public function resident(): void
    {
        Security::requireRole(['Resident']);
        $uid = (int)$_SESSION['user_id'];
        $centers = $this->em->getRepository(RecyclingCenter::class)->findBy([], ['name'=>'ASC']);
        $subs = $this->em->getRepository(RecyclingSubmission::class)->findBy(['residentId'=>$uid], ['id'=>'DESC']);
        $appts = $this->em->getRepository(RecyclingAppointment::class)->findBy(['residentId'=>$uid], ['id'=>'DESC']);
        $rewards = $this->em->getRepository(RewardTransaction::class)->findBy(['userId'=>$uid], ['id'=>'DESC']);
        $balance = array_sum(array_map(fn($r)=>$r->points, $rewards));

        // Calculate total weight and badges
        $totalWeight = array_sum(array_map(fn($s) => $s->status === 'Approved' ? (float)$s->weightKg : 0, $subs));
        $badges = [];
        if (count(array_filter($subs, fn($s) => $s->status === 'Approved')) >= 1) $badges[] = 'Eco Starter';
        if ($totalWeight >= 10) $badges[] = 'Green Warrior';
        if ($totalWeight >= 50) $badges[] = 'Recycling Master';

        // Fetch Leaderboard (top 10 residents by points earned)
        $conn = $this->em->getConnection();
        $sql = "SELECT u.name, SUM(r.points) as total_earned FROM reward_transactions r JOIN users u ON r.user_id = u.id WHERE r.type = 'Earn' GROUP BY u.id ORDER BY total_earned DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $leaderboard = $result->fetchAllAssociative();

        view('module3/resident', compact('centers','subs','appts','rewards','balance','totalWeight','badges','leaderboard') + ['title'=>'Recycling & Rewards']);
    }

    public function submit(): void
    {
        Security::requireRole(['Resident']); Security::verifyCsrf();
        $center = $this->em->find(RecyclingCenter::class, (int)($_POST['center_id'] ?? 0));
        $weight = (float)($_POST['weight_kg'] ?? 0);
        if (!$center || $center->availability !== 'Open' || $weight <= 0) exit('Invalid submission.');

        $s = new RecyclingSubmission();
        $s->residentId = (int)$_SESSION['user_id'];
        $s->centerId = $center->id;
        $s->material = mb_substr(trim($_POST['material'] ?? ''),0,80);
        $s->weightKg = number_format($weight, 2, '.', '');
        $this->em->persist($s); $this->em->flush();

        $this->dispatcher->dispatch('recycling.submitted', ['entity'=>'RecyclingSubmission','entity_id'=>$s->id]);
        Security::flash('success','Recycling submission recorded for operator review.');
        header('Location: index.php?page=module3'); exit;
    }

    public function appointment(): void
    {
        Security::requireRole(['Resident']); Security::verifyCsrf();
        
        $limiter = new \EcoBin\Services\RateLimiter($this->em);
        try {
            $limiter->checkAndLog((int)$_SESSION['user_id'], 'module3.appointment', 3, 3600);
        } catch (\RuntimeException $e) {
            Security::flash('danger', $e->getMessage());
            header('Location: index.php?page=module3'); exit;
        }

        $center = $this->em->find(RecyclingCenter::class, (int)($_POST['center_id'] ?? 0));
        if (!$center || $center->availability !== 'Open') exit('Center unavailable.');

        $a = new RecyclingAppointment();
        $a->residentId = (int)$_SESSION['user_id'];
        $a->centerId = $center->id;
        $a->appointmentAt = new \DateTime($_POST['appointment_at']);
        $this->em->persist($a); $this->em->flush();

        $this->dispatcher->dispatch('recycling.appointment_created', ['entity'=>'RecyclingAppointment','entity_id'=>$a->id]);
        Security::flash('success','Appointment requested.');
        header('Location: index.php?page=module3'); exit;
    }

    public function operator(): void
    {
        Security::requireRole(['Recycling Center Operator']);
        $uid = (int)$_SESSION['user_id'];
        $centers = $this->em->getRepository(RecyclingCenter::class)->findBy(['operatorId'=>$uid]);
        $centerIds = array_map(fn($c)=>$c->id, $centers);

        $subs = [];
        $appts = [];
        foreach ($centerIds as $id) {
            $subs = array_merge($subs, $this->em->getRepository(RecyclingSubmission::class)->findBy(['centerId'=>$id], ['id'=>'DESC']));
            $appts = array_merge($appts, $this->em->getRepository(RecyclingAppointment::class)->findBy(['centerId'=>$id], ['id'=>'DESC']));
        }
        view('module3/operator', compact('centers','subs','appts') + ['title'=>'Recycling Centre Operator']);
    }

    public function centerSave(): void
    {
        Security::requireRole(['Recycling Center Operator']); Security::verifyCsrf();
        $id = (int)($_POST['id'] ?? 0);
        $c = $id ? $this->em->find(RecyclingCenter::class, $id) : new RecyclingCenter();
        if ($id && (!$c || $c->operatorId !== (int)$_SESSION['user_id'])) exit('Forbidden');

        $c->operatorId = (int)$_SESSION['user_id'];
        $c->name = mb_substr(trim($_POST['name']),0,120);
        $c->address = mb_substr(trim($_POST['address']),0,500);
        $c->acceptedMaterials = mb_substr(trim($_POST['accepted_materials']),0,255);
        $c->availability = in_array($_POST['availability'] ?? '', ['Open','Full','Closed'], true) ? $_POST['availability'] : 'Open';
        $this->em->persist($c); $this->em->flush();

        $this->dispatcher->dispatch('recycling.center_saved', ['entity'=>'RecyclingCenter','entity_id'=>$c->id]);
        Security::flash('success','Recycling centre information saved.');
        header('Location: index.php?page=module3-operator'); exit;
    }

    public function reviewSubmission(): void
    {
        Security::requireRole(['Recycling Center Operator']); Security::verifyCsrf();
        $s = $this->em->find(RecyclingSubmission::class, (int)$_POST['submission_id']);
        if (!$s) exit('Not found');
        $center = $this->em->find(RecyclingCenter::class, $s->centerId);
        if (!$center || $center->operatorId !== (int)$_SESSION['user_id']) exit('Forbidden');

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['Approved','Rejected'], true)) exit('Invalid');

        $s->status = $status;
        if ($status === 'Approved' && $s->points === 0) {
            $configService = new \EcoBin\Services\SystemConfigService($this->em);
            $defaultRate = (int)($configService->get('recycling.points_per_kg') ?? 5);
            $strategy = \EcoBin\Services\RewardStrategy\RewardContext::getStrategy($s->material, $defaultRate);
            $s->points = $strategy->calculate((float)$s->weightKg);
            $r = new RewardTransaction();
            $r->userId = $s->residentId;
            $r->points = $s->points;
            $r->type = 'Earn';
            $r->description = 'Recycling submission #' . $s->id;
            $this->em->persist($r);
        }
        $this->em->flush();

        if ($status === 'Approved') {
            $this->dispatcher->dispatch('recycling.approved', ['entity'=>'RecyclingSubmission','entity_id'=>$s->id,'user_id'=>$s->residentId]);
        } else {
            $this->dispatcher->dispatch('recycling.rejected', ['entity'=>'RecyclingSubmission','entity_id'=>$s->id]);
        }
        Security::flash('success','Submission reviewed.');
        header('Location: index.php?page=module3-operator'); exit;
    }

    public function reviewAppointment(): void
    {
        Security::requireRole(['Recycling Center Operator']); Security::verifyCsrf();
        $a = $this->em->find(RecyclingAppointment::class, (int)$_POST['appointment_id']);
        if (!$a) exit('Not found');
        $center = $this->em->find(RecyclingCenter::class, $a->centerId);
        if (!$center || $center->operatorId !== (int)$_SESSION['user_id']) exit('Forbidden');

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['Confirmed','Completed','Cancelled'], true)) exit('Invalid');
        $a->status = $status; $this->em->flush();

        $this->dispatcher->dispatch('appointment.updated', [
            'entity'=>'RecyclingAppointment','entity_id'=>$a->id,'user_id'=>$a->residentId,
            'message'=>'Your recycling appointment is now ' . $status . '.'
        ]);
        Security::flash('success','Appointment updated.');
        header('Location: index.php?page=module3-operator'); exit;
    }

    public function redeem(): void
    {
        Security::requireRole(['Resident']); Security::verifyCsrf();
        $uid = (int)$_SESSION['user_id'];
        
        $limiter = new \EcoBin\Services\RateLimiter($this->em);
        try {
            $limiter->checkAndLog($uid, 'module3.redeem', 5, 3600); // Max 5 redemptions per hour
        } catch (\RuntimeException $e) {
            Security::flash('danger', $e->getMessage());
            header('Location: index.php?page=module3'); exit;
        }

        $pointsToRedeem = (int)($_POST['points'] ?? 0);
        $rewardName = trim($_POST['reward_name'] ?? 'Reward');

        if ($pointsToRedeem <= 0) exit('Invalid points.');

        $this->em->beginTransaction();
        try {
            $conn = $this->em->getConnection();
            // Calculate current balance with pessimistic write lock (FOR UPDATE)
            $sql = "SELECT SUM(points) FROM reward_transactions WHERE user_id = :uid FOR UPDATE";
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery(['uid' => $uid]);
            $balance = (int)$result->fetchOne();

            if ($balance < $pointsToRedeem) {
                throw new \Exception('Insufficient points. Balance: ' . $balance);
            }

            // Deduct points
            $r = new RewardTransaction();
            $r->userId = $uid;
            $r->points = -$pointsToRedeem;
            $r->type = 'Redeem';
            $r->description = 'Redeemed: ' . mb_substr($rewardName, 0, 200);
            $this->em->persist($r);
            $this->em->flush();
            $this->em->commit();

            if (isset($this->dispatcher)) {
                $this->dispatcher->dispatch('reward.redeemed', ['user_id' => $uid, 'points' => $pointsToRedeem]);
            }
            Security::flash('success', 'Reward redeemed successfully!');
        } catch (\Exception $e) {
            $this->em->rollback();
            Security::flash('danger', $e->getMessage());
        }

        header('Location: index.php?page=module3'); exit;
    }
}
