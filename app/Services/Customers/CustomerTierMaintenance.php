<?php

namespace App\Services\Customers;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;
use DateTimeZone;

final class CustomerTierMaintenance
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /** Return null when the stored value is malformed; malformed data is never overwritten. */
    public static function refreshedTier(array $tier, ?DateTimeImmutable $today = null): ?array
    {
        if (!isset($tier['data']) || !is_array($tier['data'])) return null;

        $timezone = new DateTimeZone('Asia/Jakarta');
        $today = ($today ?? new DateTimeImmutable('now', $timezone))->setTimezone($timezone)->setTime(0, 0);
        $active = [];
        $total = 0;

        foreach ($tier['data'] as $entry) {
            if (!is_array($entry) || !isset($entry['kadaluarsa'], $entry['nominal'])
                || !is_numeric($entry['nominal'])
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', (string)$entry['kadaluarsa'])) {
                return null;
            }
            $expiry = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$entry['kadaluarsa'], $timezone);
            $dateErrors = DateTimeImmutable::getLastErrors();
            if (!$expiry || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) return null;
            if ($expiry >= $today) {
                $active[] = $entry;
                $total += (int)$entry['nominal'];
            }
        }

        $tier['label'] = match (true) {
            $total < 10000000 => 'bronze',
            $total < 50000000 => 'silver',
            $total < 100000000 => 'gold',
            default => 'platinum',
        };
        $tier['data'] = $active;
        return $tier;
    }

    /** Process one keyset-paginated batch without loading the full customer table. */
    public function refreshBatch(string $afterEmail, int $limit = 100): array
    {
        $limit = min(500, max(1, $limit));
        $builder = $this->db->table('pembeli')->select('email_user')->orderBy('email_user', 'ASC')->limit($limit);
        if ($afterEmail !== '') $builder->where('email_user >', $afterEmail);
        $customers = $builder->get()->getResultArray();
        $updated = 0;
        $skipped = 0;
        $lastEmail = $afterEmail;

        foreach ($customers as $customer) {
            $email = (string)($customer['email_user'] ?? '');
            if ($email === '') { $skipped++; continue; }
            $lastEmail = $email;
            $this->db->transBegin();
            try {
                $sql = 'SELECT email_user, tier FROM pembeli WHERE email_user = ? LIMIT 1';
                if ($this->db->DBDriver !== 'SQLite3') $sql .= ' FOR UPDATE';
                $current = $this->db->query($sql, [$email])->getRowArray();
                if (!$current) {
                    $this->db->transCommit();
                    continue;
                }

                $tier = json_decode((string)($current['tier'] ?? ''), true);
                $nextTier = is_array($tier) ? self::refreshedTier($tier) : null;
                if ($nextTier === null) {
                    $skipped++;
                    $this->db->transCommit();
                    continue;
                }

                if ($nextTier !== $tier) {
                    $ok = $this->db->table('pembeli')->where('email_user', $email)
                        ->update(['tier' => json_encode($nextTier, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)]);
                    if (!$ok) throw new \RuntimeException('Tier update failed.');
                    $updated += $this->db->affectedRows() > 0 ? 1 : 0;
                }
                $this->db->transCommit();
            } catch (\Throwable $error) {
                $this->db->transRollback();
                throw $error;
            }
        }

        return ['read' => count($customers), 'updated' => $updated, 'skipped' => $skipped, 'last_email' => $lastEmail];
    }
}
