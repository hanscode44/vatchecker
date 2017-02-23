<?php

namespace VATChecker\Tests;

use PHPUnit\Framework\TestCase;
use VATChecker\VATNumber;

/**
 * @package VATChecker\Tests
 * @author Wim Griffioen <wgriffioen@gmail.com>
 */
class ReturnValuesTest extends TestCase
{
    public function testEmptyNumberReturnValue()
    {
        $vatNumber = new VATNumber('');

        $this->assertEquals(VATNumber::EMPTY_VAT_NUMBER, $vatNumber->validateFormat());

        $vatNumber = new VATNumber('xx');

        $this->assertNotEquals(VATNumber::EMPTY_VAT_NUMBER, $vatNumber->validateFormat());
    }

    public function testInvalidCountryCodeReturnValue()
    {
        $vatNumber = new VATNumber('US');

        $this->assertEquals(VATNumber::INVALID_COUNTRY_CODE, $vatNumber->validateFormat());

        $vatNumber = new VATNumber('NL');

        $this->assertNotEquals(VATNumber::INVALID_COUNTRY_CODE, $vatNumber->validateFormat());
    }
}
 