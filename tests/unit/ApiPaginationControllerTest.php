<?php

use App\Controllers\Api\V1\CatalogController;
use App\Controllers\Api\V1\OrderController;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiPaginationControllerTest extends CIUnitTestCase
{
    private array $previousGet = [];

    protected function setUp(): void
    {
        parent::setUp();
        $request = service('request');
        $this->previousGet = $request->getGet() ?? [];
        $request->setMethod('GET');
    }

    protected function tearDown(): void
    {
        service('request')->setGlobal('get', $this->previousGet);
        parent::tearDown();
    }

    private function catalogController(): CatalogController
    {
        return new class extends CatalogController {
            public function __construct()
            {
                $this->request = service('request');
                $this->response = service('response');
            }
        };
    }

    private function orderController(): OrderController
    {
        return new class extends OrderController {
            public function __construct()
            {
                $this->request = service('request');
                $this->response = service('response');
            }
        };
    }

    public function testCatalogRejectsOutOfRangePageBeforeQueryingCatalog(): void
    {
        service('request')->setGlobal('get', ['page' => '10001']);
        $response = $this->catalogController()->products();

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertSame('VALIDATION_ERROR', $body['error']['code']);
    }

    public function testOrderHistoryRejectsMalformedPageBeforeQueryingOrders(): void
    {
        service('request')->setGlobal('get', ['page' => '1.2']);
        $response = $this->orderController()->index();

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertSame('VALIDATION_ERROR', $body['error']['code']);
    }
}
