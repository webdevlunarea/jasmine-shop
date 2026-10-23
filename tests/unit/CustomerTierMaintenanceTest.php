<?php

use App\Services\Customers\CustomerTierMaintenance;
use CodeIgniter\Test\CIUnitTestCase;
final class CustomerTierMaintenanceTest extends CIUnitTestCase
{
    public function testExpiredEntriesAreRemovedAndTierUsesJakartaDate(): void
    {
        $today = new \DateTimeImmutable('2026-10-23 00:00:00', new \DateTimeZone('Asia/Jakarta'));
        $tier = ['label' => 'gold', 'data' => [
            ['nominal' => 6000000, 'kadaluarsa' => '2026-10-22', 'id_pesanan' => 'old'],
            ['nominal' => 9000000, 'kadaluarsa' => '2026-10-23', 'id_pesanan' => 'today'],
        ]];

        $actual = CustomerTierMaintenance::refreshedTier($tier, $today);

        $this->assertSame('bronze', $actual['label']);
        $this->assertCount(1, $actual['data']);
        $this->assertSame('today', $actual['data'][0]['id_pesanan']);
    }

    public function testTierThresholdsMatchCheckoutRules(): void
    {
        $today = new \DateTimeImmutable('2026-10-23', new \DateTimeZone('Asia/Jakarta'));
        foreach ([
            [10000000, 'silver'],
            [50000000, 'gold'],
            [100000000, 'platinum'],
        ] as [$amount, $expected]) {
            $tier = CustomerTierMaintenance::refreshedTier([
                'label' => 'bronze',
                'data' => [['nominal' => $amount, 'kadaluarsa' => '2027-10-23']],
            ], $today);
            $this->assertSame($expected, $tier['label']);
        }
    }

    public function testMalformedTierIsLeftForManualReview(): void
    {
        $this->assertNull(CustomerTierMaintenance::refreshedTier(['label' => 'gold', 'data' => 'invalid']));
        $this->assertNull(CustomerTierMaintenance::refreshedTier([
            'label' => 'gold',
            'data' => [['nominal' => 1000, 'kadaluarsa' => '2026-02-31']],
        ]));
    }
}
