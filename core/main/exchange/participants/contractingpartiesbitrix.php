<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    RuntimeException,
    Main\Helpers\Logger,
    Main\Helpers\Config;
/** ***********************************************************************************************
 * Application participant ContractingPartiesBitrix
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ContractingPartiesBitrix extends AbstractParticipant
{
    private static
        $companiesTypes         =
            [
                'CUSTOMER'      => 'CUSTOMER',
                'SUPPLIER'      => 'SUPPLIER',
                'COMPETITOR'    => 'COMPETITOR',
                'PARTNER'       => 'PARTNER',
                'OTHER'         => 'OTHER'
            ],
        $companiesScopes        =
            [
                'IT'            => 'IT',
                'TELECOM'       => 'TELECOM',
                'MANUFACTURING' => 'MANUFACTURING',
                'BANKING'       => 'BANKING',
                'CONSULTING'    => 'CONSULTING',
                'FINANCE'       => 'FINANCE',
                'GOVERNMENT'    => 'GOVERNMENT',
                'DELIVERY'      => 'DELIVERY',
                'ENTERTAINMENT' => 'ENTERTAINMENT',
                'NOTPROFIT'     => 'NOTPROFIT',
                'OTHER'         => 'OTHER'
            ];
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  array                       data
     ************************************************************************/
    protected function readProvidedData() : array
    {
        $logger                     = Logger::getInstance();
        $config                     = Config::getInstance();
        $logMessagePrefix           = 'BITRIX contractingparties provided data reading';
        $exportRequestUrlTemplate   = $config->getParam('participants.contractingparties.companies.bitrix.exportRequestUrl');
        $userLogin                  = $config->getParam('participants.contractingparties.companies.bitrix.userLogin');
        $userPassword               = $config->getParam('participants.contractingparties.companies.bitrix.userPassword');
        $exportRequestUrl           = str_replace
        (
            ['{LOGIN}', '{PASSWORD}'],
            [$userLogin, $userPassword],
            $exportRequestUrlTemplate
        );

        try
        {
            $data = $this->getBitrixProvidedData($exportRequestUrl);

            foreach ($data as $index => $item)
            {
                $data[$index] = $this->convertProvidedItemData($item);
            }

            return $data;
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: reading data error, $error");
            return [];
        }
    }
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   array $data                 data to write
     * @return  bool                        process result
     ************************************************************************/
    protected function provideDataForDelivery(array $data) : bool
    {
        $logger                     = Logger::getInstance();
        $config                     = Config::getInstance();
        $logMessagePrefix           = 'BITRIX contractingparties providing data for delivery';
        $importRequestUrlTemplate   = $config->getParam('participants.contractingparties.companies.bitrix.importRequestUrl');
        $userLogin                  = $config->getParam('participants.contractingparties.companies.bitrix.userLogin');
        $userPassword               = $config->getParam('participants.contractingparties.companies.bitrix.userPassword');
        $importRequestUrl           = str_replace
        (
            ['{LOGIN}', '{PASSWORD}'],
            [$userLogin, $userPassword],
            $importRequestUrlTemplate
        );

        try
        {
            foreach ($data as $index => $item)
            {
                $data[$index] = $this->convertItemDataForDelivery($item);
            }

            $this->postBitrixDataForDelivery($importRequestUrl, $data);
            return true;
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: posting data error, $error");
            return false;
        }
    }
    /** **********************************************************************
     * get bitrix provided data
     *
     * @param   string $requestUrl          url request
     * @return  array                       provided data
     * @throws  RuntimeException            reading data error
     ************************************************************************/
    private function getBitrixProvidedData(string $requestUrl) : array
    {
        $response               = file_get_contents($requestUrl);
        $responseError          = $response === false;
        $jsonAnswer             = !$responseError ? json_decode($response, true) : [];
        $jsonAnswerIncorrect    = json_last_error() !== JSON_ERROR_NONE || !is_array($jsonAnswer);
        $jsonAnswerResult       = (string)  ($jsonAnswer['result']  ?? '');
        $jsonAnswerErrors       = (array)   ($jsonAnswer['errors']  ?? []);
        $jsonAnswerData         = (array)   ($jsonAnswer['data']    ?? []);

        if ($responseError)
        {
            throw new RuntimeException('cannot read page content');
        }
        if ($jsonAnswerIncorrect)
        {
            throw new RuntimeException('incorrect geted data format');
        }
        if ($jsonAnswerResult != 'ok')
        {
            $errorMessage = count($jsonAnswerErrors) > 0
                ? implode(', ', $jsonAnswerErrors)
                : 'caught error answer with no explains';
            throw new RuntimeException($errorMessage);
        }

        foreach ($jsonAnswerData as $key => $value)
        {
            if (!is_array($value))
            {
                unset($jsonAnswerData[$key]);
            }
        }

        return $jsonAnswerData;
    }
    /** **********************************************************************
     * post bitrix data for delivery
     *
     * @param   string      $requestUrl     url response
     * @param   array       $data           data
     * @return  void
     * @throws  RuntimeException            post data error
     ************************************************************************/
    private function postBitrixDataForDelivery(string $requestUrl, array $data) : void
    {
        $streamContext          = stream_context_create
        ([
            'http' =>
                [
                    'header'    => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'    => 'POST',
                    'content'   => http_build_query(['data' => $data])
                ]
        ]);
        $response               = file_get_contents($requestUrl, false, $streamContext);
        $responseError          = $response === false;
        $jsonAnswer             = !$responseError ? json_decode($response, true) : [];
        $jsonAnswerIncorrect    = json_last_error() !== JSON_ERROR_NONE || !is_array($jsonAnswer);
        $jsonAnswerResult       = (string)  ($jsonAnswer['result']  ?? '');
        $jsonAnswerErrors       = (array)   ($jsonAnswer['errors']  ?? []);

        if ($responseError)
        {
            throw new RuntimeException('no answer caught');
        }
        if ($jsonAnswerIncorrect)
        {
            throw new RuntimeException('incorrect answer format');
        }
        if ($jsonAnswerResult != 'ok')
        {
            $errorMessage = count($jsonAnswerErrors) > 0
                ? implode(', ', $jsonAnswerErrors)
                : 'caught error answer with no explains';
            throw new RuntimeException($errorMessage);
        }
    }
    /** **********************************************************************
     * convert provided item data
     *
     * @param   array $itemData             provided item data
     * @return  array                       converted provided item data
     ************************************************************************/
    private function convertProvidedItemData(array $itemData) : array
    {
        $phonesRaw  = $this->filterNotEmptyArrays((array) $itemData['PHONE']);
        $emailsRaw  = $this->filterNotEmptyArrays((array) $itemData['EMAIL']);
        $sitesRaw   = $this->filterNotEmptyArrays((array) $itemData['SITE']);
        $phones     = [];
        $emails     = [];
        $sites      = [];
        $type       = array_search($itemData['TYPE'],   self::$companiesTypes);
        $scope      = array_search($itemData['SCOPE'],  self::$companiesScopes);
        $type       = $type     !== false   ? $type  : '';
        $scope      = $scope    !== false   ? $scope : '';

        foreach ($phonesRaw as $index => $values)
        {
            $phonePostfix   = is_string($index) && strlen($index) > 0 ? $index : 'OTHER';
            $values         = $this->filterNotEmptyStrings($values);

            foreach ($values as $phone)
            {
                $phones[] = "$phone|$phonePostfix";
            }
        }
        foreach ($emailsRaw as $index => $values)
        {
            $values = $this->filterNotEmptyStrings($values);
            $emails = array_merge($emails, $values);
        }
        foreach ($sitesRaw as $index => $values)
        {
            $values = $this->filterNotEmptyStrings($values);
            $sites  = array_merge($sites, $values);
        }

        $itemData['PHONE']  = $phones;
        $itemData['EMAIL']  = $emails;
        $itemData['SITE']   = $sites;
        $itemData['TYPE']   = $type;
        $itemData['SCOPE']  = $scope;

        return $itemData;
    }
    /** **********************************************************************
     * convert item data for delivery
     *
     * @param   array $itemData             item data for delivery
     * @return  array                       converted item data for delivery
     ************************************************************************/
    private function convertItemDataForDelivery(array $itemData) : array
    {
        $phonesRaw  = $this->filterNotEmptyStrings((array) $itemData['PHONE']);
        $phones     = [];
        $type       = self::$companiesTypes[$itemData['TYPE']]      ??  '';
        $scope      = self::$companiesScopes[$itemData['SCOPE']]    ??  '';

        foreach ($phonesRaw as $phone)
        {
            $phoneExplode   = explode('|', $phone);
            $phoneValue     = $phoneExplode[0] ?? '';
            $phoneType      = isset($phoneExplode[1]) && strlen($phoneExplode[1]) > 0
                ? $phoneExplode[1]
                : 'OTHER';

            if (strlen($phoneValue) > 0)
            {
                $phones[$phoneType]     = $phones[$phoneType] ?? [];
                $phones[$phoneType][]   = $phoneValue;
            }
        }

        $itemData['PHONE']  = $phones;
        $itemData['TYPE']   = $type;
        $itemData['SCOPE']  = $scope;

        return $itemData;
    }
    /** **********************************************************************
     * filter array and leave only not empty string
     *
     * @param   array $values               values
     * @return  array                       filtered values
     ************************************************************************/
    private function filterNotEmptyStrings(array $values) : array
    {
        return array_filter
        (
            $values,
            function($value)
            {
                return is_string($value) && strlen($value) > 0;
            }
        );
    }
    /** **********************************************************************
     * filter array and leave only not empty arrays
     *
     * @param   array $values               values
     * @return  array                       filtered values
     ************************************************************************/
    private function filterNotEmptyArrays(array $values) : array
    {
        return array_filter
        (
            $values,
            function($value)
            {
                return is_array($value) && count($value) > 0;
            }
        );
    }
}