<?php

namespace EcoBin\Services;

class SecureImageUploader
{
    private const MAX_SIZE =
        5 * 1024 * 1024;


    private const ALLOWED_TYPES = [

        'image/jpeg'
            => 'jpg',

        'image/png'
            => 'png',

        'image/webp'
            => 'webp'

    ];


    public function uploadMultiple(
        array $files,
        string $folder,
        int $maxFiles = 5
    ): array {

        if (
            empty(
                $files['name']
            )
        ) {
            return [];
        }


        if (
            !is_array(
                $files['name']
            )
        ) {

            $files = [

                'name'
                    => [
                        $files['name']
                    ],

                'tmp_name'
                    => [
                        $files['tmp_name']
                    ],

                'error'
                    => [
                        $files['error']
                    ],

                'size'
                    => [
                        $files['size']
                    ]

            ];
        }


        if (
            count(
                $files['name']
            )
            >
            $maxFiles
        ) {

            throw new \RuntimeException(
                "Maximum {$maxFiles} images allowed."
            );
        }


        $saved = [];


        foreach (
            $files['name']
            as
            $index => $name
        ) {

            $error =
                $files['error']
                [$index]
                ??
                UPLOAD_ERR_NO_FILE;


            if (
                $error
                ===
                UPLOAD_ERR_NO_FILE
            ) {
                continue;
            }


            if (
                $error
                !==
                UPLOAD_ERR_OK
            ) {
                throw new \RuntimeException(
                    'Image upload failed.'
                );
            }


            $size =
                (int)
                (
                    $files['size']
                    [$index]
                    ??
                    0
                );


            if (
                $size
                >
                self::MAX_SIZE
            ) {

                throw new \RuntimeException(
                    'Each image must be 5MB or smaller.'
                );
            }


            $tmp =
                $files['tmp_name']
                [$index];


            if (
                !is_uploaded_file(
                    $tmp
                )
            ) {

                throw new \RuntimeException(
                    'Invalid uploaded file.'
                );
            }


            $finfo =
                new \finfo(
                    FILEINFO_MIME_TYPE
                );


            $mime =
                $finfo->file(
                    $tmp
                );


            if (
                !isset(
                    self::ALLOWED_TYPES[
                        $mime
                    ]
                )
            ) {

                throw new \RuntimeException(
                    'Only JPG, PNG and WEBP images are allowed.'
                );
            }


            /*
             * Additional defence:
             * verify that PHP can actually
             * parse the file as an image.
             */
            $imageInfo =
                @getimagesize(
                    $tmp
                );


            if (
                $imageInfo
                ===
                false
            ) {

                throw new \RuntimeException(
                    'Uploaded file is not a valid image.'
                );
            }


            $extension =
                self::ALLOWED_TYPES[
                    $mime
                ];


            /*
             * Never use original filename.
             *
             * Prevents:
             * shell.php.jpg
             * directory traversal
             * filename collisions
             */
            $filename =

                bin2hex(
                    random_bytes(
                        24
                    )
                )

                . '.'

                . $extension;


            $directory =

                __DIR__
                .
                '/../../uploads/'
                .
                $folder
                .
                '/';


            if (
                !is_dir(
                    $directory
                )
            ) {

                mkdir(
                    $directory,
                    0755,
                    true
                );
            }


            $destination =

                $directory
                .
                $filename;


            if (
                !move_uploaded_file(
                    $tmp,
                    $destination
                )
            ) {

                throw new \RuntimeException(
                    'Unable to save image.'
                );
            }


            $saved[] =

                'uploads/'
                .
                $folder
                .
                '/'
                .
                $filename;
        }


        return $saved;
    }
}