<?php
namespace EcoBin\Services;

use EcoBin\Contracts\EventObserver;

class EventDispatcher
{
    private array $observers = [];

    public function attach(EventObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function dispatch(string $event, array $data = []): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }
}
