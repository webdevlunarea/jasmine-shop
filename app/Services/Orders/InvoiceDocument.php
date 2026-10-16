<?php

namespace App\Services\Orders;

/** Read-only presentation of stored order/payment snapshots, not live product prices. */
final class InvoiceDocument
{
    public static function canRead(?array $order, $email, $role, bool $loggedIn): bool
    {
        return $loggedIn && $order !== null && !empty($order['id_midtrans']) && ((string)$role === '1'
            || ((string)$email !== '' && (string)$email === (string)($order['email_cus'] ?? '')));
    }

    private static function decode($value): array
    {
        if (is_array($value)) return $value;
        $result = json_decode((string)$value, true);
        return is_array($result) ? $result : [];
    }

    public static function build(array $order, ?array $shipment = null): array
    {
        $payment = self::decode($order['data_mid'] ?? '');
        $items = [];
        $subtotal = 0;
        foreach (self::decode($order['items'] ?? '') as $item) {
            if (!is_array($item)) continue;
            $price = (int)round((float)($item['value'] ?? $item['price'] ?? 0));
            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $amount = $price * $quantity;
            $subtotal += $amount;
            $items[] = ['name'=>(string)($item['name'] ?? 'Produk'), 'quantity'=>$quantity, 'price'=>$price, 'amount'=>$amount];
        }
        $rows = [['label'=>'Subtotal produk', 'amount'=>$subtotal]];
        $known = $subtotal;
        foreach (['diskonVoucher'=>'Potongan voucher', 'pakai_poin'=>'Poin digunakan'] as $field=>$label) {
            $discount = max(0, (int)round((float)($order[$field] ?? 0)));
            if ($discount > 0) {
                $rows[] = ['label'=>$label, 'amount'=>-$discount];
                $known -= $discount;
            }
        }
        if ($shipment !== null && isset($shipment['shipping_cost'])) {
            $shipping = max(0, (int)$shipment['shipping_cost']);
            $rows[] = ['label'=>'Biaya pengiriman', 'amount'=>$shipping];
            $known += $shipping;
        }
        // Legacy orders lack an itemized fee snapshot: never invent a fixed admin fee.
        $hasTotal = isset($payment['gross_amount']) && is_numeric($payment['gross_amount']);
        $total = $hasTotal ? (int)round((float)$payment['gross_amount']) : null;
        $reconciliation = $hasTotal ? $total - $known : 0;
        if ($reconciliation !== 0) $rows[] = ['label'=>$reconciliation > 0 ? 'Biaya & penyesuaian lainnya' : 'Potongan & penyesuaian lainnya', 'amount'=>$reconciliation];
        $rawDate = (string)($payment['transaction_time'] ?? '');
        $timestamp = $rawDate !== '' ? strtotime($rawDate) : false;
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $date = $timestamp === false ? 'Tanggal belum tercatat' : date('d', $timestamp) . ' ' . $months[(int)date('n', $timestamp)-1] . date(' Y, H:i', $timestamp) . ' WIB';
        $orderId = (string)($order['id_midtrans'] ?? '');
        $number = ($timestamp === false ? '' : date('dmY', $timestamp) . '/CBM/') . explode('-', $orderId)[0];
        $status = (string)($order['status'] ?? '');
        $paid = in_array($status, ['Proses','Dikirim','Selesai'], true);
        $pending = in_array($status, ['Menunggu Pembayaran','Menunggu Pembayaran Rekening'], true);
        $paymentType = (string)($payment['payment_type'] ?? '');
        $method = ['rekening'=>'Transfer bank', 'qris'=>'QRIS', 'gopay'=>'GoPay', 'credit_card'=>'Kartu kredit', 'echannel'=>'Mandiri Bill', 'bank_transfer'=>'Transfer bank'][$paymentType]
            ?? ($paymentType === '' ? 'Belum tercatat' : ucfirst(str_replace('_', ' ', $paymentType)));
        $bank = $payment['va_numbers'][0]['bank'] ?? $payment['bank'] ?? '';
        if ($bank !== '') $method .= ' · ' . strtoupper((string)$bank);
        $courier = ($shipment['shipping_method_code'] ?? '') === 'internal_delivery' ? 'Jasa Kirim Toko'
            : trim(implode(' · ', array_filter([(string)($shipment['courier_name'] ?? ''), (string)($shipment['service_name'] ?? '')])));
        if ($courier === '') $courier = preg_replace('/\s*-\s*Rp\s*[\d.,]+.*$/i', '', (string)($order['kurir'] ?? ''));
        if ($courier === '' || $courier === 'kosong') $courier = 'Belum ditentukan';
        $tracking = trim((string)($shipment['tracking_number'] ?? $order['resi'] ?? ''));
        if (!\App\Services\Integrations\LunaOrderSync::validTracking($tracking)) $tracking = 'Belum tersedia';
        return [
            'orderId'=>$orderId, 'number'=>$number, 'date'=>$date,
            'recipient'=>(string)($order['nama_pen'] ?? ''), 'address'=>(string)($order['alamat_pen'] ?? ''),
            'items'=>$items, 'rows'=>$rows, 'total'=>$total, 'cashback'=>max(0, (int)($order['cashback'] ?? 0)),
            'status'=>$status, 'badge'=>$paid ? 'Lunas' : ($pending ? 'Belum dibayar' : ($status ?: 'Status belum tersedia')),
            'paid'=>$paid, 'method'=>$method, 'courier'=>$courier, 'tracking'=>$tracking,
            'hasAdjustment'=>$reconciliation !== 0,
            'filename'=>'Invoice-Lunarea-' . (preg_replace('/[^A-Za-z0-9_-]/', '', $orderId) ?: 'Pesanan') . '.pdf',
        ];
    }
}
