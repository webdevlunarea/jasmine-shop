<?php

use App\Services\Api\ApiPagination;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiPaginationTest extends CIUnitTestCase
{
    public function testDefaultsAndMaximumPageSizeAreApplied(): void
    {
        $this->assertSame(['page' => 1, 'per_page' => 20, 'offset' => 0], ApiPagination::resolve(null, null, 20, 40));
        $this->assertSame(['page' => 4, 'per_page' => 40, 'offset' => 120], ApiPagination::resolve('4', '500', 20, 40));
    }

    public function testInvalidAndUnboundedPageInputsAreRejected(): void
    {
        foreach (['0', '-1', '1.5', '999999999999999999999999999999', (string)(ApiPagination::MAX_PAGE + 1), ['2']] as $input) {
            $this->assertNull(ApiPagination::resolve($input, '20', 20, 40));
        }
    }

    public function testInvalidPerPageIsRejectedRatherThanCreatingUnsafeOffset(): void
    {
        foreach (['0', '-5', '2.5', '999999999999999999999999999999', ['25']] as $input) {
            $this->assertNull(ApiPagination::resolve('2', $input, 20, 40));
        }
    }
}
