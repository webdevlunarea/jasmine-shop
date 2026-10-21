<?php

namespace App\Controllers\Api\V1;

use App\Models\PembeliModel;
use App\Models\PointHistoryModel;

/** Read-only Luna Poin balance and ledger for the authenticated Android customer. */
final class PointsController extends ApiController
{
    public function index()
    {
        try {
            $email = $this->authEmail();
            $customer = (new PembeliModel())->getPembeli($email);
            if (!$customer) {
                return $this->failure('POINTS_ACCOUNT_UNAVAILABLE', 'Data akun Luna Poin belum tersedia.', 404);
            }

            $batches = json_decode((string)($customer['poin'] ?? '[]'), true);
            $batches = is_array($batches) ? $batches : [];
            $today = new \DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));
            $balance = 0;
            $nextExpiry = null;
            $expiring = [];
            foreach ($batches as $batch) {
                if (!is_array($batch) || empty($batch['active'])) continue;
                $nominal = max(0, (int)($batch['nominal'] ?? 0));
                $expiry = trim((string)($batch['kadaluarsa'] ?? ''));
                $expiryTime = $expiry !== '' ? strtotime($expiry) : false;
                if ($expiryTime !== false && $today->getTimestamp() > $expiryTime) continue;
                $balance += $nominal;
                if ($expiry !== '') {
                    $expiring[] = ['amount' => $nominal, 'expires_at' => $expiry];
                    if ($nextExpiry === null || $expiry < $nextExpiry) $nextExpiry = $expiry;
                }
            }

            $tierData = json_decode((string)($customer['tier'] ?? '{}'), true);
            $tierData = is_array($tierData) ? $tierData : [];
            $tierLabel = strtolower(trim((string)($tierData['label'] ?? 'bronze')));
            $tierRules = [
                'bronze' => ['label' => 'Bronze', 'min' => 0, 'next' => 10000000],
                'silver' => ['label' => 'Silver', 'min' => 10000000, 'next' => 50000000],
                'gold' => ['label' => 'Gold', 'min' => 50000000, 'next' => 100000000],
                'platinum' => ['label' => 'Platinum', 'min' => 100000000, 'next' => null],
            ];
            if (!isset($tierRules[$tierLabel])) $tierLabel = 'bronze';
            $tierRows = is_array($tierData['data'] ?? null) ? $tierData['data'] : [];
            $tierSpend = array_sum(array_map(static fn($row): int => is_array($row) ? max(0, (int)($row['nominal'] ?? 0)) : 0, $tierRows));
            $benefits = [
                'bronze' => [
                    ['amount' => '25k', 'name' => 'Bonus Ulang Tahun', 'description' => 'Dapatkan 25.000 poin bonus pada bulan ulang tahunmu!'],
                    ['amount' => '5%', 'name' => 'Diskon Produk Private Label', 'description' => 'Dapatkan diskon 5% untuk barang-barang private label'],
                    ['amount' => '', 'name' => 'Voucher Bulanan Eksklusif', 'description' => 'Dapatkan voucher belanja eksklusif setiap bulan pada program spesial'],
                    ['amount' => '', 'name' => 'Dapatkan & Gunakan Poin', 'description' => 'Dapatkan poin yang bisa kamu gunakan untuk bertransaksi'],
                ],
                'silver' => [ ['amount' => '10%', 'name' => 'Voucher Diskon', 'description' => 'Nikmati diskon hingga 10% untuk barang tertentu tiap bulannya'] ],
                'gold' => [
                    ['amount' => '25k', 'name' => 'Voucher Ulang Tahun', 'description' => 'Nikmati voucher belanja eksklusif senilai Rp25.000 di hari ulang tahunmu.'],
                    ['amount' => '', 'name' => 'Early Access untuk Promo', 'description' => 'Akses ke berbagai produk dan promo lebih awal dari pengguna lainnya.'],
                ],
                'platinum' => [
                    ['amount' => '', 'name' => 'Customer Care Khusus', 'description' => 'Jalur customer care khusus yang tersedia 24 jam.'],
                    ['amount' => '50k', 'name' => 'Diskon Khusus', 'description' => 'Diskon khusus senilai Rp50.000 per bulan dengan minimum pembelian.'],
                ],
            ];
            $tierCatalog = [];
            foreach ($tierRules as $key => $rule) {
                $tierCatalog[] = [
                    'key' => $key,
                    'label' => $rule['label'],
                    'minimum' => $rule['min'],
                    'next_minimum' => $rule['next'],
                    'benefits' => $benefits[$key],
                ];
            }

            $historyModel = new PointHistoryModel();
            $page = max(1, min(1000, (int)$this->request->getGet('page')));
            $perPage = 30;
            $historyModel->where('email_user', $email);
            $total = $historyModel->countAllResults(false);
            $history = $historyModel->orderBy('tanggal', 'DESC')->orderBy('id', 'DESC')
                ->findAll($perPage, ($page - 1) * $perPage);
            $entries = array_map(static fn(array $row): array => [
                'id' => (int)($row['id'] ?? 0),
                'label' => (string)($row['label'] ?? 'Perubahan poin'),
                'amount' => (int)($row['nominal'] ?? 0),
                'description' => (string)($row['keterangan'] ?? ''),
                'date' => (string)($row['tanggal'] ?? ''),
            ], $history);

            return $this->success(['balance' => $balance, 'next_expiry' => $nextExpiry,
                'expiring_batches' => $expiring, 'history' => $entries,
                'tier' => ['key' => $tierLabel, 'label' => $tierRules[$tierLabel]['label'],
                    'spend' => $tierSpend, 'minimum' => $tierRules[$tierLabel]['min'],
                    'next_minimum' => $tierRules[$tierLabel]['next'],
                    'progress' => $tierRules[$tierLabel]['next'] === null ? 100 : max(0, min(100,
                        (int)round(($tierSpend - $tierRules[$tierLabel]['min']) / max(1, $tierRules[$tierLabel]['next'] - $tierRules[$tierLabel]['min']) * 100)))],
                    'benefits' => $benefits[$tierLabel], 'birthday_registered' => !empty($customer['tgl_lahir']),
                'tier_levels' => $tierCatalog,
                'page' => $page, 'per_page' => $perPage, 'total_history' => $total]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API Luna points read failed: ' . get_class($e));
            return $this->failure('POINTS_UNAVAILABLE', 'Informasi Luna Poin belum dapat dimuat. Coba lagi.', 503);
        }
    }
}
