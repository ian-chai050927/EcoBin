<?php

namespace EcoBin\States\Collection;

class InProgressState implements CollectionState
{
    public function getName(): string
    {
        return 'In Progress';
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
        return true;
    }

    public function canCancel(): bool
    {
        return false;
    }
}