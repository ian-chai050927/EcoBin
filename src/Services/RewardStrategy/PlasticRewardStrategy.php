<?php
namespace EcoBin\Services\RewardStrategy;

class PlasticRewardStrategy implements RewardCalculatorInterface
{
    // E.g., Plastic gives 15 points per kg
    public function calculate(float $weightKg): int
    {
        return max(1, (int)round($weightKg * 15));
    }
}
