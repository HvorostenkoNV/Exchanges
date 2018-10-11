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
        $response = file_get_contents($requestUrl);
        if ($response === false)
        {
            throw new RuntimeException('cannot read page content');
        }

        $jsonAnswer = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($jsonAnswer))
        {
            throw new RuntimeException('incorrect geted data format');
        }

        if (!array_key_exists('result', $jsonAnswer) || $jsonAnswer['result'] != 'ok')
        {
            $errorMessage = array_key_exists('message', $jsonAnswer)
                ? $jsonAnswer['message']
                : 'caught error answer with no explains';

            throw new RuntimeException($errorMessage);
        }

        return array_key_exists('data', $jsonAnswer) && is_array($jsonAnswer['data'])
            ? $jsonAnswer['data']
            : [];
    }
    /** **********************************************************************
     * post bitrix data for delivery
     *
     * @param   string      $requestUrl     url response
     * @param   array       $data           data
     * @throws  RuntimeException            post data error
     ************************************************************************/
    private function postBitrixDataForDelivery(string $requestUrl, array $data) : void
    {
        $streamContext = stream_context_create
        ([
            'http' =>
                [
                    'header'    => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'    => 'POST',
                    'content'   => http_build_query(['data' => $data])
                ]
        ]);

        $response = file_get_contents($requestUrl, false, $streamContext);
        if ($response === false)
        {
            throw new RuntimeException('no answer caught');
        }

        $jsonAnswer = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($jsonAnswer))
        {
            throw new RuntimeException('incorrect answer format');
        }

        if (!array_key_exists('result', $jsonAnswer) || $jsonAnswer['result'] != 'ok')
        {
            $errorMessage = array_key_exists('message', $jsonAnswer)
                ? $jsonAnswer['message']
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
        return $itemData;
    }
}