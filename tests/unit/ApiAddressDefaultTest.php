<?php

use App\Controllers\Api\V1\AddressController;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiAddressDefaultTest extends CIUnitTestCase
{
    private function controller(): AddressController
    {
        return new class extends AddressController {
            public function shouldBecomeDefault(bool $requested, bool $creating, bool $hasAddresses): bool
            {
                return $this->shouldMakeDefault($requested, $creating, $hasAddresses);
            }
        };
    }

    public function testFirstAddressBecomesDefaultAutomatically(): void
    {
        $this->assertTrue($this->controller()->shouldBecomeDefault(false, true, false));
    }

    public function testAdditionalAddressDoesNotReplaceExistingDefaultUnlessRequested(): void
    {
        $controller = $this->controller();

        $this->assertFalse($controller->shouldBecomeDefault(false, true, true));
        $this->assertTrue($controller->shouldBecomeDefault(true, true, true));
    }

    public function testUpdatingExistingAddressKeepsDefaultOnlyWhenExplicitlySelected(): void
    {
        $controller = $this->controller();

        $this->assertFalse($controller->shouldBecomeDefault(false, false, true));
        $this->assertTrue($controller->shouldBecomeDefault(true, false, true));
    }
}
