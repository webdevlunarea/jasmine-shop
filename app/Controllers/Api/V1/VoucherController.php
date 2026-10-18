<?php

namespace App\Controllers\Api\V1;

use App\Models\VoucherClaimedModel;

/** Read-only list of the authenticated customer's currently usable claims. */
final class VoucherController extends ApiController
{
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
