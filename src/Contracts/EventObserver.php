<?php
namespace EcoBin\Contracts;

interface EventObserver
{
    public function update(string $event, array $data): void;
}
