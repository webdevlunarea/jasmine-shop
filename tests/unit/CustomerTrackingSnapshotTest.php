<?php

use App\Services\Api\CustomerTrackingSnapshot;
use PHPUnit\Framework\TestCase;

final class CustomerTrackingSnapshotTest extends TestCase
{
    public function testPaymentStateAndPlaceholderResiAreNotReportedAsShipmentTracking(): void
    {
        $result = CustomerTrackingSnapshot::make([
            'id_midtrans' => 'ORDER-123', 'status' => 'Proses',
            'resi' => 'Menunggu pengiriman JNE', 'kurir' => 'kosong',
        ], null);

        $this->assertSame('ORDER-123', $result['order_id']);
        $this->assertNull($result['tracking_number']);
        $this->assertNull($result['courier']);
        $this->assertNull($result['status']);
        $this->assertSame('Proses', $result['order_status']);
    }

    public function testSavedShipmentStateIsIndependentFromOrderState(): void
    {
        $result = CustomerTrackingSnapshot::make([
            'id_midtrans' => 'ORDER-456', 'status' => 'Proses',
            'resi' => 'Menunggu pengiriman', 'kurir' => 'JNE',
        ], [
            'courier_name' => 'JNE', 'tracking_number' => 'AB123456789ID',
            'shipping_status' => 'Dalam Pengiriman', 'updated_at' => '2026-10-18 10:00:00',
        ]);

        $this->assertSame('AB123456789ID', $result['tracking_number']);
        $this->assertSame('JNE', $result['courier']);
        $this->assertSame('Dalam Pengiriman', $result['status']);
        $this->assertSame('Proses', $result['order_status']);
        $this->assertSame('2026-10-18 10:00:00', $result['updated_at']);
    }
}
