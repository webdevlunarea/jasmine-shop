<?php

use App\Services\ProductDescriptionSanitizer;
use CodeIgniter\Test\CIUnitTestCase;

final class ProductDescriptionSanitizerTest extends CIUnitTestCase
{
    public function testKeepsBasicFormattingAndRemovesExecutableMarkupAndAttributes(): void
    {
        $html = '<h2 onclick="alert(1)">Detail</h2><p style="color:red">Tahan lama</p><script>alert(2)</script><a href="javascript:alert(3)" onclick="alert(4)">tautan</a><a href="https://example.test/item" target="_blank">produk</a>';
        $this->assertSame('<h2>Detail</h2><p>Tahan lama</p>tautan<a href="https://example.test/item" rel="noopener noreferrer">produk</a>', ProductDescriptionSanitizer::sanitize($html));
    }

    public function testRejectsExcessiveDescriptionPayload(): void
    {
        $this->expectException(DomainException::class);
        ProductDescriptionSanitizer::sanitize(str_repeat('x', 200001));
    }
}
