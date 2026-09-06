<?php
/**
 * Module 3: Recycling & Reward Management
 * @author Ho Ze-Yang
 */
namespace EcoBin\Services\RewardStrategy;

class MetalRewardStrategy implements RewardCalculatorInterface
{
    // E.g., Metal gives 20 points per kg
    public function calculate(float $weightKg): int
    {
        return max(1, (int)round($weightKg * 20));
    }
}
