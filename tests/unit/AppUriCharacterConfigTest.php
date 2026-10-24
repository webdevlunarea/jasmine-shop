<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

final class AppUriCharacterConfigTest extends CIUnitTestCase
{
    public function testPaddedBase64RedirectSegmentAllowsEqualsPadding(): void
    {
        $allowed = (new App())->permittedURIChars;

        $this->assertSame(1, preg_match('/\\A[' . $allowed . ']+\\z/iu', 'Lw=='));
    }
}
