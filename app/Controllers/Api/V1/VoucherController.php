<?php

namespace App\Controllers\Api\V1;

use App\Models\VoucherClaimedModel;

/** Read-only list of the authenticated customer's currently usable claims. */
final class VoucherController extends ApiController
{
    /** Public, non-sensitive promotion details for the Android launch offer. */
    public function promotions()
    {
        try {
            $model = new \App\Models\VoucherModel();
            $rows = $model
                ->select('id, nama, satuan, nominal, jenis, keterangan, all_user, auto_claimed, kuota, jadwal, max_potongan')
                ->getVoucher();

            $result = [];
            foreach ($rows as $voucher) {
                // Only globally available campaigns are suitable for a public response.
                if ((string)($voucher['all_user'] ?? '0') !== '1') continue;
                if ((int)($voucher['kuota'] ?? 0) === 0) continue;
                $result[] = [
                    'voucher_id' => (int)$voucher['id'],
                    'name' => (string)($voucher['nama'] ?? 'Promo Lunarea'),
                    'type' => (string)($voucher['jenis'] ?? ''),
                    'unit' => (string)($voucher['satuan'] ?? ''),
                    'nominal' => (int)($voucher['nominal'] ?? 0),
                    'description' => (string)($voucher['keterangan'] ?? ''),
                    'max_discount' => (int)($voucher['max_potongan'] ?? 0),
                    'requires_login' => true,
                ];
            }

            return $this->success(['promotions' => $result]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API public voucher promotions failed: ' . get_class($e));
            return $this->failure('VOUCHER_PROMOTIONS_UNAVAILABLE', 'Informasi promo belum dapat dimuat.', 503);
        }
    }

    public function index()
    {
        try {
            $email = $this->authEmail();
            $claims = (new VoucherClaimedModel())->getVoucherEmail($email);
            $today = new \DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));
            $result = [];
            foreach ($claims as $claim) {
                if ((string)($claim['voucher_active'] ?? '') !== '1') continue;
                if (!empty($claim['kadaluarsa']) && strtotime((string)$claim['kadaluarsa']) !== false
                    && $today->getTimestamp() > strtotime((string)$claim['kadaluarsa'])) continue;
                $schedule = explode('@', (string)($claim['jadwal'] ?? ''));
                if (count($schedule) >= 2 && $schedule[0] && $schedule[1]) {
                    $start = strtotime($schedule[0]);
                    $end = strtotime($schedule[1]);
                    if (($start !== false && $today->getTimestamp() < $start)
                        || ($end !== false && $today->getTimestamp() > $end)) continue;
                }
                $result[] = [
                    'claim_id' => (int)$claim['id'],
                    'name' => (string)($claim['nama'] ?? 'Voucher'),
                    'description' => (string)($claim['keterangan'] ?? ''),
                    'type' => (string)($claim['jenis'] ?? ''),
                    'unit' => (string)($claim['satuan'] ?? ''),
                    'nominal' => (int)($claim['nominal'] ?? 0),
                    'max_discount' => (int)($claim['max_potongan'] ?? 0),
                    'expires_at' => (string)($claim['kadaluarsa'] ?? ''),
                ];
            }
            return $this->success(['vouchers' => $result]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API voucher list failed: ' . get_class($e));
            return $this->failure('VOUCHERS_UNAVAILABLE', 'Daftar voucher belum dapat dimuat. Coba lagi.', 503);
        }
    }
}
