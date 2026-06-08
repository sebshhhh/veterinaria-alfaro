<?php

namespace App\Support;

class PhotoUrl
{
    public static function make(?string $path): string
    {
        $cleanPath = trim((string) $path, '/');

        return $cleanPath !== ''
            ? url('/pet-photo/' . $cleanPath)
            : asset('img/default-pet.svg');
    }
}
