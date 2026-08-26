<?php

namespace EcoBin\States\Collection;

interface CollectionState
{
    public function getName(): string;

    public function canAssign(): bool;

    public function canStart(): bool;

    public function canComplete(): bool;

    public function canCancel(): bool;
}