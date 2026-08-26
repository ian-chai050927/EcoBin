<?php

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


        if (
            $collection->residentId
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

        if (
            $collection
                ->collectionStaffId
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