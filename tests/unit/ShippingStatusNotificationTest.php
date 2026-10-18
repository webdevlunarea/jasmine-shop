<?php

namespace Tests\Unit;

use App\Services\Notifications\ShippingStatusNotification;
use CodeIgniter\Test\CIUnitTestCase;

final class ShippingStatusNotificationTest extends CIUnitTestCase
{
    public function testKnownShippingStatesHaveAccurateIndonesianCopy(): void
    {
        $this->assertSame('Pesanan sedang disiapkan', ShippingStatusNotification::for('Disiapkan', 'L-1')['title']);
        $this->assertSame('Pesanan sedang dikirim', ShippingStatusNotification::for('Dalam Pengiriman', 'L-1')['title']);
        $this->assertSame('Pengiriman mengalami kendala', ShippingStatusNotification::for('Gagal Kirim', 'L-1')['title']);
        $this->assertSame('Pesanan telah diterima', ShippingStatusNotification::for('Terkirim', 'L-1')['title']);
        $this->assertSame('Pesanan L-1 telah ditandai diterima.', ShippingStatusNotification::for('Terkirim', 'L-1')['body']);
    }

    public function testUnknownOrUnboundStateDoesNotInventNotification(): void
    {
        $this->assertNull(ShippingStatusNotification::for('Status Tidak Dikenal', 'L-1'));
        $this->assertNull(ShippingStatusNotification::for('Dalam Pengiriman', ''));
    }
}
