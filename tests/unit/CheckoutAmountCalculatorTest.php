<?php

namespace Tests\Unit;

use App\Services\Payments\CheckoutAmountCalculator;
use PHPUnit\Framework\TestCase;

final class CheckoutAmountCalculatorTest extends TestCase
{
    /** @dataProvider adminFeeCases */
    public function testAdminFeeRetainsExistingCheckoutRules(string $method, int $amount, float $taxPercent, int $expected): void
    {
        self::assertSame($expected, (new CheckoutAmountCalculator())->adminFee($method, $amount, $taxPercent));
    }

    public static function adminFeeCases(): array
    {
        return [
            'bank transfer includes configured tax' => ['bca', 100000, 11, 4440],
            'ewallet rounds up fractional rupiah' => ['gopay', 1001, 0, 21],
            'shopeepay rate' => ['shopeepay', 50000, 0, 1000],
            'qris rate' => ['qris', 100000, 0, 700],
            'dana rate' => ['dana', 100000, 0, 1500],
            'card fixed plus percentage' => ['card', 100000, 0, 4900],
            'manual transfer has no gateway fee' => ['rekening', 100000, 0, 0],
        ];
    }

    public function testMidtransTotalUsesSignedIntegerPricesAndQuantities(): void
    {
        $items = [
            ['price' => 120000, 'quantity' => 2],
            ['price' => -5000, 'quantity' => 1],
            ['price' => 4000.4, 'quantity' => 3],
        ];
        self::assertSame(247000, (new CheckoutAmountCalculator())->totalFromItems($items));
    }
}
