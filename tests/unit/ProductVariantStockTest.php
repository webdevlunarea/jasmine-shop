<?php

use App\Services\ProductVariantStock;
use CodeIgniter\Test\CIUnitTestCase;

final class ProductVariantStockTest extends CIUnitTestCase
{
    public function testLegacyAggregateStockIsSharedAcrossVariantsWithoutBeingSummedSeveralTimes(): void
    {
        $variants = '["Hitam","Putih","Cokelat"]';
        $this->assertSame(7, ProductVariantStock::stockForIndex($variants, '7', 0));
        $this->assertSame(7, ProductVariantStock::stockForIndex($variants, '7', 2));
        $this->assertSame(7, ProductVariantStock::totalStock($variants, '7'));
    }

    public function testPerVariantStockVectorRemainsIndependent(): void
    {
        $variants = [['nama' => 'Hitam'], ['name' => 'Putih'], 'Cokelat'];
        $this->assertSame(['Hitam', 'Putih', 'Cokelat'], ProductVariantStock::names($variants));
        $this->assertSame(2, ProductVariantStock::stockForIndex($variants, '2,3,4', 0));
        $this->assertSame(3, ProductVariantStock::stockForIndex($variants, '2,3,4', 1));
        $this->assertSame(9, ProductVariantStock::totalStock($variants, '2,3,4'));
    }

    public function testVariantlessProductUsesTheCheckoutDefaultVariant(): void
    {
        $this->assertSame(['Default'], ProductVariantStock::names('[]'));
        $this->assertSame(5, ProductVariantStock::stockForIndex([], '5', 0));
        $this->assertSame(5, ProductVariantStock::totalStock([], '5'));
    }

    public function testInvalidOrNegativeStockFailsClosed(): void
    {
        $this->assertSame(0, ProductVariantStock::stockForIndex(['A', 'B'], 'invalid', 1));
        $this->assertSame(0, ProductVariantStock::stockForIndex(['A', 'B'], '-3', 0));
        $this->assertSame(0, ProductVariantStock::stockForIndex(['A'], '8', 1));
    }
}
