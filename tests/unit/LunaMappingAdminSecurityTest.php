<?php

use App\Controllers\LunaMappingAdmin;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaMappingAdminSecurityTest extends CIUnitTestCase
{
    protected function tearDown(): void { session()->remove(['isLogin','role','email']); parent::tearDown(); }
    private function controller(string $method = 'POST'): LunaMappingAdmin
    {
        $request=\Config\Services::request(); $request->setMethod($method); $request->setGlobal('post', []);
        $controller=new LunaMappingAdmin();
        $controller->initController($request, \Config\Services::response(), \Config\Services::logger());
        return $controller;
    }
    public function testAnonymousAndCustomerNeverReachCatalogueOrSave(): void
    {
        foreach ([[], ['isLogin'=>true,'role'=>'2','email'=>'customer@example.test']] as $session) {
            session()->remove(['isLogin','role','email']); session()->set($session);
            $this->assertSame(403,$this->controller()->save()->getStatusCode());
            $this->assertSame(403,$this->controller('GET')->catalogue()->getStatusCode());
        }
    }
    public function testAdminSaveRequiresPostAndRouteOwnsCsrfValidation(): void
    {
        session()->set(['isLogin'=>true,'role'=>'1','email'=>'admin@example.test']);
        $this->assertSame(403,$this->controller('GET')->save()->getStatusCode());
        // CSRF runs once in the explicit route filter, before the controller.
        $this->assertSame(302,$this->controller()->save()->getStatusCode());
        $routes=file_get_contents(APPPATH.'Config/Routes.php');
        $this->assertStringContainsString("'/lunamappingadmin/save', 'LunaMappingAdmin::save', ['filter' => ['harusAdmin', 'csrf']]",$routes);
    }
}
