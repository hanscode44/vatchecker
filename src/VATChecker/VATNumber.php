<?php

namespace VATChecker;

/**
 * Class used to check the validity of a VAT-Number
 *
 * @package VATChecker
 * @author wgriffioen, hloos
 *
 */
class VATNumber
{
    const RESPONSE_TIMEOUT = 5;
    const INVALID_COUNTRY_CODE = 1;
    const INVALID_FORMAT = 2;
    const UNABLE_TO_CHECK = 4;
    const VALID_VAT_NUMBER = 8;
    const INVALID_VAT_NUMBER = 16;
    const EMPTY_VAT_NUMBER = 32;
    const VALID_VAT_FORMAT = 64;

    /**
     * @var string Contains the first two characters of the input of the constructor
     */
    private $countryCode;

    /**
     * @var string Contains the part of the VAT-number after the country code
     */
    private $vatNumber;

    /**
     * @var string Will contain the input of the constructor
     */
    private $input;

    /**
     * @var \SoapClient Holds the \SoapClient object
     */
    private $soapClient;

    /**
     * @var object The result
     */
    private $response;

    /**
     * @var string Contains the name of the company of the response
     */
    private $name;

    /**
     * @var string Contains the address of the company in the response
     */
    private $address;

    /**
     * @var array Contains all the regular expressions for all the possible VAT-numbers
     */
    private $countries = [
        'AT' => '/^ATU[0-9]{8}$/',
        'BE' => '/^BE0[0-9]{9}$/',
        'BG' => '/^BG[0-9]{9,10}$/',
        'CY' => '/^CY[0-9]{8}L$/',
        'CZ' => '/^CZ[0-9]{8,10}$/',
        'DE' => '/^DE[0-9]{9}$/',
        'DK' => '/^DK[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}$/',
        'EE' => '/^EE[0-9]{9}$/',
        'EL' => '/^EL[0-9]{9}$/',
        'ES' => '/^ES[A-Z0-9][0-9]{7}[A-Z0-9]$/',
        'FI' => '/^FI[0-9]{8}$/',
        'FR' => '/^FR[A-Z]{2}\s?[0-9]{9}$/',
        'GB' => [
            '/^GB[0-9]{3}\s?[0-9]{4}\s?[0-9]{2}(\s?[0-9]{3})?$/',
            '/^GB(HA|GD)[0-9]{3}$/'
        ],
        'HU' => '/^HU[0-9]{8}$/',
        'HR' => '/^HR[0-9]{11}$/',
        'IE' => '/^IE[0-9][A-Z0-9][0-9]{5}[A-Z]$/',
        'IT' => '/^IT[0-9]{11}$/',
        'LT' => '/^LT([0-9]{9,12})$/',
        'LU' => '/^LU[0-9]{8}$/',
        'LV' => '/^LV[0-9]{11}$/',
        'MT' => '/^MT[0-9]{8}$/',
        'NL' => '/^NL[0-9]{9}B[0-9]{2}$/',
        'PL' => '/^PL[0-9]{10}$/',
        'PT' => '/^PT[0-9]{9}$/',
        'RO' => '/^RO[0-9]{9}$/',
        'SE' => '/^SE[0-9]{12}$/',
        'SI' => '/^SI[0-9]{8}$/',
        'SK' => '/^SK[0-9]{10}$/'
    ];

    /**
     * Constructor
     *
     * Stores the country-code and the actual VAT-number in the class
     * variables.
     *
     * @param string $vatNumber The VAT-Number to be checked
     */
    public function __construct($vatNumber)
    {
        // Set the default_socket_timeout
        ini_set('default_socket_timeout', self::RESPONSE_TIMEOUT);

        // Entire input
        $this->input = str_replace('.', '', $vatNumber);
        $this->input = strtoupper($this->input);

        // Country code
        $this->countryCode = strtoupper(substr($vatNumber, 0, 2));

        // Actual number
        $this->vatNumber = substr($vatNumber, 2, strlen($vatNumber));
    }

