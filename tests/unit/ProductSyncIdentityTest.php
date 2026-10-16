<?php

use App\Controllers\ProductSyncController;
use App\Services\Integrations\LunaProductIdentity;
use CodeIgniter\Test\CIUnitTestCase;

final class ProductSyncIdentityTest extends CIUnitTestCase
{
    private const UUID = '7ef42be0-9851-4d75-ab2b-13e9acd58990';

    private function resolve(array $product, array $products, array $identities = [])
    {
        $reflection = new ReflectionClass(ProductSyncController::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $barang = new class($products) {
            private array $rows; private $id;
            public function __construct(array $rows) {$this->rows = $rows;}
            public function where($key, $value) {$this->id = $value; return $this;}
            public function first() {return $this->rows[$this->id] ?? null;}
        };
        $konstanta = new class($identities) {
            private array $rows;
            public function __construct(array $rows) {$this->rows = $rows;}
            public function like(...$args) {return $this;}
            public function findAll() {return $this->rows;}
        };
        foreach (['barangModel'=>$barang, 'konstantaModel'=>$konstanta] as $name=>$model) {
            $property = $reflection->getProperty($name); $property->setAccessible(true); $property->setValue($controller, $model);
        }
        $method = $reflection->getMethod('findWebsiteProduct'); $method->setAccessible(true);
        return $method->invoke($controller, $product);
    }

    public function testSimilarProductNameDoesNotBindOrOverwriteCatalog(): void
    {
        $this->assertNull($this->resolve(['id'=>self::UUID,'name'=>'MT 120'], ['B1'=>['id'=>'B1','nama'=>'MT 120']]));
    }

    public function testExplicitWebsiteIdRetainsOriginalPrimaryKey(): void
    {
        $result = $this->resolve(['id'=>self::UUID,'website_id'=>'B1'], ['B1'=>['id'=>'B1']]);
        $this->assertSame('B1', $result['id']);
        $this->assertSame('explicit_id', $result['_luna_match_by']);
    }

    public function testPreviouslyVerifiedIdentityWorksAfterSkuRename(): void
    {
        $rows = [['label'=>LunaProductIdentity::PREFIX.'B1','value'=>json_encode(['product_id'=>self::UUID])]];
        $result = $this->resolve(['id'=>self::UUID,'sku'=>'NEW-SKU'], ['B1'=>['id'=>'B1']], $rows);
        $this->assertSame('B1', $result['id']);
        $this->assertSame('canonical_id', $result['_luna_match_by']);
    }

    public function testConflictingWebsiteAndSkuIdsRejectInsteadOfGuessing(): void
    {
        $this->expectException(DomainException::class);
        $this->resolve(['id'=>self::UUID,'website_id'=>'B1','sku'=>'B2'], ['B1'=>['id'=>'B1'],'B2'=>['id'=>'B2']]);
    }
}
