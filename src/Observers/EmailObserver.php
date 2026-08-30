<?php
namespace EcoBin\Observers;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Contracts\EventObserver;
use EcoBin\Entities\User;
use EcoBin\Services\Mailer;

class EmailObserver implements EventObserver
{
    private const EMAILABLE_EVENTS = [
        'collection.assigned',
        'collection.completed',
        'recycling.approved',
        'appointment.updated',
        'reward.redeemed',
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private Mailer $mailer
    ) {}

    public function update(string $event, array $data): void
    {
        if (!in_array($event, self::EMAILABLE_EVENTS, true) || empty($data['user_id'])) {
            return;
        }

        $user = $this->em->find(User::class, (int)$data['user_id']);
        if (!$user || !$user->email) {
            return;
        }

        [$subject, $body] = $this->buildMessage($event, $data);

        $this->mailer->send($user->email, $subject, $body);
    }

    private function buildMessage(string $event, array $data): array
    {
        $message = $data['message'] ?? $this->defaultMessage($event);

        $subject = match ($event) {
            'collection.assigned' => 'EcoBin: Your collection has been scheduled',
            'collection.completed' => 'EcoBin: Your collection is complete',
            'recycling.approved' => 'EcoBin: Recycling submission approved',
            'appointment.updated' => 'EcoBin: Recycling appointment update',
            'reward.redeemed' => 'EcoBin: Reward redemption confirmed',
            default => 'EcoBin Notification',
        };

        $body = '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p style="color:#718078;font-size:12px;">This is an automated message from EcoBin Smart Waste Management.</p>';

        return [$subject, $body];
    }

    private function defaultMessage(string $event): string
    {
        return match ($event) {
            'collection.assigned' => 'Your waste collection has been scheduled.',
            'collection.completed' => 'Your waste collection has been completed.',
            'recycling.approved' => 'Your recycling submission was approved and points were awarded.',
            'appointment.updated' => 'Your recycling appointment status has changed.',
            'reward.redeemed' => 'Your reward redemption has been processed.',
            default => 'You have a new EcoBin notification.',
        };
    }
}