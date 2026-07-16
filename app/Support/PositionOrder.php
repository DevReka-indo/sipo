<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PositionOrder
{
    public static function map(): array
    {
        return [
            // Presiden Direktur
            23 => 1,  // Presiden Direktur
            24 => 2,  // P.J. Presiden Direktur
            25 => 3,  // Plt. Presiden Direktur

            // Direktur
            1  => 4,  // Direktur
            26 => 5,  // P.J. Direktur
            27 => 6,  // Plt. Direktur

            // Setara General Manager
            2  => 7,  // General Manager
            29 => 8,  // Sp. Spesialis Utama
            13 => 9,  // P.J. General Manager
            12 => 10, // Plt. General Manager
            28 => 11, // Plt. Spesialis Utama

            // Setara Senior Manager
            3  => 12, // Senior Manager
            14 => 13, // Spesialis Madya
            4  => 14, // P.J. Senior Manager
            17 => 15, // P.J. Spesialis Madya
            11 => 16, // Plt. Senior Manager
            20 => 17, // Plt. Spesialis Madya

            // Setara Manager
            5  => 18, // Manager
            16 => 19, // Spesialis Muda
            30 => 20, // Project Manager
            7  => 21, // P.J. Manager
            19 => 22, // P.J. Spesialis Muda
            10 => 23, // Plt. Manager
            22 => 24, // Plt. Spesialis Muda

            // Setara Supervisor
            6  => 25, // Supervisor
            15 => 26, // Spesialis Pratama
            8  => 27, // P.J. Supervisor
            18 => 28, // P.J. Spesialis Pratama
            21 => 29, // Plt. Spesialis Pratama

            // Staff
            9  => 30, // Staff
        ];
    }

    public static function value(?int $positionId): int
    {
        if ($positionId === null) {
            return 999;
        }

        return self::map()[$positionId] ?? 999;
    }

    public static function sortUsers(Collection $users): Collection
    {
        return $users
            ->sortBy(function ($user) {
                return self::value((int) ($user->position_id_position ?? 0));
            })
            ->values();
    }
}
