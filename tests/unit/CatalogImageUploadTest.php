<?php

use App\Services\CatalogImageUpload;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

final class CatalogImageUploadTest extends CIUnitTestCase
{
    private array $temp = [];
    protected function tearDown(): void
    {
        foreach ($this->temp as $path) if (is_file($path)) unlink($path);
        session()->remove(['role','isLogin']);
        parent::tearDown();
    }
    private function image(string $format = 'webp', int $width = 12): string
    {
        $image = imagecreatetruecolor($width, 8); ob_start();
        try { $format === 'avif' ? imageavif($image) : ($format === 'png' ? imagepng($image) : imagewebp($image)); return ob_get_contents(); }
        finally { ob_end_clean(); imagedestroy($image); }
    }
    private function file(string $bytes, int $error = UPLOAD_ERR_OK): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'luna-media-'); $this->temp[] = $path; file_put_contents($path, $bytes);
        return new class($path, 'selected.webp', 'image/webp', strlen($bytes), $error) extends UploadedFile {
            public function isValid(): bool { return $this->getError() === UPLOAD_ERR_OK; }
        };
    }
    private function controller(array $files, array $post = [], array $old = [])
    {
        return new class($files, $post, $old, $this->image()) extends \App\Controllers\Pages {
            public array $saved = [];
            public function __construct($files, $post, $old, $bytes) {
                $this->request = new class($files,$post) {
                    private array $files; private array $post;
                    public function __construct($files,$post) { $this->files=$files; $this->post=$post; }
                    public function getFile($key) { return $this->files[$key] ?? null; }
                    public function getPost($key) { return $this->post[$key] ?? null; }
                };
                $this->barangModel = new class($bytes) {
                    private string $bytes;
                    public function __construct($bytes) { $this->bytes=$bytes; }
                    public function where(...$args) { return $this; }
                    public function first() { return ['id'=>'P1','varian'=>'["Putih"]','jml_varian'=>1,'diskon'=>10,'gambar'=>$this->bytes]; }
                };
                $this->gambarBarangModel = new class($old) {
                    private array $old;
                    public function __construct($old) { $this->old=$old; }
                    public function getGambar($id) { return $this->old; }
                };
            }
            protected function guardOrderAdminRequest() { return null; }
            protected function saveProductMedia(string $id, array $product, array $images, array $map): void { $this->saved=[$id,$product,$images,$map]; }
        };
    }
    public function testWebpContentIsValidatedAndKeptWithoutReencoding(): void
    {
        $bytes=$this->image(); $this->assertSame('image/webp', CatalogImageUpload::validate($bytes));
        $this->assertSame($bytes, CatalogImageUpload::read($this->file($bytes)));
    }
    public function testLegacyPngPreviewsUseActualMimeButNewPngIsRejected(): void
    {
        $bytes=$this->image('png'); $this->assertStringStartsWith('data:image/png;base64,',CatalogImageUpload::source($bytes));
        $this->expectException(DomainException::class); CatalogImageUpload::validate($bytes);
    }
    public function testAvifIsAcceptedOnlyWhenDimensionsCanBeValidated(): void
    {
        if (!function_exists('imageavif') || empty(gd_info()['AVIF Support'])) $this->markTestSkipped('Local GD has no AVIF encoder for fixture.');
        $bytes=$this->image('avif');
        $this->assertStringStartsWith('data:image/avif;base64,',CatalogImageUpload::source($bytes));
        $info=getimagesizefromstring($bytes);
        if (empty($info[0]) || empty($info[1])) $this->expectException(DomainException::class);
        $this->assertSame('image/avif',CatalogImageUpload::validate($bytes));
    }
    /** @dataProvider invalidContent */
    public function testSpoofedAndEmptyFilesAreRejected(string $bytes): void
    { $this->expectException(DomainException::class); CatalogImageUpload::validate($bytes); }
    public static function invalidContent(): array { return [[''],['<svg xmlns="http://www.w3.org/2000/svg"></svg>'],['<?php echo "fake.webp"; ?>'],['not an image']]; }
    public function testOversizedImageIsRejected(): void
    { $this->expectException(DomainException::class); CatalogImageUpload::validate($this->image(), 10); }
    public function testExcessiveDimensionsAreRejectedBeforeDecode(): void
    { $this->expectException(DomainException::class); CatalogImageUpload::validate($this->image('webp',4097)); }
    public function testOptionalMissingFileIsNotATransferFailure(): void
    { $this->assertNull(CatalogImageUpload::read(null)); $this->assertNull(CatalogImageUpload::read($this->file('',UPLOAD_ERR_NO_FILE))); }
    public function testRequiredMissingFileIsRejected(): void
    { $this->expectException(DomainException::class); CatalogImageUpload::read(null,'Banner',4194304,true); }
    public function testPhpUploadErrorIsNotSilentlyIgnored(): void
    { $this->expectException(DomainException::class); CatalogImageUpload::read($this->file('',UPLOAD_ERR_INI_SIZE)); }
    public function testSavedAdditionalSlotsAreNotHiddenByVariantCount(): void
    {
        $this->assertSame(7,CatalogImageUpload::slotCount(['varian'=>'["Putih"]','jml_varian'=>1],['gambar7'=>'saved']));
        $this->assertSame(50,CatalogImageUpload::slotCount(['varian'=>'[]','jml_varian'=>1000],[]));
    }
    public function testVersionedSlotUrlsContainNoBlobAndChangeWhenImageChanges(): void
    {
        $url=CatalogImageUpload::slotUrl('P1',2,'first');
        $this->assertMatchesRegularExpression('~^/imgproduct/P1/2\?v=[a-f0-9]{16}$~',$url);
        $this->assertNotSame($url,CatalogImageUpload::slotUrl('P1',2,'second'));
        $this->assertSame('/img/nopic.jpg',CatalogImageUpload::slotUrl('P1',51,'first'));
        $this->assertSame('/img/nopic.jpg',CatalogImageUpload::slotUrl('P1',1,null));
    }
    public function testGalleryEndpointUsesActualMimeAndSupportsConditionalRequests(): void
    {
        $bytes=$this->image('png');
        $controller=new class($bytes) extends \App\Controllers\GambarController {
            public array $queries=[];
            public function __construct($bytes) {
                $this->response=\Config\Services::response(null,false);
                $this->request=new class { public string $etag=''; public function getHeaderLine($name){return $this->etag;} };
                $this->gambarBarangModel=new class($bytes) {
                    public array $queries=[]; private string $bytes;
                    public function __construct($bytes){$this->bytes=$bytes;}
                    public function select($key){$this->queries[]=$key;return $this;}
                    public function where(...$args){return $this;}
                    public function first(){return ['gambar2'=>$this->bytes];}
                };
            }
            public function countQueries(){return count($this->gambarBarangModel->queries);}
            public function etag($value){$this->request->etag=$value;}
        };
        $this->assertSame(404,$controller->tampilFotoProduk('P1','51')->getStatusCode());
        $this->assertSame(0,$controller->countQueries());
        $response=$controller->tampilFotoProduk('P1','2');
        $this->assertSame(200,$response->getStatusCode());
        $this->assertSame('image/png',$response->getHeaderLine('Content-Type'));
        $this->assertSame($bytes,$response->getBody());
        $controller->etag($response->getHeaderLine('ETag'));
        $this->assertSame(304,$controller->tampilFotoProduk('P1','2')->getStatusCode());
    }
    public function testProductCanAddAnExtraPhotoWithoutChangingMasterVariants(): void
    {
        $bytes=$this->image(); $c=$this->controller(['gambar2'=>$this->file($bytes)],['variant_image_'.md5('Putih')=>'1']);
        $c->actionEditProduct('P1');
        $this->assertSame(['id'=>'P1','diskon'=>10.0],$c->saved[1]);
        $this->assertSame($bytes,$c->saved[2]['gambar2']);
        $this->assertNotEmpty($c->saved[2]['gambar1']); // New gallery row retains the previous public cover.
        $this->assertSame(['Putih'=>1],$c->saved[3]);
    }
    public function testMainPhotoUpdatesBothPublicCoverAndGallery(): void
    {
        $bytes=$this->image(); $c=$this->controller(['gambar1'=>$this->file($bytes)]); $c->actionEditProduct('P1');
        $this->assertSame($bytes,$c->saved[1]['gambar']); $this->assertSame($bytes,$c->saved[2]['gambar1']);
    }
    public function testWebsiteOwnedProductFieldsCanBeEditedWithoutChangingMasterFields(): void
    {
        $post = [
            'harga'=>'1299000', 'diskon'=>'15', 'deskripsi'=>'<p>Deskripsi baru</p><script>alert(1)</script>',
            'deskripsi_nonhtml'=>'Deskripsi baru', 'pencarian'=>'lemari minimalis', 'shopee'=>'https://shopee.co.id/item',
            'tokped'=>'', 'tiktok'=>'', 'youtube'=>'https://youtube.com/watch?v=abc',
            'stok'=>'999', 'varian'=>'Varian yang dimanipulasi', 'kategori'=>'kategori-manipulasi',
        ];
        $c=$this->controller([], $post);
        $c->actionEditProduct('P1');
        $saved=$c->saved[1];
        $this->assertSame('1299000',$saved['harga']);
        $this->assertSame(15.0,$saved['diskon']);
        $this->assertSame('<p>Deskripsi baru</p>',$saved['deskripsi']);
        $this->assertSame('Deskripsi baru',$saved['deskripsi_nonhtml']);
        $this->assertSame('lemari minimalis',$saved['pencarian']);
        $this->assertSame('https://shopee.co.id/item',$saved['shopee']);
        $this->assertArrayNotHasKey('stok',$saved);
        $this->assertArrayNotHasKey('varian',$saved);
        $this->assertArrayNotHasKey('kategori',$saved);
    }
    public function testInvalidWebsitePriceOrMarketplaceUrlDoesNotSaveAnyChanges(): void
    {
        foreach ([['harga'=>'0'], ['harga'=>'1299000','shopee'=>'javascript:alert(1)']] as $post) {
            $c=$this->controller([], $post);
            $c->actionEditProduct('P1');
            $this->assertSame([],$c->saved);
        }
    }
    public function testInvalidSecondFilePreventsEveryWriteIncludingDiscountAndMapping(): void
    {
        $c=$this->controller(['gambar1'=>$this->file($this->image()),'gambar2'=>$this->file($this->image('png'))],['diskon'=>'25']);
        $c->actionEditProduct('P1'); $this->assertSame([],$c->saved);
    }
    public function testFailedUploadPreventsEveryWrite(): void
    { $c=$this->controller(['gambar1'=>$this->file('',UPLOAD_ERR_PARTIAL)]); $c->actionEditProduct('P1'); $this->assertSame([],$c->saved); }
    public function testExcessiveBatchDoesNotSaveAnyMedia(): void
    {
        $bytes=str_pad($this->image(),CatalogImageUpload::MAX_BYTES,"\0");
        $file=$this->file($bytes); $c=$this->controller(['gambar1'=>$file,'gambar2'=>$file,'gambar3'=>$file,'gambar4'=>$file]);
        $c->actionEditProduct('P1'); $this->assertSame([],$c->saved);
    }
    public function testMappingToAnEmptySlotIsRejected(): void
    { $c=$this->controller([],['variant_image_'.md5('Putih')=>'49']); $c->actionEditProduct('P1'); $this->assertSame([],$c->saved); }
    public function testNoUploadPreservesOldMediaAndAcceptsSavedExtraSlot(): void
    {
        $c=$this->controller([],['variant_image_'.md5('Putih')=>'5'],['gambar6'=>$this->image()]); $c->actionEditProduct('P1');
        $this->assertSame(['id'=>'P1'],$c->saved[2]); $this->assertSame(['Putih'=>5],$c->saved[3]);
    }
    public function testAnonymousMediaSubmissionStopsBeforeModelsAreAccessed(): void
    {
        session()->remove(['isLogin','role']);
        $c=new class extends \App\Controllers\Pages { public function __construct() { $this->request=new class { public function getMethod(){return 'post';} }; $this->response=\Config\Services::response(null,false); } };
        $this->assertSame(403,$c->actionEditProduct('P1')->getStatusCode());
        $this->assertSame(403,$c->actionAddBanner()->getStatusCode());
        $this->assertSame(403,$c->actionCategoryImagesAdmin()->getStatusCode());
    }

    public function testInvalidBannerUploadDoesNotUpdateMetadataOrReplaceOldImage(): void
    {
        $controller=$this->controller(['gambar'=>$this->file($this->image('png'))],['judul'=>'Banner baru']);
        $model=new class { public array $updates=[]; public function where(...$args){return $this;} public function first(){return ['id'=>1,'gambar'=>'legacy'];} public function set($data){$this->updates=$data;return $this;} public function update(){return true;} };
        $property=new ReflectionProperty(\App\Controllers\Pages::class,'bannerModel');$property->setAccessible(true);$property->setValue($controller,$model);
        $controller->actionEditBanner(1);$this->assertSame([],$model->updates);
    }

    public function testEditingBannerWithoutNewFilePreservesItsExistingImage(): void
    {
        $controller=$this->controller([],['judul'=>'Judul saja']);
        $model=new class { public array $updates=[]; public function where(...$args){return $this;} public function first(){return ['id'=>1,'gambar'=>'legacy'];} public function set($data){$this->updates=$data;return $this;} public function update(){return true;} };
        $property=new ReflectionProperty(\App\Controllers\Pages::class,'bannerModel');$property->setAccessible(true);$property->setValue($controller,$model);
        $controller->actionEditBanner(1);$this->assertSame('Judul saja',$model->updates['judul']);$this->assertArrayNotHasKey('gambar',$model->updates);
    }
}
