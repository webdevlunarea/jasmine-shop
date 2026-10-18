<?php

namespace App\Services\Notifications;

/** Customer-facing push copy for the shipping lifecycle; unknown states are never guessed. */
final class ShippingStatusNotification
{
    public static function for(string $status, string $orderId): ?array
    {
        $messages = [
            'Disiapkan' => ['Pesanan sedang disiapkan', 'Pesanan %s sedang disiapkan untuk dikirim.'],
            'Dalam Pengiriman' => ['Pesanan sedang dikirim', 'Pesanan %s sedang dalam pengiriman.'],
            'Gagal Kirim' => ['Pengiriman mengalami kendala', 'Pengiriman pesanan %s mengalami kendala. Silakan hubungi layanan pelanggan kami.'],
            'Terkirim' => ['Pesanan telah diterima', 'Pesanan %s telah ditandai diterima.'],
        ];
        if (!isset($messages[$status]) || trim($orderId) === '') return null;
        return [
            'title' => $messages[$status][0],
            'body' => sprintf($messages[$status][1], $orderId),
        ];
    }
}
