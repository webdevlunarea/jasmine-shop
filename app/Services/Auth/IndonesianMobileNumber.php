<?php

namespace App\Services\Auth;

/** Validates and canonicalizes Indonesian mobile numbers for customer signup. */
class IndonesianMobileNumber
{
    public function normalize(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || !preg_match('/^[+0-9\s().-]+$/', $value)
            || substr_count($value, '+') > 1 || (str_contains($value, '+') && !str_starts_with($value, '+'))) return null;
        $digits = preg_replace('/\D+/', '', $value);
        $international = false;
        if (str_starts_with($value, '+')) {
            if (!str_starts_with($digits, '62')) return null;
            $digits = substr($digits, 2);
            $international = true;
        } elseif (str_starts_with($digits, '0062')) {
            $digits = substr($digits, 4);
            $international = true;
        } elseif (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
            $international = true;
        }

        if ($international) {
            if (!str_starts_with($digits, '8')) return null;
            $national = '0' . $digits;
        } else {
            if (str_starts_with($digits, '0')) $national = $digits;
            elseif (str_starts_with($digits, '8')) $national = '0' . $digits;
            else return null;
        }
        if (!preg_match('/^08[1-9][0-9]{7,10}$/', $national)) return null;

        $subscriber = substr($national, 2);
        if (preg_match('/(\d)\1{6,}/', $subscriber)) return null;
        for ($i = 0; $i <= strlen($subscriber) - 7; $i++) {
            $sequence = substr($subscriber, $i, 7);
            if ($sequence === '0123456' || $sequence === '1234567' || $sequence === '2345678'
                || $sequence === '3456789' || $sequence === '9876543' || $sequence === '8765432'
                || $sequence === '7654321' || $sequence === '6543210') return null;
        }
        return $national;
    }
}
