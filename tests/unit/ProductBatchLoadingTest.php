<?php

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Models\KonstantaModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ProductBatchLoadingTest extends CIUnitTestCase
{
    public function testBatchLoadersFetchOnlyRequestedActiveProductsMetadataAndImageColumns(): void
    {
        $db = Database::connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
        ], false);

        try {
            $db->query('CREATE TABLE barang (id TEXT PRIMARY KEY, nama TEXT, active TEXT, varian TEXT)');
            $db->query('CREATE TABLE konstanta (id INTEGER PRIMARY KEY AUTOINCREMENT, label TEXT, value TEXT)');
            $db->query('CREATE TABLE gambar_barang (id TEXT PRIMARY KEY, gambar1 BLOB, gambar2 BLOB, gambar3 BLOB)');
            $db->table('barang')->insertBatch([
                ['id' => 'A1', 'nama' => 'Meja', 'active' => '1', 'varian' => '["Natural"]'],
                ['id' => 'A2', 'nama' => 'Kursi', 'active' => '1', 'varian' => '["Black"]'],
                ['id' => 'OFF', 'nama' => 'Nonaktif', 'active' => '0', 'varian' => '[]'],
            ]);
            $db->table('konstanta')->insertBatch([
                ['label' => 'product_variant_image_map_A1', 'value' => '{"Natural":1}'],
                ['label' => 'product_variant_image_map_A2', 'value' => '{"Black":2}'],
                ['label' => 'luna_product_identity_A1', 'value' => '{"product_id":"remote-A1"}'],
                ['label' => 'luna_product_identity_A2', 'value' => '{"product_id":"remote-A2"}'],
            ]);
            $db->table('gambar_barang')->insertBatch([
                ['id' => 'A1', 'gambar1' => 'a1-first', 'gambar2' => 'a1-second', 'gambar3' => 'unused-a1'],
                ['id' => 'A2', 'gambar1' => 'unused-a2', 'gambar2' => 'unused-a2-2', 'gambar3' => 'a2-third'],
            ]);

            $products = (new BarangModel($db))->getBarangByIds(['A1', 'A2', 'OFF', 'MISSING'], ['id', 'nama', 'varian']);
            self::assertCount(2, $products);
            self::assertArrayHasKey('A1', $products);
            self::assertArrayHasKey('A2', $products);
            self::assertArrayNotHasKey('OFF', $products);

            $constants = new KonstantaModel($db);
            $maps = $constants->getProductVariantImageMaps(['A1', 'A2', 'MISSING']);
            self::assertSame(['Natural' => 1], $maps['A1']);
            self::assertSame(['Black' => 2], $maps['A2']);
            self::assertSame(['product_id' => 'remote-A1'], $constants->getLunaProductIdentities(['A1', 'MISSING'])['A1']);
            self::assertNull($constants->getLunaProductIdentities(['MISSING'])['MISSING']);

            $images = (new GambarBarangModel($db))->getSelectedImagesByProduct([
                'A1' => ['gambar2'],
                'A2' => ['gambar3'],
            ]);
            self::assertSame('a1-second', $images['A1']['gambar2']);
            self::assertSame('a2-third', $images['A2']['gambar3']);
            self::assertArrayNotHasKey('gambar1', $images['A1']);
            self::assertArrayNotHasKey('gambar2', $images['A2']);
        } finally {
            $db->close();
        }
    }
}
