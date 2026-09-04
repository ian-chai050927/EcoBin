<?php
/*
 * @author EcoBin Team — Module 2 (Waste Collection)
 * Authorization helper for CollectionRequest ownership checks.
 * Uses ORM associations ($collection->resident, $collection->collectionStaff)
 * instead of raw integer FK fields.
 */

namespace EcoBin\Services;

use EcoBin\Entities\CollectionRequest;

class CollectionAuthorization
{
    public static function ensureResidentOwns(
        CollectionRequest $collection
    ): void {

        if (
            empty(
                $_SESSION[
                    'user_id'
                ]
            )
        ) {
            http_response_code(401);

            exit(
                'Unauthorized.'
            );
        }


        /*
         * Compare via ORM association: $collection->resident is a User object.
         * We compare its ID against the session user ID.
         */
        if (
            $collection->resident->id
            !==
            (int)
            $_SESSION[
                'user_id'
            ]
        ) {

            http_response_code(403);

            exit(
                '403 Forbidden: '
                .
                'You do not own this collection request.'
            );
        }
    }


    public static function ensureAssignedStaff(
        CollectionRequest $collection
    ): void {

        /*
         * $collection->collectionStaff is a User object (or null if unassigned).
         */
        if (
            ($collection->collectionStaff?->id)
            !==
            (int)
            $_SESSION[
                'user_id'
            ]
        ) {

            http_response_code(403);

            exit(
                '403 Forbidden: '
                .
                'This collection job '
                .
                'is not assigned to you.'
            );
        }
    }
}