<?php

namespace App\Services\Payments;

/** Resolves the buyer's glass-protection choice without PHP's truthy-string trap. */
final class GlassProtectionSelection
{
    public static function resolve($submitted, $sessionSelection, int $eligibleAmount): bool
    {
        if ($eligibleAmount <= 0) return false;

        $value = $submitted !== null ? $submitted : $sessionSelection;
        if (!is_scalar($value)) return false;

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) === true;
    }
}
