<?php

namespace VATChecker\Tests\Validation;

use PHPUnit\Framework\TestCase;
use VATChecker\VATNumber;

class CroatianValidationTest extends TestCase
{
    public function testCroationFormat()
    {
        $vatNumber = new VATNumber('HR99999999999');

        $this->assertNotEquals(VATNumber::EMPTY_VAT_NUMBER, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_COUNTRY_CODE, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_FORMAT, $vatNumber->validateFormat());
    }
}
