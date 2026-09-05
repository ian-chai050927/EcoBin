<?php
/*
 * @author  Jordan Liew Yi Xiang
 * @module  Module 2 — Waste Report & Collection Management
 */

namespace EcoBin\States\Collection;

class PendingState implements CollectionState
{
    public function getName(): string
    {
        return 'Pending';
    }

    public function canAssign(): bool
    {
        return true;
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
        return true;
    }
}