    /**
     * Validate the format of the given VAT number
     *
     * @return int
     */
    public function validateFormat()
    {
        // Check if the VAT-number has been entered
        if (strlen($this->input) == 0) {
            return self::EMPTY_VAT_NUMBER;
        }

        // Check if the country code exists
        if (!isset($this->countries[$this->countryCode])) {
            return self::INVALID_COUNTRY_CODE;
        }

        // Store the regex or array of multiple regular expressions in local variable
        $regex = $this->countries[$this->countryCode];

        // Match the input against the regular expressions
        if (is_array($regex)) {
            $validFormat = false;

            // Multiple regular expressions
            foreach ($regex as $regularExpression) {
                if (preg_match($regularExpression, $this->input)) {
                    $validFormat = true;
                    break;
                }
            }

            if (!$validFormat) {
                return self::INVALID_FORMAT;
            }
        } else {
            // Single regular expression
            if (!preg_match($regex, $this->input)) {
                return self::INVALID_FORMAT;
            }
        }

        return self::VALID_VAT_FORMAT;
    }

    /**
     * Performs a call against the webservice of the EC
     *
     * @return int
     */
    public function checkValidity()
    {
        if ($this->validateFormat() === self::INVALID_FORMAT) {
            return self::INVALID_VAT_NUMBER;
        }

        $this->soapClient = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');

        try {
            $response = $this->soapClient->checkVat(
                [
                    'countryCode' => $this->countryCode,
                    'vatNumber' => $this->vatNumber,
                ]
            );
        } catch (\SoapFault $e) {
            // In case of a timeout return VATChecker::UNABLE_TO_CHECK
            return self::UNABLE_TO_CHECK;
        }

        if ($response->valid) {

            $this->response = $response;

            return self::VALID_VAT_NUMBER;
        } else {
            return self::INVALID_VAT_NUMBER;
        }
    }

    public function requestIdentifier($ownVatNumber)
    {
        if ($this->validateFormat() === self::INVALID_FORMAT) {
            return self::INVALID_VAT_NUMBER;
        }

        // Country code
        $requesterCountryCode = strtoupper(substr($ownVatNumber, 0, 2));

        // Actual number
        $requesterVatNumber = substr($ownVatNumber, 2, strlen($ownVatNumber));

        $this->soapClient = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');

        try {
            $response = $this->soapClient->checkVatApprox(
                [
                    'countryCode' => $this->countryCode,
                    'vatNumber' => $this->vatNumber,
                    'requesterCountryCode' => $requesterCountryCode,
                    'requesterVatNumber' => $requesterVatNumber
                ]
            );
        } catch (\SoapFault $e) {
            // In case of a timeout return VATChecker::UNABLE_TO_CHECK
            return self::UNABLE_TO_CHECK;
        }

        if ($response->valid) {

            $this->response = $response;

            return self::VALID_VAT_NUMBER;
        } else {
            return self::INVALID_VAT_NUMBER;
        }

    }

    /**
     * Checks if the VAT-number is valid.
     *
     * If it's not valid, it will not tell why it isn't valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->checkValidity() === self::VALID_VAT_NUMBER;
    }

    /**
     * Returns the name of the company
     *
     * @return string Name of the company
     */
    public function getName()
    {
        return $this->response->name;
    }

    /**
     * Returns the address of the company
     *
     * @return string Address of the company
     */
    public function getAddress()
    {
        return $this->removeNewLines($this->response->address);
    }

    /**
     * Returns the company name of the requester
     *
     * @return string Tradername -> name of the company of the requester
     */
    public function getTraderName()
    {
        return $this->response->traderName;
    }

    /**
     * Returns the company type of the requester
     *
     * @return string company type of the requester
     */
    public function getTraderCompanyType()
    {
        return $this->response->traderCompanyType;
    }

    /**
     * Returns the address of the requester
     *
     * @return string address of the requester
     */
    public function getTraderAddress()
    {
        return $this->response->traderAddress;
    }

    /**
     * Returns the request identificationNumber
     *
     * @return string
     */
    public function getRequestIdentifier()
    {
        return $this->response->requestIdentifier;
    }

    /**
     * Returns the response of the requestIdentifier method
     *
     * @return object
     */
    public function getResponse()
    {
        return $this->response;
    }


    protected function removeNewLines(string $sString)
    {
        return str_replace(["\n", "\r\n"], ', ', $sString);
    }

}
