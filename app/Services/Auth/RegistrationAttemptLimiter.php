<?php

namespace App\Services\Auth;

/** Shared registration limits for website and Android/API entry points. */
final class RegistrationAttemptLimiter
{
    private $check;

    public function __construct(?callable $check = null)
    {
        $this->check = $check ?? static fn(string $key, int $limit, int $seconds): bool =>
            service('throttler')->check($key, $limit, $seconds);
    }

    public function allow(string $ipAddress, string $identity): bool
    {
        $identity = mb_strtolower(trim($identity));
        $ipKey = 'lunarea-register-ip-' . hash('sha256', trim($ipAddress));
        $identityKey = $identity === '' ? null : 'lunarea-register-identity-' . hash('sha256', $identity);

        // CI's file cache token-bucket read/update is not one atomic operation.
        // Serialize registration checks on this host so parallel PHP workers do not
        // all consume the same cached token at once.
        $lockPath = WRITEPATH . 'cache/registration-attempts.lock';
        $lock = @fopen($lockPath, 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            if (is_resource($lock)) fclose($lock);
            return false;
        }

        try {
            $allowed = ($this->check)($ipKey, 5, 3600);
            $identityAllowed = $identityKey === null || ($this->check)($identityKey, 3, 3600);
            return $allowed && $identityAllowed;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
