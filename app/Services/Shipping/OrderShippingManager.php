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
            $internal = ($shipment['shipping_method_code'] ?? '') === 'internal_delivery';
            $courier = trim((string)($input['kurir'] ?? $order['kurir'] ?? ''));
            $awb = trim((string)($input['resi'] ?? $order['resi'] ?? ''));
            if ($complete) {
                if (!in_array($current, ['Dikirim', 'Selesai'], true)) throw new \DomainException('Pesanan belum dikirim.');
                $next = 'Selesai';
                $shippingStatus = 'Terkirim';
            } elseif ($internal) {
                $shippingStatus = trim((string)($input['shipping_status'] ?? 'Dalam Pengiriman'));
                if (!in_array($shippingStatus, ['Disiapkan', 'Dalam Pengiriman', 'Gagal Kirim', 'Terkirim'], true)) {
                    throw new \DomainException('Status pengiriman toko tidak valid.');
                }
                if ($shippingStatus === 'Terkirim' && !in_array($current, ['Dikirim', 'Selesai'], true)) {
                    throw new \DomainException('Tandai barang dalam pengiriman sebelum mengonfirmasi diterima.');
                }
                if ($shippingStatus === 'Gagal Kirim' && $current !== 'Dikirim') throw new \DomainException('Pesanan belum dikirim.');
                if ($shippingStatus === 'Disiapkan' && $current !== 'Proses') throw new \DomainException('Pesanan sudah keluar dari tahap persiapan.');
                $next = $shippingStatus === 'Terkirim' ? 'Selesai' : ($shippingStatus === 'Disiapkan' ? 'Proses' : 'Dikirim');
                $courier = 'Jasa Kirim Toko';
                $awb = '';
            } else {
                if ($shipment && $courier !== trim((string)$order['kurir'])) {
                    throw new \DomainException('Kurir mengikuti pilihan checkout. Perubahan kurir memerlukan pemeriksaan terpisah.');
                }
                if ($courier === '' || strlen($courier) > 255 || $awb === '' || strlen($awb) > 120
                    || preg_match('/[<>\x00-\x1F\x7F]/', $courier . $awb)
                    || stripos($awb, 'menunggu') !== false || strtolower($awb) === 'kosong') {
                    throw new \DomainException('Isi nama kurir dan nomor resi yang valid.');
                }
                $next = 'Dikirim';
                $shippingStatus = 'Dalam Pengiriman';
            }
            if ($current === 'Selesai' && $next !== 'Selesai') throw new \DomainException('Pesanan selesai tidak boleh dikembalikan ke pengiriman.');
            if ($internal) { $courier = 'Jasa Kirim Toko'; $awb = ''; }
            $changes = ['status' => $next, 'resi' => $awb, 'kurir' => $courier];
            $changed = $next !== $current || $awb !== (string)$order['resi'] || $courier !== (string)$order['kurir']
                || ($shipment && $shippingStatus !== (string)$shipment['shipping_status']);
            $this->db->table('pemesanan')->where('id_midtrans', $orderId)->update($changes);
            if ($shipment) {
                // Quoted courier/service/cost stay immutable. Only fulfillment fields change.
                $this->db->table('order_shipments')->where('id', $shipment['id'])->update([
                    'tracking_number' => $internal ? null : $awb,
                    'shipping_status' => $shippingStatus,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $shipment = array_merge($shipment, ['tracking_number' => $internal ? null : $awb, 'shipping_status' => $shippingStatus]);
            }
            if (!$this->db->transStatus()) throw new \RuntimeException('Shipping transaction failed');
            $this->db->transCommit();
            return ['order' => array_merge($order, $changes), 'shipment' => $shipment, 'changed' => (bool)$changed];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
