<?php

use App\Services\Shipping\ShippingSubsidyPolicy as Policy;
use CodeIgniter\Test\CIUnitTestCase;

final class ShippingSubsidyTest extends CIUnitTestCase
{
    public function testTenPercentAndCapUseIntegerIdr(): void
    {
        $policy = Policy::fromInput(['enabled' => true, 'percentage' => '10', 'max_amount' => 0]);
        $result = Policy::calculate(100000, $policy, 'jne');
        $this->assertSame(10000, $result['subsidy_amount']);
        $this->assertSame(90000, $result['payable_cost']);
        $policy['max_amount'] = 5000;
        $this->assertSame(95000, Policy::calculate(100000, $policy, 'jne')['payable_cost']);
    }

    public function testFractionalPercentAndRounding(): void
    {
        $policy = Policy::fromInput(['enabled' => true, 'percentage' => '10.25']);
        $this->assertSame(1025, $policy['percentage_bps']);
        $this->assertSame(102, Policy::calculate(999, $policy, 'jne')['subsidy_amount']);
    }

    public function testInternalDeliveryAndExcludedCouriersAreNotSubsidized(): void
    {
        $policy = Policy::fromInput(['enabled' => true, 'percentage' => '10', 'couriers' => ['jne']]);
        $this->assertSame(0, Policy::calculate(100000, $policy, 'tiki')['subsidy_amount']);
        $this->assertSame(0, Policy::calculate(100000, $policy, 'jne', true)['subsidy_amount']);
        $this->assertSame(0, Policy::calculate(0, $policy, 'internal', true)['payable_cost']);
    }

    public function testOneHundredPercentNeverBecomesNegative(): void
    {
        $policy = Policy::fromInput(['enabled' => true, 'percentage' => '100']);
        $this->assertSame(0, Policy::calculate(123456, $policy, 'jne')['payable_cost']);
    }

    public function testPeriodUsesWibAndExclusiveEnd(): void
    {
        $policy = Policy::fromInput(['enabled' => true, 'percentage' => '10', 'starts_at' => '2026-10-15T08:00', 'ends_at' => '2026-10-15T09:00']);
        $this->assertSame(10000, Policy::calculate(100000, $policy, 'jne', false, new DateTimeImmutable('2026-10-15T01:00:00Z'))['subsidy_amount']);
        $this->assertSame(0, Policy::calculate(100000, $policy, 'jne', false, new DateTimeImmutable('2026-10-15T02:00:00Z'))['subsidy_amount']);
    }

    /** @dataProvider invalidSettings */
    public function testInvalidSettingsAreRejected(array $settings): void
    {
        $this->expectException(\DomainException::class);
        Policy::fromInput($settings);
    }

    public function invalidSettings(): array
    {
        return [
            [['percentage' => '101']], [['percentage' => '-10']], [['percentage' => '1.234']],
            [['percentage' => '10', 'max_amount' => '-1']], [['percentage' => '10', 'max_amount' => '1.5']],
            [['enabled' => true, 'percentage' => '0']],
            [['percentage' => '10', 'starts_at' => '2026-02-30T10:00']],
            [['percentage' => '10', 'starts_at' => '2026-10-15T10:00', 'ends_at' => '2026-10-14T10:00']],
        ];
    }
}
