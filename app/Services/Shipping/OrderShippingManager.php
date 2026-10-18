<?php

namespace App\Services\Shipping;

use CodeIgniter\Database\BaseConnection;

class OrderShippingManager
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function update(string $orderId, array $input, bool $complete = false): array
    {
        $this->db->transBegin();
        try {
            $query = $this->db->table('pemesanan')->where('id_midtrans', $orderId)->getCompiledSelect();
            $order = $this->db->query($query . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$order) throw new \DomainException('Pesanan tidak ditemukan.');
            $current = (string)$order['status'];
            if (!in_array($current, ['Proses', 'Dikirim', 'Selesai'], true)) {
                throw new \DomainException('Pengiriman hanya bisa diperbarui untuk pesanan yang sudah dibayar.');
            }
            $hasShipments = $this->db->tableExists('order_shipments');
            $shipment = $hasShipments ? $this->db->table('order_shipments')->where('id_midtrans', $orderId)->get()->getRowArray() : null;
            $previousShippingStatus = (string)($shipment['shipping_status'] ?? match ($current) {
                'Proses' => 'Disiapkan',
                'Dikirim' => 'Dalam Pengiriman',
                'Selesai' => 'Terkirim',
                default => '',
            });
            $internal = ($shipment['shipping_method_code'] ?? '') === 'internal_delivery';
            $courier = trim((string)($input['kurir'] ?? $order['kurir'] ?? ''));
            $awb = trim((string)($input['resi'] ?? $order['resi'] ?? ''));
            if ($complete) {
                // Legacy completed shipments remain readable; this path does not import new sales.
                if (!in_array($current, ['Dikirim', 'Selesai'], true)) throw new \DomainException('Pesanan belum dikirim.');
                $next = 'Selesai';
                $shippingStatus = 'Terkirim';
            } else {
                if ($internal) $courier = 'Jasa Kirim Toko';
                elseif ($shipment && $courier !== trim((string)$order['kurir'])) {
                    throw new \DomainException('Kurir mengikuti pilihan checkout. Perubahan kurir memerlukan pemeriksaan terpisah.');
                }
                if (!\App\Services\Integrations\LunaOrderSync::validTracking($awb) || $courier === '' || strlen($courier) > 255
                    || preg_match('/[<>\x00-\x1F\x7F]/', $courier)) {
                    throw new \DomainException($internal ? 'Isi nomor resi internal toko yang valid.' : 'Isi nama kurir dan nomor resi yang valid.');
                }
                $shippingStatus = trim((string)($input['shipping_status'] ?? ($current === 'Proses' ? 'Disiapkan' : 'Dalam Pengiriman')));
                if (!in_array($shippingStatus, ['Disiapkan', 'Dalam Pengiriman', 'Gagal Kirim', 'Terkirim'], true)) throw new \DomainException('Status pengiriman tidak valid.');
                if ($shippingStatus === 'Disiapkan' && $current !== 'Proses') throw new \DomainException('Pesanan sudah keluar dari tahap persiapan.');
                if ($shippingStatus === 'Gagal Kirim' && $current !== 'Dikirim') throw new \DomainException('Pesanan belum dikirim.');
                if ($shippingStatus === 'Terkirim' && !in_array($current, ['Dikirim', 'Selesai'], true)) throw new \DomainException('Tandai barang dalam pengiriman sebelum mengonfirmasi diterima.');
                $next = $shippingStatus === 'Disiapkan' ? 'Proses' : ($shippingStatus === 'Terkirim' ? 'Selesai' : 'Dikirim');
            }
            if ($current === 'Selesai' && $next !== 'Selesai') throw new \DomainException('Pesanan selesai tidak boleh dikembalikan ke pengiriman.');
            $mid = json_decode((string)($order['data_mid'] ?? ''), true);
            if ($next === 'Dikirim' && ($mid['_app_luna_fulfillment']['stage'] ?? '') === 'received') {
                throw new \DomainException('Pesanan masih menunggu proses pusat. Periksa status MyLuna sebelum menandai pengiriman.');
            }
            if ($internal) $courier = 'Jasa Kirim Toko';
            $changes = ['status' => $next, 'resi' => $awb, 'kurir' => $courier];
            $changed = $next !== $current || $awb !== (string)$order['resi'] || $courier !== (string)$order['kurir']
                || ($shipment && $shippingStatus !== (string)$shipment['shipping_status']);
            $this->db->table('pemesanan')->where('id_midtrans', $orderId)->update($changes);
            if ($shipment) {
                // Quoted courier/service/cost stay immutable. Only fulfillment fields change.
                $shippingUpdatedAt = date('Y-m-d H:i:s');
                $this->db->table('order_shipments')->where('id', $shipment['id'])->update([
                    'tracking_number' => $awb,
                    'shipping_status' => $shippingStatus,
                    'updated_at' => $shippingUpdatedAt,
                ]);
                $shipment = array_merge($shipment, ['tracking_number' => $awb, 'shipping_status' => $shippingStatus, 'updated_at' => $shippingUpdatedAt]);
            } else {
                $shippingUpdatedAt = date('Y-m-d H:i:s');
            }
            if ($previousShippingStatus !== $shippingStatus && $this->db->tableExists('customer_push_notifications')) {
                $pushCopy = \App\Services\Notifications\ShippingStatusNotification::for($shippingStatus, $orderId);
                if ($pushCopy !== null && filter_var((string)($order['email_cus'] ?? ''), FILTER_VALIDATE_EMAIL)) {
                    $queued = (new \App\Services\Notifications\CustomerPushNotifications($this->db))->enqueue(
                        (string)$order['email_cus'], 'shipping_status', $pushCopy['title'], $pushCopy['body'],
                        ['order_id' => $orderId, 'status' => $shippingStatus, 'tracking_number' => $awb, 'courier' => $courier],
                        'shipping:' . $orderId . ':' . $previousShippingStatus . ':' . $shippingStatus . ':' . $shippingUpdatedAt
                    );
                    if (!$queued) throw new \RuntimeException('Shipping status notification was not recorded.');
                }
            }
            if (!$this->db->transStatus()) throw new \RuntimeException('Shipping transaction failed');
            if (!$this->db->transCommit()) throw new \RuntimeException('Shipping transaction commit failed');
            return [
                'order' => array_merge($order, $changes),
                'shipment' => $shipment,
                'changed' => (bool)$changed,
                'previous_shipping_status' => $previousShippingStatus,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
