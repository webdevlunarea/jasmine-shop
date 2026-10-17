<?php

use App\Services\Integrations\LunaProductStockSync;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaProductStockSyncTest extends CIUnitTestCase
{
    private function identity(): array
    {
        return ['version'=>1,'product_id'=>'7ef42be0-9851-4d75-ab2b-13e9acd58990','variants'=>[
            'PUTIH'=>'11111111-1111-4111-8111-111111111111',
            'HITAM'=>'22222222-2222-4222-8222-222222222222',
        ]];
    }
    private function master(): array
    {
        return ['id'=>'7ef42be0-9851-4d75-ab2b-13e9acd58990','stock'=>12,'variants'=>[
            ['id'=>'22222222-2222-4222-8222-222222222222','value'=>'BLACK','stock'=>3],
            ['id'=>'11111111-1111-4111-8111-111111111111','value'=>'WHITE','stock'=>9],
        ]];
    }
    public function testStockIsMappedByUuidIntoWebsiteOrderWithoutRenamingLabels(): void
    {
        $result=LunaProductStockSync::project($this->master(),$this->identity(),['id'=>'B20240116151233','nama'=>'Cabinet','varian'=>'["PUTIH","HITAM"]']);
        $this->assertSame(['nama'=>'Cabinet','varian'=>'["PUTIH","HITAM"]','stok'=>'9,3'],$result);
    }
    public function testRejectsChangedOrUnmappedMasterVariants(): void
    {
        foreach ([array_replace($this->master(),['variants'=>array_slice($this->master()['variants'],0,1)]), array_replace($this->master(),['id'=>'33333333-3333-4333-8333-333333333333'])] as $master) {
            try { LunaProductStockSync::project($master,$this->identity(),['nama'=>'Cabinet','varian'=>'["PUTIH","HITAM"]']); $this->fail('Unmapped stock accepted'); }
            catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }
    public function testUnvariedMasterUsesOnlyItsIntegerMasterStock(): void
    {
        $identity=['version'=>1,'product_id'=>'7ef42be0-9851-4d75-ab2b-13e9acd58990','variants'=>[]];
        $result=LunaProductStockSync::project(['id'=>$identity['product_id'],'stock'=>0,'variants'=>[]],$identity,['nama'=>'Table','varian'=>'[]']);
        $this->assertSame(['nama'=>'Table','varian'=>'[]','stok'=>'0'],$result);
    }
}
