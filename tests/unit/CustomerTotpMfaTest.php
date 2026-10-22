<?php

use App\Services\Auth\CustomerTotpMfa;
use PHPUnit\Framework\TestCase;

final class CustomerTotpMfaTest extends TestCase
{
    public function testRfc6238Sha1SixDigitVectorMatchesAtUnixTimestamp59(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // Base32("12345678901234567890")
        self::assertSame(1, CustomerTotpMfa::matchCounter($secret, '287082', 59));
    }

    public function testTotpRejectsMalformedOrIncorrectCodes(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        self::assertNull(CustomerTotpMfa::matchCounter($secret, '28708', 59));
        self::assertNull(CustomerTotpMfa::matchCounter($secret, '287083', 59));
        self::assertNull(CustomerTotpMfa::matchCounter($secret, '１２３４５６', 59));
    }
}
