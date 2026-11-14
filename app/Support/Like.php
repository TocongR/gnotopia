<?php

namespace App\Support;

class Like
{
    /**
     * A "contains" pattern for a LIKE query written with `escape '!'`.
     * "!" is the escape character, so a typed % or _ matches itself instead of acting as a wildcard.
     */
    public static function contains(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
    }
}
