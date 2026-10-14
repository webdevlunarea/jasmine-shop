<?php

use CodeIgniter\Test\CIUnitTestCase;

final class CommerceResponsiveViewTest extends CIUnitTestCase
{
    /** @dataProvider shipmentCases */
    public function testOrderShippingRendersResponsiveLayoutWithLegacyAndNewOrders(?array $shipment): void
    {
        $renderer = new class {
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }

            public function render(array $data): string
            {
                extract($data, EXTR_SKIP);
                ob_start();
                try {
                    include APPPATH . 'Views/pages/orderShipping.php';
                    return ob_get_contents();
                } finally {
                    ob_end_clean();
                }
            }
        };

        $html = $renderer->render([
            'shipment' => $shipment,
            'kurir' => 'JNE',
            'pemesanan' => [
                'id_midtrans' => 'ORDER-TEST-12345678901234567890',
                'resi' => 'TEST12345678901234567890',
                'alamat_pen' => 'Jalan Contoh <alamat> Kecamatan Ngaliyan Semarang',
            ],
            'dataMid' => ['payment_type' => 'rekening', 'gross_amount' => 12500000],
            'bank' => 'bca',
            'items' => [['name' => str_repeat('Lemari Putih ', 12), 'quantity' => 12, 'value' => 12500000]],
        ]);

        $this->assertStringContainsString('order-page-shell', $html);
        $this->assertStringContainsString('order-steps', $html);
        $this->assertStringContainsString('order-payment-summary', $html);
        $this->assertStringContainsString('&lt;alamat&gt;', $html);
        $this->assertStringNotContainsString('</h5></p>', $html);
    }

    public static function shipmentCases(): array
    {
        return [
            'legacy' => [null],
            'external' => [[
                'shipping_method_code' => 'external', 'courier_name' => 'JNE',
                'service_name' => 'Reguler', 'shipping_cost' => 1250000,
                'tracking_number' => 'TEST123456', 'shipping_status' => 'Dikirim',
            ]],
            'internal' => [[
                'shipping_method_code' => 'internal_delivery', 'shipping_cost' => 0,
                'shipping_status' => 'Disiapkan', 'tracking_number' => '',
            ]],
        ];
    }

    public function testCheckoutRetainsPaymentAndShippingControls(): void
    {
        $source = file_get_contents(APPPATH . 'Views/pages/checkoutcorecc.php');
        foreach (['checkout-page-shell', 'checkout-layout', 'checkout-total-row',
            'checkout-products-table', 'id="form-checkout"', 'action="/actionpaycore"',
            'name="shipping_quote_token"', 'id="checkbox-syarat"', 'id="btn-checkout"',
            'id="modal-metode-bayar"'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringNotContainsString('gap: 10em', $source);
        $this->assertFileExists(FCPATH . 'css/commerce-responsive.css');
    }
}
