<?php
/*
 * @author  Jordan Liew Yi Xiang
 * @module  Module 2 — Waste Report & Collection Management
 */

namespace EcoBin\States\Collection;

class CompletedState implements CollectionState
{
    public function getName(): string
    {
        return 'Completed';
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