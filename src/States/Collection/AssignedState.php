<?php
/*
 * @author  Jordan Liew Yi Xiang
 * @module  Module 2 — Waste Report & Collection Management
 */

namespace EcoBin\States\Collection;

class AssignedState implements CollectionState
{
    public function getName(): string
    {
        return 'Assigned';
    }

    public function canAssign(): bool
    {
        return true;
    }

    public function canStart(): bool
    {
        return true;
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