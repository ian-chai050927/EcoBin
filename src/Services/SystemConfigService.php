<?php
namespace EcoBin\Services;

use Doctrine\ORM\EntityManagerInterface;
use EcoBin\Entities\SystemConfig;

class SystemConfigService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function listAll(): array
    {
        return $this->em->getRepository(SystemConfig::class)->findAll();
    }

    public function get(string $key): ?string
    {
        $c = $this->em->find(SystemConfig::class, $key);
        return $c?->value;
    }

    public function set(string $key, string $value): SystemConfig
    {
        $key = preg_replace('/[^A-Za-z0-9_.-]/', '', $key);
        if ($key === '') {
            throw new \InvalidArgumentException('Invalid configuration key.');
        }

        $c = $this->em->find(SystemConfig::class, $key) ?? new SystemConfig();
        $c->key = $key;
        $c->value = trim($value);
        $c->updatedAt = new \DateTime();

        $this->em->persist($c);
        $this->em->flush();

        return $c;
    }
}