<?php

use CodeIgniter\Test\CIUnitTestCase;

final class SecurityHardeningSurfaceTest extends CIUnitTestCase
{
    public function testSecretDumpAndRetiredPublicMutationRoutesAreAbsent(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $pages = file_get_contents(APPPATH . 'Controllers/Pages.php');

        foreach ([
            '/cobaenv', '/actionaddvoucherapi', '/apiauto/', '/olahdb/desk', '/isipath',
            '/copygambar', '/fixisiartikel', '/fixkurir', '/isigambarartikel',
            '/gantiukuran/', '/isitier/', "'/ro",
        ] as $retiredRoute) {
            $this->assertStringNotContainsString($retiredRoute, $routes);
        }

        $this->assertStringNotContainsString('function cobaenv', $pages);
        $this->assertStringNotContainsString('function actionAddVoucherAPI', $pages);
        $this->assertStringNotContainsString('dd($myEnv)', $pages);
    }

    public function testAdminPostRoutesRequireAuthorizationAndCsrf(): void
    {
        $routes = file(APPPATH . 'Config/Routes.php', FILE_IGNORE_NEW_LINES);
        foreach ($routes as $line) {
            if (preg_match('/^\s*\$routes->post\(/', $line) && str_contains($line, "'harusAdmin'")) {
                $this->assertStringContainsString("'csrf'", $line, $line);
            }
        }
    }

    public function testVoucherEmailBroadcastIsAdminAndCsrfProtected(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $view = file_get_contents(APPPATH . 'Views/pages/listVoucher.php');
        $this->assertStringContainsString("'/actionbroadcastvoucheremail', 'Pages::actionBroadcastVoucherEmail', ['filter' => ['harusAdmin', 'csrf']]", $routes);
        $this->assertStringContainsString("'X-CSRF-TOKEN': csrfHash", $view);
    }

    public function testShippingProviderKeysAreLoadedFromEnvironmentAndTlsVerificationIsEnabled(): void
    {
        $pages = file_get_contents(APPPATH . 'Controllers/Pages.php');
        $this->assertStringContainsString("env('RAJAONGKIR_API_KEY'", $pages);
        $this->assertStringContainsString("env('DAKOTA_API_KEY'", $pages);
        $this->assertStringContainsString("env('DAKOTA_CLIENT_ID'", $pages);
        $this->assertStringNotContainsString('CURLOPT_SSL_VERIFYHOST => 0', $pages);
        $this->assertStringNotContainsString('CURLOPT_SSL_VERIFYPEER => 0', $pages);
    }
}
