<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ShippingSubsidyViewTest extends CIUnitTestCase
{
    public function testPartialRendersWithoutParentLocalCouriersVariable(): void
    {
        $html = view('pages/partials/shippingSubsidySettings', [
            'rajaSettings' => ['couriers' => ['jne', 'tiki']],
            'subsidyStorageReady' => true,
        ], ['saveData' => false]);
        $this->assertStringContainsString('Subsidi Ongkir', $html);
        $this->assertStringContainsString('value="jne"', $html);
        $this->assertStringContainsString('value="tiki"', $html);
        $this->assertStringContainsString('action="/shippingadmin/subsidy"', $html);
    }

    public function testPartialHasDefaultsWhenCouriersAreNotConfigured(): void
    {
        $html = view('pages/partials/shippingSubsidySettings', [
            'rajaSettings' => [], 'subsidyStorageReady' => false,
        ], ['saveData' => false]);
        $this->assertStringContainsString('value="jne"', $html);
        $this->assertStringContainsString('value="sentral"', $html);
    }

    public function testPartialAcceptsLegacyDelimitedCourierSettings(): void
    {
        $html = view('pages/partials/shippingSubsidySettings', [
            'rajaSettings' => ['couriers' => 'jne:tiki,pos'],
            'subsidyStorageReady' => true,
        ], ['saveData' => false]);
        $this->assertStringContainsString('value="tiki"', $html);
        $this->assertStringContainsString('value="pos"', $html);
    }
}
