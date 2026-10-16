<?php

use App\Services\Integrations\LunaProductIdentity;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaProductIdentityTest extends CIUnitTestCase
{
    private const PRODUCT = '7ef42be0-9851-4d75-ab2b-13e9acd58990';
    private const VARIANT = '11111111-1111-4111-8111-111111111111';

    public function testIdentityIsAdditiveAndDoesNotChangePaymentSnapshot(): void
    {
        $identity = LunaProductIdentity::fromProduct(['id'=>self::PRODUCT, 'variants'=>[['id'=>self::VARIANT, 'value'=>'CHERRY HITAM']]]);
        $original = ['id'=>'B20240116151233', 'name'=>'Meja Tulis - MT 120 (CHERRY HITAM)', 'value'=>445550, 'quantity'=>7];
        $enriched = LunaProductIdentity::enrich($original, $identity, 'CHERRY HITAM');
        $this->assertSame(self::PRODUCT, $enriched['luna_product_id']);
        $this->assertSame(self::VARIANT, $enriched['luna_variant_id']);
        unset($enriched['luna_product_id'], $enriched['luna_variant_id']);
        $this->assertSame($original, $enriched);
    }

    public function testLegacySnapshotIsNotRewritten(): void
    {
        $item = ['id'=>'B1','value'=>12000,'quantity'=>1];
        $this->assertSame($item, LunaProductIdentity::enrich($item, null, 'Default'));
    }

    public function testUnmappedVariantFailsClosed(): void
    {
        $identity = LunaProductIdentity::fromProduct(['id'=>self::PRODUCT, 'variants'=>[['id'=>self::VARIANT, 'value'=>'HITAM']]]);
        $this->expectException(DomainException::class);
        LunaProductIdentity::enrich(['id'=>'B1'], $identity, 'PUTIH');
    }

    public function testDuplicateVariantNamesRejectRatherThanOverwrite(): void
    {
        $this->expectException(DomainException::class);
        LunaProductIdentity::fromProduct(['id'=>self::PRODUCT, 'variants'=>[
            ['id'=>self::VARIANT,'value'=>'HITAM'], ['id'=>'22222222-2222-4222-8222-222222222222','value'=>'HITAM'],
        ]]);
    }

    public function testNonUuidMasterIsRejected(): void
    {
        $this->expectException(DomainException::class);
        LunaProductIdentity::fromProduct(['id'=>'SKU-WEBSITE']);
    }
}
