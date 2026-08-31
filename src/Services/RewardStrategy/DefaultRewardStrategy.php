<?php
namespace EcoBin\Services\RewardStrategy;

class DefaultRewardStrategy implements RewardCalculatorInterface
{
    // Default multiplier
    public function calculate(float $weightKg): int
    {
        return max(1, (int)round($weightKg * 5));
    }
}
