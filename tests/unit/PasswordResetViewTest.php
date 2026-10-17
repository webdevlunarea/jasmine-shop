<?php

use CodeIgniter\Test\CIUnitTestCase;

final class PasswordResetViewTest extends CIUnitTestCase
{
    /** @dataProvider steps */
    public function testCleanResetFormsKeepNumericOtpAndNoSecretsInValues(string $step): void
    {
        $c=config(\Config\Recaptcha::class); $original=$c->enabled; $c->enabled=false;
        $renderer=new class {
            public function extend($name): string { return ''; }
            public function section($name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render(string $step): string {
                $email='buyer@example.test'; $maskedEmail='B****@example.test'; $message=''; $error='';
                ob_start(); try { include APPPATH.'Views/pages/password_reset.php'; return ob_get_contents(); } finally { ob_end_clean(); }
            }
        };
        try { $html=$renderer->render($step); } finally { $c->enabled=$original; }
        $dom=new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html); $xp=new DOMXPath($dom);
        $this->assertSame(1,$xp->query('//h1')->length);
        $this->assertSame(1,$xp->query('//form[@method="post"]')->length);
        $this->assertSame(1,$xp->query('//input[@name="'.csrf_token().'"]')->length);
        $this->assertSame(site_url($step==='request'?'lupa-sandi':'reset-sandi'),$xp->query('//form')->item(0)->getAttribute('action'));
        if($step==='confirm') {
            $this->assertStringContainsString('B****@example.test',$html);
            $this->assertStringNotContainsString('buyer@example.test',$html);
            $this->assertStringContainsString('name="confirm_email"',$html);
            $this->assertStringContainsString('name="change_identity"',$html);
        }
        if($step==='reset') {
            $input=$xp->query('//input[@name="otp"]')->item(0);
            $this->assertSame('text',$input->getAttribute('type'));
            $this->assertSame('numeric',$input->getAttribute('inputmode'));
            $this->assertSame('[0-9]{6}',$input->getAttribute('pattern'));
            $this->assertSame('6',$input->getAttribute('maxlength'));
            $this->assertSame('one-time-code',$input->getAttribute('autocomplete'));
            $this->assertSame(0,$xp->query('//input[@name="otp" or @type="password"]/@value')->length);
            $this->assertSame(2,$xp->query('//input[@type="password" and @autocomplete="new-password"]')->length);
        }
        foreach($xp->query('//input[not(@type="hidden")]') as $input) {
            $this->assertSame(1,$xp->query('//label[@for="'.$input->getAttribute('id').'"]')->length);
        }
        if(getenv('PASSWORD_RESET_ARTIFACTS')==='1') {
            $dir=WRITEPATH.'cache/password-reset-review'; if(!is_dir($dir)) mkdir($dir,0775,true);
            $url='file:///'.str_replace('\\','/',rtrim(FCPATH,'\\/')); $styles='';
            foreach(['bootstrap.min.css','style.css','commerce-responsive.css','buyer-auth.css'] as $css) $styles.='<link rel="stylesheet" href="'.$url.'/css/'.$css.'">';
            file_put_contents($dir.'/'.$step.'.html','<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'.$styles.'<style>:root{--theme-primary:#2f2d20;--theme-primary-hover:#232217;--theme-soft:#f7f2ed;--theme-soft-2:#dfcbb9;--theme-background:#fff}</style></head><body class="buyer-layout buyer-auth-body">'.$html.'</body></html>');
        }
    }
    public static function steps(): array { return [['request'],['reset']]; }

    public function testEmailIsEscapedTableBasedAndContainsOnlyNumericOtp(): void
    {
        $html=view('emails/password_reset',['mode'=>'code','code'=>'012345'],['saveData'=>false]);
        $dom=new DOMDocument(); @$dom->loadHTML($html); $xp=new DOMXPath($dom);
        $this->assertSame(1,$xp->query('//h1')->length);
        $this->assertGreaterThanOrEqual(3,$xp->query('//table[@role="presentation"]')->length);
        $this->assertSame(0,$xp->query('//script|//form|//iframe|//img')->length);
        $this->assertStringContainsString('012345',$html);
        $this->assertStringContainsString('10 menit',strip_tags($html));
        $this->assertStringNotContainsString('<img',view('emails/password_reset',['mode'=>'code','code'=>'<img src=x>'],['saveData'=>false]));
        if(getenv('PASSWORD_RESET_ARTIFACTS')==='1') {
            $dir=WRITEPATH.'cache/password-reset-review'; if(!is_dir($dir)) mkdir($dir,0775,true);
            file_put_contents($dir.'/email.html',$html);
        }
    }

    public function testChangedNotificationDoesNotIncludeOtpOrPasswordFields(): void
    {
        $html=view('emails/password_reset',['mode'=>'changed','code'=>'012345'],['saveData'=>false]);
        $this->assertStringNotContainsString('012345',$html);
        $this->assertStringContainsString('Sandi berhasil diperbarui',$html);
        $this->assertStringNotContainsString('<input',$html);
    }
}
