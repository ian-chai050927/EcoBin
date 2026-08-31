<?php
namespace EcoBin\Services\RewardStrategy;

interface RewardCalculatorInterface
{
    /**
     * Calculate reward points based on the weight of the material.
     *
     * @param float $weightKg
     * @return int
     */
    public function calculate(float $weightKg): int;
}
