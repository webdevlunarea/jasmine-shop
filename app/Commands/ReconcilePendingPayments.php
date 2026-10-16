<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\Payments\PaymentExpiry;

/** Operator-scheduled status reads, never a bulk provider cancellation job. */
final class ReconcilePendingPayments extends BaseCommand
{
    protected $group = 'Payments';
    protected $name = 'payments:reconcile';
    protected $description = 'Periksa status pesanan pending yang mencapai batas waktu pembayaran.';
    protected $options = ['--limit'=>'Jumlah pesanan dipindai per jadwal (1–100, default 25).', '--dry-run'=>'Hanya hitung kandidat; tanpa API, update order, email, atau perubahan cursor.'];

    public function run(array $params)
    {
        $limit = max(1, min(100, (int)(CLI::getOption('limit') ?: 25)));
        $db = \Config\Database::connect();
        $cache = \Config\Services::cache();
        $cursorKey = 'pending-payments-scan-cursor';
        $cursor = max(0, (int)$cache->get($cursorKey));
        $read = static function (int $after) use ($db, $limit): array {
            return $db->table('pemesanan')->whereIn('status', ['Menunggu Pembayaran','Menunggu Pembayaran Rekening'])
                ->where('id >', $after)->orderBy('id', 'asc')->limit($limit)->get()->getResultArray();
        };
        $orders = $read($cursor);
        if (!$orders && $cursor > 0) $orders = $read(0);
        if (CLI::getOption('dry-run') !== null) {
            CLI::write('Dry run: ' . count($orders) . ' pesanan pending pada batch. Tidak ada perubahan.');
            return;
        }
        $worker = new class extends \App\Controllers\Pages {
            public function reconcile(array $order): array { return $this->reconcilePendingOrder($order, true); }
        };
        $checked = 0;
        $updated = 0;
        foreach ($orders as $order) {
            $payment = json_decode($order['data_mid'] ?? '', true) ?: [];
            if (PaymentExpiry::timestamp($payment) === null || PaymentExpiry::isDue($payment)) {
                $latest = $worker->reconcile($order);
                $checked++;
                if ($latest['status'] !== $order['status']) $updated++;
            }
            $cursor = (int)$order['id'];
        }
        // Keyset scan rotates past unchanged/temporarily failing orders instead of starving later rows.
        $cache->save($cursorKey, $orders ? $cursor : 0, 86400);
        CLI::write('Dipindai: ' . count($orders) . '; diperiksa: ' . $checked . '; status diperbarui: ' . $updated . '.');
    }
}
