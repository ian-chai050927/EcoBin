<?php

namespace EcoBin\Services;

use EcoBin\States\Collection\CollectionState;
use EcoBin\States\Collection\PendingState;
use EcoBin\States\Collection\AssignedState;
use EcoBin\States\Collection\InProgressState;
use EcoBin\States\Collection\CompletedState;
use EcoBin\States\Collection\CancelledState;

class CollectionWorkflow
{
    private CollectionState $state;

    public function __construct(
        string $currentStatus
    ) {
        $this->state =
            $this->createState(
                $currentStatus
            );
    }


    public function getState(): CollectionState
    {
        return $this->state;
    }


    public function assign(): string
    {
        if (
            !$this->state
                ->canAssign()
        ) {
            throw new \RuntimeException(
                'Collection cannot be assigned from status: '
                .
                $this->state->getName()
            );
        }

        $this->state =
            new AssignedState();

        return
            $this->state
                ->getName();
    }


    public function start(): string
    {
        if (
            !$this->state
                ->canStart()
        ) {
            throw new \RuntimeException(
                'Collection cannot be started from status: '
                .
                $this->state->getName()
            );
        }

        $this->state =
            new InProgressState();

        return
            $this->state
                ->getName();
    }


    public function complete(): string
    {
        if (
            !$this->state
                ->canComplete()
        ) {
            throw new \RuntimeException(
                'Collection cannot be completed from status: '
                .
                $this->state->getName()
            );
        }

        $this->state =
            new CompletedState();

        return
            $this->state
                ->getName();
    }


    public function cancel(): string
    {
        if (
            !$this->state
                ->canCancel()
        ) {
            throw new \RuntimeException(
                'Collection cannot be cancelled from status: '
                .
                $this->state->getName()
            );
        }

        $this->state =
            new CancelledState();

        return
            $this->state
                ->getName();
    }


    private function createState(
        string $status
    ): CollectionState {

        return match (
            $status
        ) {

            'Pending'
                => new PendingState(),

            'Assigned'
                => new AssignedState(),

            'In Progress'
                => new InProgressState(),

            'Completed'
                => new CompletedState(),

            'Cancelled'
                => new CancelledState(),

            default
                => new PendingState()
        };
    }
}