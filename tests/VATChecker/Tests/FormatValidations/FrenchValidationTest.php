<?php

namespace VATChecker\Tests\Validations;

use PHPUnit\Framework\TestCase;
use VATChecker\VATNumber;

/**
 * @package VATChecker\Tests\FormatValidations
 * @author Wim Grifioen <wgriffioen@gmail.com>
 */
class FrenchValidationTest extends TestCase
{
    public function testValidFrenchFormat()
    {
        $vatNumber = new VATNumber('FRAB123456789');

        $this->assertNotEquals(VATNumber::EMPTY_VAT_NUMBER, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_COUNTRY_CODE, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_FORMAT, $vatNumber->validateFormat());

        $vatNumber = new VATNumber('FRAB 123456789');

        $this->assertNotEquals(VATNumber::EMPTY_VAT_NUMBER, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_COUNTRY_CODE, $vatNumber->validateFormat());
        $this->assertNotEquals(VATNumber::INVALID_FORMAT, $vatNumber->validateFormat());
    }


}
 