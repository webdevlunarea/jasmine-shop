<?php

use App\Services\Integrations\LunaCatalogSchema;
use App\Services\Integrations\LunaProductIdentity;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaCatalogSchemaTest extends CIUnitTestCase
{
    public function testVerifiedMt120MetadataFitsExistingColumn(): void
    {
        $identity = LunaProductIdentity::fromProduct([
            'id'=>'7ef42be0-9851-4d75-ab2b-13e9acd58990',
            'variants'=>[['id'=>'3b2bb1d3-df78-4272-92b2-87612eac4c1d','value'=>'CHERRY HITAM']],
        ]);
        $json = LunaCatalogSchema::metadata('B20240116151233', $identity);
        $this->assertLessThanOrEqual(255, strlen($json));
        $this->assertSame($identity, json_decode($json, true));
    }

    public function testLargeMetadataRejectsInsteadOfBeingTruncated(): void
    {
        $this->expectException(DomainException::class);
        LunaCatalogSchema::metadata('B1', ['variants'=>['PUTIH'=>str_repeat('x', 256)]]);
    }

    public function testLongMasterUuidIsNotUsedAsWebsitePrimaryKey(): void
    {
        $this->expectException(DomainException::class);
        LunaCatalogSchema::product('7ef42be0-9851-4d75-ab2b-13e9acd58990', []);
    }

    public function testVerifiedLargerCapacityPermitsMetadataWithoutTruncation(): void
    {
        $identity = ['variants'=>['PUTIH'=>str_repeat('x', 400)]];
        $json = LunaCatalogSchema::metadata('B1', $identity, 65535);
        $this->assertSame($identity, json_decode($json, true));
        $this->assertGreaterThan(255, strlen($json));
    }

    public function testLegacyStockListLimitIsEnforced(): void
    {
        $this->expectException(DomainException::class);
        LunaCatalogSchema::product('B1', ['stok'=>str_repeat('1', 41)]);
    }

    public function testVariantNameLimitIsEnforcedForStockHistory(): void
    {
        $this->expectException(DomainException::class);
        LunaCatalogSchema::stock(['varian'=>str_repeat('A', 21)]);
    }

    public function testValidUtf8NamesAreMeasuredInCharacters(): void
    {
        LunaCatalogSchema::stock(['nama'=>str_repeat('é', 50), 'varian'=>str_repeat('é', 20)]);
        $this->addToAssertionCount(1);
    }

    public function testStockHistoryDisplayNameLimitIsEnforced(): void
    {
        $this->expectException(DomainException::class);
        LunaCatalogSchema::stock(['nama'=>str_repeat('A', 51)]);
    }
}
