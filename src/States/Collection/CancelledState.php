<?php

namespace EcoBin\States\Collection;

class CancelledState implements CollectionState
{
    public function getName(): string
    {
        return 'Cancelled';
    }

    public function canAssign(): bool
    {
        return false;
    }

    public function canStart(): bool
    {
        return false;
    }

    public function canComplete(): bool
    {
        return false;
    }

    public function canCancel(): bool
    {
        return false;
    }
}