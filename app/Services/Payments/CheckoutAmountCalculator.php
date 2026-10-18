<?php

namespace App\Services\Payments;

/** Shared IDR arithmetic for the existing web checkout and future JSON checkout. */
final class CheckoutAmountCalculator
{
    private const BANK_TRANSFER_METHODS = ['bca', 'bri', 'bni', 'mandiri', 'permata', 'cimb'];

    /** Keep the current Lunarea checkout fee rules in one pure, testable function. */
    public function adminFee(string $method, int $amountBeforeFee, float $taxPercent): int
    {
        $method = strtolower(trim($method));
        if (in_array($method, self::BANK_TRANSFER_METHODS, true)) {
            $fee = 4000 + (4000 * $taxPercent / 100);
        } elseif (in_array($method, ['gopay', 'shopeepay'], true)) {
            $fee = $amountBeforeFee * 0.02;
        } elseif ($method === 'qris') {
            $fee = $amountBeforeFee * 0.007;
        } elseif ($method === 'dana') {
            $fee = $amountBeforeFee * 0.015;
        } elseif ($method === 'card') {
            $fee = ($amountBeforeFee * 0.029) + 2000;
        } else {
            $fee = 0;
        }

        return (int)ceil($fee);
    }

    /** Sum canonical Midtrans item_details in integer rupiah. */
    public function totalFromItems(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $price = (int)round((float)($item['price'] ?? 0));
            $quantity = max(1, (int)($item['quantity'] ?? 1));
            $total += $price * $quantity;
        }
        return $total;
    }
}
