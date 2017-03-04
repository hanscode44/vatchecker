<?php

require 'vendor/autoload.php';

use VATChecker\VATNumber;

$number = new VATNumber('NL999999999B01');

if ($number->isValid()) {

    echo 'Business Name: ' . $number->getName() . '<br />';
    echo 'Address: ' . $number->getAddress() . '<br />';
} else {

    echo "Invalid VAT-Number";
};
