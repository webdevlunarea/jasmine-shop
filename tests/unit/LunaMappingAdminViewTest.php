<?php

use CodeIgniter\Test\CIUnitTestCase;

final class LunaMappingAdminViewTest extends CIUnitTestCase
{
    private function render(array $data): string
    {
        $renderer = new class {
            public function extend($name): string { return ''; }
            public function section($name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render($data): string { extract($data, EXTR_SKIP); ob_start(); try { include APPPATH.'Views/pages/lunaMappingAdmin.php'; return ob_get_contents(); } finally { ob_end_clean(); } }
        };
        return $renderer->render($data);
    }
    private function fixture(): array
    {
        $row = ['id'=>'B20240116151233', 'nama'=>'MT 120 <produk>', 'active'=>1, 'identity'=>null, 'variants'=>['CHERRY HITAM', 'PUTIH'], 'revision'=>str_repeat('a',64), 'mapping_error'=>null];
        return ['title'=>'Pemetaan MyLuna', 'products'=>[$row], 'selected'=>$row, 'total'=>113, 'mapped'=>1, 'q'=>'', 'pending'=>false, 'page'=>1, 'pages'=>6, 'msg'=>null, 'identityEnabled'=>false];
    }
    public function testPageRequiresExplicitReviewAndStartsDisabled(): void
    {
        $html = $this->render($this->fixture());
        $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="UTF-8" ?>'.$html); $xp = new DOMXPath($dom);
        $this->assertSame(1,$xp->query('//form[@action="/lunamappingadmin/save" and @method="post"]')->length);
        $this->assertSame(1,$xp->query('//input[@name="'.csrf_token().'"]')->length);
        $this->assertSame(1,$xp->query('//input[@name="confirmed" and @required]')->length);
        $this->assertSame(1,$xp->query('//textarea[@name="review_note" and @required and @minlength="10"]')->length);
        $this->assertSame(1,$xp->query('//fieldset[@disabled]')->length);
        $this->assertSame(1,$xp->query('//button[@id="save-mapping" and @disabled]')->length);
        $this->assertStringContainsString('MT 120 &lt;produk&gt;', $html);
        $this->assertStringContainsString('checkout masih nonaktif', $html);
        $this->assertStringNotContainsString('X-Luna-Webhook-Token', $html);
        $this->assertStringNotContainsString('<produk>', $html);
    }
    public function testEmptyAndInvalidStatesDoNotOfferSaveForm(): void
    {
        foreach ([null, array_replace($this->fixture()['selected'], ['mapping_error'=>'Perlu diperiksa <error>'])] as $selected) {
            $html=$this->render(array_replace($this->fixture(), ['selected'=>$selected]));
            $this->assertStringNotContainsString('id="mapping-form"',$html);
            $this->assertStringNotContainsString('<error>',$html);
        }
    }
    public function testControllerContainsIndependentAuthorizationCsrfAndSerializedSave(): void
    {
        $code=file_get_contents(APPPATH.'Controllers/LunaMappingAdmin.php');
        foreach (['security()->verify($this->request)', 'GET_LOCK', 'FOR UPDATE', 'LunaMappingReview::revision', 'LunaMappingReview::fingerprint', 'transRollback', 'RELEASE_LOCK', 'saveLunaProductIdentity'] as $guard) $this->assertStringContainsString($guard,$code);
        foreach (['PemesananModel','LUNA_PRODUCT_IDENTITY_ENABLED\', true', 'ALTER TABLE', 'INSERT INTO products'] as $forbidden) $this->assertStringNotContainsString($forbidden,$code);
        $this->assertStringContainsString('LunaMappingReview::stock', $code);
        $this->assertStringContainsString('Sinkron stok awal dari MyLuna', $code);
        $routes=file_get_contents(APPPATH.'Config/Routes.php');
        $this->assertStringContainsString("'/lunamappingadmin/save', 'LunaMappingAdmin::save', ['filter' => 'harusAdmin']",$routes);
    }
    public function testExportSyntheticPreviewWhenExplicitlyRequested(): void
    {
        if (getenv('LUNA_MAPPING_UI_PREVIEW') !== '1') { $this->assertTrue(true); return; }
        $dir=ROOTPATH.'REFERENSI/shipping-staging/mapping-ui-preview';
        if (!is_dir($dir)) mkdir($dir,0700,true);
        $css='';
        foreach (['style.css','admin-workspace.css','admin-modules.css','luna-mapping-admin.css'] as $file) $css.=file_get_contents(FCPATH.'css/'.$file)."\n";
        $css.=':root{--theme-primary:#2F2D20;--theme-primary-hover:#242218;--theme-sidebar:#2F2D20;--theme-background:#f8f8f5;--theme-accent:#C7A78A;--theme-soft:#f4f0e8;--theme-soft-2:#e7d7c9;--hijau:#2F2D20;}.mapping-preview .konten{margin:0;padding:2rem 0}';
        $content=$this->render($this->fixture());
        $html='<!doctype html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"><style>'.$css.'</style></head><body class="mapping-preview">'.$content.'</body></html>';
        file_put_contents($dir.'/index.html',$html);
        $this->assertFileExists($dir.'/index.html');
    }
}
