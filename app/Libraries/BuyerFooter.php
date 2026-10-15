<?php

namespace App\Libraries;

/** Presentation-only footer selection. Never changes routing or payment behavior. */
final class BuyerFooter
{
    public static function resolve(string $path, string $basePath = ''): array
    {
        $path = '/' . trim((string) (parse_url($path, PHP_URL_PATH) ?? ''), '/');
        $basePath = '/' . trim($basePath, '/');
        if ($basePath !== '/' && ($path === $basePath || strpos($path, $basePath . '/') === 0)) {
            $path = substr($path, strlen($basePath));
        }
        $path = preg_replace('~^/?index\.php(?:/|$)~', '/', $path);
        $root = explode('/', trim($path, '/'))[0];
        $payment = in_array($root, ['checkout', 'actionpaycore', 'order', 'payorder', 'invoice'], true);
        $minimal = $payment || in_array($root, ['login', 'signup', 'masuk', 'daftar', 'daftarcoba', 'verify', 'auth'], true);
        $compact = in_array($root, ['cart', 'account', 'transaction', 'wishlist', 'point', 'voucher', 'retur', 'lacak-pengiriman'], true);
        return ['variant' => $minimal ? 'minimal' : ($compact ? 'compact' : 'full'), 'payment' => $payment];
    }
}
