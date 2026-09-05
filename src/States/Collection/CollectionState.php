<?php
/*
 * @author  Jordan Liew Yi Xiang
 * @module  Module 2 — Waste Report & Collection Management
 */

namespace EcoBin\States\Collection;

interface CollectionState
{
    public function getName(): string;

    public function canAssign(): bool;

    public function canStart(): bool;

    public function canComplete(): bool;

    public function canCancel(): bool;
}