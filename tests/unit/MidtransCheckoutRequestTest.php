<?php

namespace Tests\Unit;

use App\Services\Payments\MidtransCheckoutRequest;
use DomainException;
use PHPUnit\Framework\TestCase;

final class MidtransCheckoutRequestTest extends TestCase
{
    /** @dataProvider supportedMethods */
    public function testSupportedNativeMethodsBuildExpectedGatewayPayload(string $method, string $type): void
    {
        $payload = (new MidtransCheckoutRequest())->build($this->checkout($method), 'L00000123', 'buyer@example.test', 'Buyer', '+628123456789', 'card-token');
        self::assertSame('L00000123', $payload['transaction_details']['order_id']);
        self::assertSame(10000, $payload['transaction_details']['gross_amount']);
        self::assertSame($type, $payload['payment_type']);
        self::assertSame(10000, $payload['item_details'][0]['price']);
    }

    public static function supportedMethods(): array
    {
        return [
            'bank transfer' => ['bca', 'bank_transfer'],
            'permata' => ['permata', 'permata'],
            'mandiri' => ['mandiri', 'echannel'],
            'QRIS' => ['qris', 'qris'],
            'GoPay' => ['gopay', 'gopay'],
            'ShopeePay' => ['shopeepay', 'shopeepay'],
            'credit card token' => ['card', 'credit_card'],
        ];
    }

    public function testRejectsUnverifiedMethodsAndUnbalancedItemTotal(): void
    {
        $builder = new MidtransCheckoutRequest();
        try {
            $builder->build($this->checkout('rekening'), 'L00000123', 'buyer@example.test', 'Buyer', '+628123456789');
            self::fail('Legacy placeholder payment must not be used by Android.');
        } catch (DomainException $e) {
            self::assertSame('Metode pembayaran ini belum tersedia di aplikasi.', $e->getMessage());
        }

        $checkout = $this->checkout('bca');
        $checkout['total'] = 11000;
        $this->expectException(DomainException::class);
        $builder->build($checkout, 'L00000123', 'buyer@example.test', 'Buyer', '+628123456789');
    }

    public function testCardRequiresProviderToken(): void
    {
        $this->expectException(DomainException::class);
        (new MidtransCheckoutRequest())->build($this->checkout('card'), 'L00000123', 'buyer@example.test', 'Buyer', '+628123456789');
    }

    public function testRejectsMalformedOrFractionalGatewayLineItems(): void
    {
        $checkout = $this->checkout('bca');
        $checkout['midtrans_item_details'][0]['quantity'] = 0;
        try {
            (new MidtransCheckoutRequest())->build($checkout, 'L00000123', 'buyer@example.test', 'Buyer', '+628123456789');
            self::fail('Zero quantity must not be silently normalized into a billable item.');
        } catch (DomainException $e) {
            self::assertSame('Rincian pembayaran tidak valid.', $e->getMessage());
        }

        $checkout = $this->checkout('bca');
        $checkout['midtrans_item_details'][0]['price'] = 10000.5;
        $this->expectException(DomainException::class);
        (new MidtransCheckoutRequest())->build($checkout, 'L00000123', 'buyer@example.test', 'Buyer', '+628123456789');
    }

    private function checkout(string $method): array
    {
        return [
            'payment_method' => $method,
            'total' => 10000,
            'midtrans_item_details' => [['id' => 'P1', 'name' => 'Meja', 'price' => 10000, 'quantity' => 1]],
        ];
    }
}
