<?php

use App\Services\Auth\IndonesianMobileNumber;
use CodeIgniter\Test\CIUnitTestCase;

final class IndonesianMobileNumberTest extends CIUnitTestCase
{
    private IndonesianMobileNumber $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new IndonesianMobileNumber();
    }

    public function testAcceptsAndNormalizesIndonesianMobileFormats(): void
    {
        $this->assertSame('081298763450', $this->validator->normalize('0812 9876 3450'));
        $this->assertSame('081298763450', $this->validator->normalize('+62 812-9876-3450'));
        $this->assertSame('081298763450', $this->validator->normalize('6281298763450'));
    }

    public function testRejectsInvalidAndObviousPlaceholderNumbers(): void
    {
        foreach (['1234567890', '081234', '081111111111', '081234567890', 'abc081234567890', '+1 202 555 0199'] as $number) {
            $this->assertNull($this->validator->normalize($number), $number);
        }
    }
}
