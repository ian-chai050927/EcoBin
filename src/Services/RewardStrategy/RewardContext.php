<?php
namespace EcoBin\Services\RewardStrategy;

class RewardContext
{
    public static function getStrategy(string $material): RewardCalculatorInterface
    {
        $materialLower = strtolower(trim($material));
        
        if (str_contains($materialLower, 'plastic')) {
            return new PlasticRewardStrategy();
        }
        if (str_contains($materialLower, 'metal')) {
            return new MetalRewardStrategy();
        }
        if (str_contains($materialLower, 'paper')) {
            return new PaperRewardStrategy();
        }
        
        return new DefaultRewardStrategy();
    }
}
