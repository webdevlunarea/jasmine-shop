<?php

namespace App\Services\Notifications;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/** Queues an FCM notice for customers who saved the exact product/variant. */
final class BackInStockNotificationService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function notify(string $productId, string $productName, string $variant, string $eventId): int
    {
        if (!$this->db->tableExists('customer_push_notifications') || !$this->db->tableExists('pembeli')) return 0;
        $productId = trim($productId);
        $variant = trim($variant);
        if ($productId === '' || $variant === '' || trim($eventId) === '') return 0;

        // LIKE narrows the candidate set; the decoded legacy wishlist is still
        // checked for exact product and variant identity before enqueueing.
        $customers = $this->db->table('pembeli')->select('email_user,wishlist')
            ->where('wishlist IS NOT NULL', null, false)
            ->like('wishlist', $productId)
            ->get()->getResultArray();
        $notifications = new CustomerPushNotifications($this->db);
        $queued = 0;
        foreach ($customers as $customer) {
            $email = strtolower(trim((string)($customer['email_user'] ?? '')));
            $saved = json_decode((string)($customer['wishlist'] ?? '[]'), true);
            if ($email === '' || !is_array($saved) || !$this->containsVariant($saved, $productId, $variant)) continue;

            $label = $productName . ($variant !== 'Default' ? ' · ' . $variant : '');
            $ok = $notifications->enqueue(
                $email,
                'back_in_stock',
                'Produk favorit tersedia kembali',
                mb_substr($label . ' kini memiliki stok. Buka aplikasi untuk melihatnya.', 0, 240),
                ['type' => 'back_in_stock', 'product_id' => $productId, 'variant' => $variant],
                'back-in-stock:' . $productId . ':' . mb_strtolower($variant) . ':' . $eventId
            );
            if (!$ok) throw new \RuntimeException('Back-in-stock notification could not be queued.');
            $queued++;
        }
        return $queued;
    }

    private function containsVariant(array $items, string $productId, string $variant): bool
    {
        foreach ($items as $item) {
            $id = is_array($item) ? (string)($item['id'] ?? $item['product_id'] ?? '') : (is_string($item) ? $item : '');
            if ($id !== $productId) continue;
            $savedVariant = is_array($item) ? trim((string)($item['varian'] ?? $item['variant'] ?? '')) : '';
            // Old product-level favorites have no variant; treat them as a
            // subscription to any option of that product.
            if ($savedVariant === '' || mb_strtolower($savedVariant) === mb_strtolower($variant)) return true;
        }
        return false;
    }
}
