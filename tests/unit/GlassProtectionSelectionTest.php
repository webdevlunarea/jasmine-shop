<?php

use App\Services\Payments\GlassProtectionSelection;
use CodeIgniter\Test\CIUnitTestCase;

final class GlassProtectionSelectionTest extends CIUnitTestCase
{
    public function testExplicitSubmittedBooleanTakesPrecedenceAndFalseStringStaysFalse(): void
    {
        self::assertTrue(GlassProtectionSelection::resolve('true', false, 25000));
        self::assertFalse(GlassProtectionSelection::resolve('false', true, 25000));
        self::assertFalse(GlassProtectionSelection::resolve('0', true, 25000));
        self::assertTrue(GlassProtectionSelection::resolve(null, 'true', 25000));
        self::assertFalse(GlassProtectionSelection::resolve(null, 'false', 25000));
    }

    public function testProtectionCannotBeSelectedWhenCartHasNoEligibleGlassProducts(): void
    {
        self::assertFalse(GlassProtectionSelection::resolve('true', true, 0));
        self::assertFalse(GlassProtectionSelection::resolve(null, true, -25000));
    }
}
