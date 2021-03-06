<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    RuntimeException,
    Main\Helpers\Logger,
    Main\Helpers\Config;
/** ***********************************************************************************************
 * Application participant ContractingPartiesAddressesBitrix
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ContractingPartiesAddressesBitrix extends AbstractParticipant
{
    private static $addressesTypes =
        [
            'REGISTERED'    => 'Registered',
            'PRIMARY'       => 'Primary',
            'HOME'          => 'Home',
            'BENEFICIARY'   => 'Адрес бенефициара',
            'OTHER'         => 'Other'
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
        $logMessagePrefix           = 'BITRIX contractingparties addresses provided data reading';
        $exportRequestUrlTemplate   = $config->getParam('participants.contractingparties.addresses.bitrix.exportRequestUrl');
        $userLogin                  = $config->getParam('participants.contractingparties.addresses.bitrix.userLogin');
        $userPassword               = $config->getParam('participants.contractingparties.addresses.bitrix.userPassword');
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
        $logMessagePrefix           = 'BITRIX contractingparties addresses providing data for delivery';
        $importRequestUrlTemplate   = $config->getParam('participants.contractingparties.addresses.bitrix.importRequestUrl');
        $userLogin                  = $config->getParam('participants.contractingparties.addresses.bitrix.userLogin');
        $userPassword               = $config->getParam('participants.contractingparties.addresses.bitrix.userPassword');
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
        $requisite1CId  = (string)  ($itemData['COMPANY_REQUISITE_1C_ID']   ?? '');
        $typeRaw        = (string)  ($itemData['TYPE']                      ?? '');
        $type           = array_search($typeRaw, self::$addressesTypes);
        $type           = $type !== false ? $type : '';
        $address1CId    = strlen($requisite1CId) > 0 && strlen($type) > 0
            ? "$requisite1CId-$type"
            : '';

        $itemData['TYPE']                   = $type;
        $itemData['COMPANY_ADDRESS_1C_ID']  = $address1CId;

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
        $typeRaw    = (string) ($itemData['Тип'] ?? '');
        $type       = self::$addressesTypes[$typeRaw] ?? '';

        $itemData['TYPE'] = $type;

        return $itemData;
    }
}