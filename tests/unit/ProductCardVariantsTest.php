<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ProductCardVariantsTest extends CIUnitTestCase
{
    /** @dataProvider variantCases */
    public function testCompactVariantPreviewKeepsActualChoices($choices, string $preview, string $count): void
    {
        $html = view('partials/product_card_variants', ['cardVariants' => $choices], ['saveData' => false]);
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xp = new DOMXPath($doc);
        $this->assertSame($preview, $xp->query('//span[@class="product-card-variant-preview"]')->item(0)->textContent);
        $nodes = $xp->query('//span[@class="product-card-variant-count"]');
        $this->assertSame($count === '' ? 0 : 1, $nodes->length);
        if ($count !== '') $this->assertSame($count, $nodes->item(0)->textContent);
        if ($preview === '') $this->assertSame(1, $xp->query('//div[@aria-hidden="true"]')->length);
        $this->assertSame(0, $xp->query('//button|//a')->length);
    }

    public static function variantCases(): array
    {
        return [
            [[], '', ''], [null, '', ''], ['invalid', '', ''],
            [['Putih'], 'Putih', ''], [['Putih', 'Cokelat'], 'Putih · Cokelat', ''],
            [['Putih', 'Cokelat', 'Abu', 'Natural'], 'Putih · Cokelat', '+2 varian'],
            [[' Putih ', 'Putih', '', null, [], 'Natural'], 'Putih · Natural', ''],
        ];
    }

    public function testFullVariantNamesAreEscapedAndAccessible(): void
    {
        $html = view('partials/product_card_variants', ['cardVariants' => ['Putih', 'Natural', 'Abu <script>']], ['saveData' => false]);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('Varian tersedia:', html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
