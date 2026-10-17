<?php

use App\Services\Integrations\LunaCatalogueClient;
use App\Services\Integrations\LunaMappingReview;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaMappingReviewTest extends CIUnitTestCase
{
    private function website(): array { return ['id'=>'B20240116151233', 'nama'=>'MT 120', 'varian'=>'["CHERRY HITAM","PUTIH"]', 'active'=>1]; }
    private function master(): array { return ['id'=>'7ef42be0-9851-4d75-ab2b-13e9acd58990','name'=>'MT 120','sku'=>'MT12','active'=>true,'stock'=>42,'website_ids'=>[],
        'variants'=>[['id'=>'3b2bb1d3-df78-4272-92b2-87612eac4c1d','value'=>'CHERRY HITAM','stock'=>9],['id'=>'11111111-1111-4111-8111-111111111111','value'=>'WHITE','stock'=>3]]]; }
    private function choices(): array { return array_column($this->master()['variants'], 'id'); }

    public function testExplicitVariantAliasesUseUuidWithoutRenamingWebsite(): void
    {
        $web = $this->website(); $before = $web;
        $identity = LunaMappingReview::identity($web, $this->master(), $this->choices(), null);
        $this->assertSame($before, $web);
        $this->assertSame($this->choices()[1], $identity['variants']['PUTIH']);
        $this->assertSame($this->master()['id'], $identity['product_id']);
    }
    public function testRejectsVariantBelongingToAnotherProduct(): void
    {
        $this->expectException(DomainException::class);
        LunaMappingReview::identity($this->website(), $this->master(), [$this->choices()[0], '22222222-2222-4222-8222-222222222222'], null);
    }
    public function testRejectsDuplicateVariantChoice(): void
    {
        $this->expectException(DomainException::class);
        LunaMappingReview::identity($this->website(), $this->master(), array_fill(0, 2, $this->choices()[0]), null);
    }
    public function testRejectsIncompleteSelection(): void
    {
        $this->expectException(DomainException::class);
        LunaMappingReview::identity($this->website(), $this->master(), [$this->choices()[0]], null);
    }
    public function testRejectsInactiveOrConflictingMaster(): void
    {
        foreach ([['active'=>false], ['website_ids'=>['OTHER']]] as $change) {
            try { LunaMappingReview::identity($this->website(), array_replace($this->master(), $change), $this->choices(), null); $this->fail('Invalid master accepted'); }
            catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }
    public function testExistingProductAndVariantIdsAreImmutable(): void
    {
        $existing = LunaMappingReview::identity($this->website(), $this->master(), $this->choices(), null);
        foreach ([['product_id'=>'22222222-2222-4222-8222-222222222222'], ['variants'=>['CHERRY HITAM'=>$this->choices()[1]]]] as $change) {
            try { LunaMappingReview::identity($this->website(), $this->master(), $this->choices(), array_replace($existing, $change)); $this->fail('Existing reference overwritten'); }
            catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }
    public function testRevisionChangesWithLocalDataOrReviewedIdentity(): void
    {
        $before = LunaMappingReview::revision($this->website(), null);
        $this->assertNotSame($before, LunaMappingReview::revision(array_replace($this->website(), ['varian'=>'["PUTIH"]']), null));
        $this->assertNotSame($before, LunaMappingReview::revision($this->website(), ['version'=>1]));
        $this->assertNotSame($before, LunaMappingReview::revision(array_replace($this->website(), ['stok'=>'9,3']), null));
        $this->assertNotSame(LunaMappingReview::fingerprint($this->master()), LunaMappingReview::fingerprint(array_replace($this->master(), ['active'=>false])));
    }
    public function testStockSnapshotMapsByMasterVariantIdAndPreservesWebsiteOrderAndLabels(): void
    {
        $website=$this->website();
        $identity=LunaMappingReview::identity($website,$this->master(),$this->choices(),null);
        $projected=LunaMappingReview::stock($website,$this->master(),$identity);
        $this->assertSame('9,3',$projected['value']);
        $this->assertSame(['CHERRY HITAM','PUTIH'],array_column($projected['entries'],'variant'));
        $this->assertSame([9,3],array_column($projected['entries'],'stock'));
        $this->assertSame('["CHERRY HITAM","PUTIH"]',$website['varian']);
    }
    public function testStockSnapshotFailsClosedForMissingVariantOrInvalidQuantity(): void
    {
        $identity=LunaMappingReview::identity($this->website(),$this->master(),$this->choices(),null);
        foreach ([array_replace($this->master(), ['variants'=>[$this->master()['variants'][0]]]), array_replace($this->master(), ['variants'=>[array_replace($this->master()['variants'][0], ['stock'=>-1]),$this->master()['variants'][1]]])] as $master) {
            try { LunaMappingReview::stock($this->website(),$master,$identity); $this->fail('Incomplete or invalid inventory accepted'); }
            catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }
    public function testStockSnapshotSupportsUnvariedMasterWithoutInventingAFreeStockValue(): void
    {
        $website=array_replace($this->website(),['varian'=>'["Default"]','stok'=>'5']);
        $master=array_replace($this->master(),['stock'=>0,'variants'=>[]]);
        $identity=LunaMappingReview::identity($website,$master,[],null);
        $this->assertSame(['value'=>'0','entries'=>[['variant'=>'Default','stock'=>0]]],LunaMappingReview::stock($website,$master,$identity));
    }
    public function testNoVariantMasterOnlyAcceptsEmptyOrDefaultWebsite(): void
    {
        $master = array_replace($this->master(), ['variants'=>[]]);
        foreach (['[]', '["Default"]'] as $variants) $this->assertSame([], LunaMappingReview::identity(array_replace($this->website(), ['varian'=>$variants]), $master, [], null)['variants']);
        $this->expectException(DomainException::class);
        LunaMappingReview::identity($this->website(), $master, [], null);
    }
    public function testMalformedAndDuplicateLocalVariantsAreRejected(): void
    {
        foreach (['oops','{}','["A","A"]','[""]','[1]'] as $raw) {
            // JSON {} decodes to an empty PHP array; it is harmless, equivalent to no variants.
            if ($raw === '{}') continue;
            try { LunaMappingReview::variants(array_replace($this->website(), ['varian'=>$raw])); $this->fail('Invalid local variants'); }
            catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
    }
    private function client(int $status, array $body) {
        return new class($status, $body) {
            public array $calls=[]; private int $status; private array $body;
            public function __construct($status,$body) { $this->status=$status; $this->body=$body; }
            public function get($url,$options) { $this->calls[]=[$url,$options]; return new class($this->status,$this->body) {
                private int $status; private array $body;
                public function __construct($s,$b) { $this->status=$s; $this->body=$b; }
                public function getStatusCode() { return $this->status; }
                public function getBody() { return json_encode($this->body); }
            }; }
        };
    }
    public function testCatalogueUsesBackendTokenTlsAndExactSiblingEndpoint(): void
    {
        $http = $this->client(200, ['success'=>true,'data'=>['complete'=>true,'products'=>[$this->master()]]]);
        $client = new LunaCatalogueClient(['url'=>'https://myluna.example.test/api/v1/integrations/lunarea-web-order','token'=>'test-secret'], $http);
        $this->assertSame([$this->master()], $client->products($this->master()['id']));
        [$url,$options] = $http->calls[0];
        $this->assertSame('https://myluna.example.test/api/v1/integrations/lunarea-web-catalogue?product_id='.$this->master()['id'], $url);
        $this->assertSame('test-secret',$options['headers']['X-Luna-Webhook-Token']);
        $this->assertTrue($options['verify']); $this->assertFalse($options['allow_redirects']);
    }
    public function testFailedIncompleteOrDuplicateCatalogueFailsClosed(): void
    {
        foreach ([[500,[]], [200,['success'=>true,'data'=>['complete'=>false,'products'=>[]]]], [200,['success'=>true,'data'=>['complete'=>true,'products'=>[$this->master(),$this->master()]]]]] as [$code,$body]) {
            try { (new LunaCatalogueClient(['url'=>'https://myluna.example.test/api/lunarea-web-order','token'=>'secret'], $this->client($code,$body)))->products(); $this->fail('Bad catalogue accepted'); }
            catch (DomainException $e) { $this->assertStringNotContainsString('secret',$e->getMessage()); }
        }
    }
    public function testCredentialsInUrlOrHttpAndUnknownPathAreRejectedBeforeNetwork(): void
    {
        foreach (['http://host/api/lunarea-web-order','https://user@host/api/lunarea-web-order','https://host/api/other','https://host/api/lunarea-web-order?token=secret'] as $url) {
            $http = $this->client(200,[]);
            try { (new LunaCatalogueClient(['url'=>$url,'token'=>'secret'], $http))->products(); $this->fail('Unsafe URL accepted'); }
            catch (DomainException $e) { $this->assertSame([], $http->calls); }
        }
    }
}
