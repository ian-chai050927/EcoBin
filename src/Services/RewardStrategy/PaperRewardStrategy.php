<?php
namespace EcoBin\Services\RewardStrategy;

class PaperRewardStrategy implements RewardCalculatorInterface
{
    // E.g., Paper gives 10 points per kg
    public function calculate(float $weightKg): int
    {
        return max(1, (int)round($weightKg * 10));
    }
}
