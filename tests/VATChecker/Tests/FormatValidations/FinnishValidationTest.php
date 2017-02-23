<?php

namespace VATChecker\Tests\Validations;

use PHPUnit\Framework\TestCase;
use VATChecker\VATNumber;

/**
 * @package VATChecker\Tests\FormatValidations
 * @author Wim Grifioen <wgriffioen@gmail.com>
 */
class FinnishValidationTest extends TestCase
{
    public function testValidFinnishFormat()
    {
        $vatNumber = new VATNumber('FI12345678');

        $this->assertNotEquals(VATNumber::EMPTY_VAT_NUMBER, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_COUNTRY_CODE, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_FORMAT, $vatNumber->validateFormat());
    }
}
 