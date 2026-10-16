<?php

use App\Models\KonstantaModel;
use App\Models\StokModel;
use App\Services\Integrations\LunaCatalogSchema;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaMysqlStagingTest extends CIUnitTestCase
{
    private $auditDb;

    protected function setUp(): void
    {
        parent::setUp();
        $path = getenv('LUNA_IDENTITY_STAGING_CONNECTION');
        if (!$path) $this->markTestSkipped('Opt-in isolated MySQL staging only.');
        $c = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if ($c['host'] !== '127.0.0.1' || $c['website'] !== 'shipping_audit_web' || $c['user'] !== 'shipping_audit'
            || !is_int($c['port']) || $c['port'] < 1024 || $c['port'] === 3306) throw new RuntimeException('Non-isolated database rejected.');
        $this->auditDb = \Config\Database::connect([
            'DSN'=>'', 'hostname'=>$c['host'], 'port'=>$c['port'], 'username'=>$c['user'], 'password'=>$c['password'],
            'database'=>$c['website'], 'DBDriver'=>'MySQLi', 'DBPrefix'=>'', 'pConnect'=>false,
            'DBDebug'=>true, 'charset'=>'utf8mb4', 'DBCollat'=>'utf8mb4_general_ci', 'strictOn'=>true,
            'failover'=>[], 'encrypt'=>false,
        ], false);
        $row = $this->auditDb->query('SELECT DATABASE() AS name, @@port AS port, VERSION() AS version')->getRowArray();
        $this->assertSame('shipping_audit_web', $row['name']);
        $this->assertSame($c['port'], (int)$row['port']);
        $this->assertStringStartsWith('8.', $row['version']);
    }

    protected function tearDown(): void
    {
        if ($this->auditDb) $this->auditDb->close();
        parent::tearDown();
    }

    public function testWebsiteWideningPreservesExistingRowsAndSupportsLargeIdentity(): void
    {
        $db = $this->auditDb;
        $settingsBefore = hash('sha256', json_encode($db->query('SELECT id,label,value FROM konstanta ORDER BY id')->getResultArray(), JSON_THROW_ON_ERROR));
        $stockBefore = hash('sha256', json_encode($db->query('SELECT id,nama,varian,jumlah,stok_akhir FROM stok ORDER BY id')->getResultArray(), JSON_THROW_ON_ERROR));
        $db->query('SET SESSION lock_wait_timeout = 10');
        // Only the validated isolated database connection receives this DDL.
        $db->query('ALTER TABLE konstanta MODIFY COLUMN value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL');
        $db->query('ALTER TABLE stok MODIFY COLUMN nama VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL');
        $this->assertSame($settingsBefore, hash('sha256', json_encode($db->query('SELECT id,label,value FROM konstanta ORDER BY id')->getResultArray(), JSON_THROW_ON_ERROR)));
        $this->assertSame($stockBefore, hash('sha256', json_encode($db->query('SELECT id,nama,varian,jumlah,stok_akhir FROM stok ORDER BY id')->getResultArray(), JSON_THROW_ON_ERROR)));
        $identity = ['version'=>1,'product_id'=>'11111111-1111-4111-8111-111111111111','variants'=>[]];
        for ($i = 1; $i <= 8; $i++) $identity['variants']['VARIAN '.$i] = sprintf('%08x-1111-4111-8111-%012x', $i, $i);
        $this->assertGreaterThan(255, strlen(json_encode($identity)));
        $lock = 'isolated-identity-test';
        $this->assertSame(1, (int)$db->query('SELECT GET_LOCK(?, 2) AS acquired', [$lock])->getRowArray()['acquired']);
        $db->transBegin();
        try {
            $model = new KonstantaModel($db);
            $model->saveLunaProductIdentity('ZSTAGEID00001', $identity);
            $this->assertSame($identity, $model->getLunaProductIdentity('ZSTAGEID00001'));
            $stock = new StokModel($db);
            $this->assertSame(100, $stock->catalogNameCapacity());
            LunaCatalogSchema::stock(['nama'=>str_repeat('A', 100)], $stock->catalogNameCapacity());
        } finally {
            $db->transRollback();
            $db->query('SELECT RELEASE_LOCK(?)', [$lock]);
        }
        $this->assertSame($settingsBefore, hash('sha256', json_encode($db->query('SELECT id,label,value FROM konstanta ORDER BY id')->getResultArray(), JSON_THROW_ON_ERROR)));
    }

    public function testReviewedMappingPersistsWithoutChangingProductsOrdersOrStock(): void
    {
        $db=$this->auditDb;
        $snapshot=static function () use ($db): array {
            $hashes=[];
            foreach (['barang'=>'id,nama,varian,stok,harga', 'stok'=>'id,id_barang,jumlah,stok_akhir', 'pemesanan'=>'id,id_midtrans,status,items', 'konstanta'=>'id,label,value'] as $table=>$columns) {
                $hashes[$table]=hash('sha256',json_encode($db->query('SELECT '.$columns.' FROM '.$table.' ORDER BY id')->getResultArray(),JSON_THROW_ON_ERROR));
            }
            return $hashes;
        };
        $before=$snapshot();
        $lock='lunarea_catalog_identity_'.substr(hash('sha256',$db->getDatabase()),0,24);
        $this->assertSame(1,(int)$db->query('SELECT GET_LOCK(?, 2) AS acquired',[$lock])->getRowArray()['acquired']);
        $db->transBegin();
        try {
            $web=['id'=>'ZSTAGEREVIEW01','nama'=>'Synthetic only','varian'=>'["PUTIH"]','active'=>1];
            $master=['id'=>'33333333-3333-4333-8333-333333333333','active'=>true,'website_ids'=>[],
                'variants'=>[['id'=>'44444444-4444-4444-8444-444444444444','value'=>'WHITE']]];
            $identity=\App\Services\Integrations\LunaMappingReview::identity($web,$master,[$master['variants'][0]['id']],null);
            $identity['review']=['by'=>'staging@example.test','at'=>'2026-10-17T00:00:00Z','note'=>'Synthetic staging review; no master writes.'];
            $model=new KonstantaModel($db); $model->saveLunaProductIdentity($web['id'],$identity);
            $this->assertSame($identity,$model->getLunaProductIdentity($web['id']));
            try { $model->saveLunaProductIdentity('ZSTAGEDOUBLE01',$identity); $this->fail('Duplicate master accepted'); }
            catch (DomainException $e) { $this->assertStringContainsString('sudah terhubung',$e->getMessage()); }
            $during=$snapshot();
            foreach (['barang','stok','pemesanan'] as $table) $this->assertSame($before[$table],$during[$table]);
        } finally { $db->transRollback(); $db->query('SELECT RELEASE_LOCK(?)',[$lock]); }
        $this->assertSame($before,$snapshot());
    }
}
