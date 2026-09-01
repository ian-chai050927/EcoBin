<?php
namespace EcoBin\Services\RewardStrategy;

class DefaultRewardStrategy implements RewardCalculatorInterface
{
    public function __construct(private int $pointsPerKg = 5) {}

    public function calculate(float $weightKg): int
    {
        return max(1, (int)round($weightKg * $this->pointsPerKg));
    }
}