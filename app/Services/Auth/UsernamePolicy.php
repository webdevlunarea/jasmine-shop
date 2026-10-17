<?php

namespace App\Services\Auth;

final class UsernamePolicy
{
    public const PATTERN = '/^[a-z][a-z0-9_]{2,29}$/D';

    public static function normalize($value): ?string
    {
        if (!is_string($value)) return null;
        $name = strtolower(trim($value));
        if (!preg_match(self::PATTERN, $name)) return null;
        if (in_array($name, ['admin', 'administrator', 'root', 'system', 'support', 'lunarea', 'google', 'facebook', 'tamu'], true)) return null;
        return $name;
    }
}
