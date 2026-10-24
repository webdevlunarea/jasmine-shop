<?php

namespace App\Services\Auth;

use Config\Database;

/** Rejects common disposable-email domains and administrator-maintained blocks. */
class DisposableEmailPolicy
{
    private const BASELINE = [
        // Disposable inbox providers (keep this curated; never block common freemail domains).
        '10minutemail.com', '10minutemail.net', '10minemail.com', '1secmail.com',
        'burnermail.io', 'disposablemail.com', 'dispostable.com', 'emailfake.com',
        'emailondeck.com', 'emailtemporanea.net', 'fakeinbox.com', 'getairmail.com',
        'getnada.com', 'guerrillamail.com', 'guerrillamailblock.com', 'inboxkitten.com',
        'mail.tm', 'mail.gw', 'mailcatch.com', 'maildrop.cc', 'mailinator.com',
        'mailnesia.com', 'mintemail.com', 'mohmal.com', 'mytemp.email',
        'sharklasers.com', 'spam4.me', 'temp-mail.io', 'temp-mail.org',
        'tempmail.com', 'tempmail.plus', 'tempmail.ninja', 'tempmailo.com',
        'throwaway.email', 'throwawaymail.com', 'trashmail.com', 'yopmail.com',
        // Forwarding/alias providers commonly used to create throwaway signup identities.
        'addy.io', 'anonaddy.com', 'anonaddy.me', 'duck.com', 'mozmail.com',
        'simplelogin.com', 'simplelogin.co', 'simplelogin.io', 'relay.firefox.com',
        'privaterelay.appleid.com',
    ];

    public function isBlocked(string $email): bool
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        [$localPart, $emailDomain] = explode('@', $email, 2);
        // Plus-addressing is a widely used alias convention; block it consistently
        // so disposable registrations cannot evade one-account-per-address checks.
        if (str_contains($localPart, '+')) return true;
        $domain = substr(strrchr($email, '@'), 1);
        foreach (self::BASELINE as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) return true;
        }
        if ((new DisposableEmailDomainList())->isListed($domain)) return true;
        try {
            $db = Database::connect();
            // Gmail ignores dots in the local part and treats googlemail.com as
            // gmail.com. Reject a second account using that equivalent address.
            if (in_array($emailDomain, ['gmail.com', 'googlemail.com'], true) && $db->tableExists('user')) {
                $canonicalLocal = str_replace('.', '', explode('+', $localPart, 2)[0]);
                $canonical = $canonicalLocal . '@gmail.com';
                $existing = $db->query('SELECT 1 FROM `user` WHERE LOWER(`email`) = ? OR (LOWER(`email`) REGEXP \'@(gmail\\.com|googlemail\\.com)$\' AND REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(LOWER(`email`), \'@\', 1), \'+\', 1), \'.\', \'\') = ?) LIMIT 1', [
                    $canonical,
                    $canonicalLocal,
                ])->getRowArray();
                if ($existing) return true;
            }
            if (!$db->tableExists('customer_blocked_email_domains')) return false;
            $candidates = [];
            $candidate = $domain;
            while (str_contains($candidate, '.')) {
                $candidates[] = $candidate;
                $candidate = substr($candidate, strpos($candidate, '.') + 1);
            }
            if ($candidates && $db->table('customer_blocked_email_domains')->select('id')->where('is_active', 1)
                ->whereIn('domain', $candidates)->limit(1)->get()->getRowArray()) return true;
        } catch (\Throwable $e) {
            log_message('error', 'Pemeriksaan domain email tambahan gagal: {type}', ['type' => get_class($e)]);
        }
        return false;
    }
}
