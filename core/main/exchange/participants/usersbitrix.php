<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    RuntimeException,
    Main\Helpers\Logger,
    Main\Helpers\Config;
/** ***********************************************************************************************
 * Application participant UsersBitrix
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class UsersBitrix extends AbstractParticipant
{
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  array                       data
     ************************************************************************/
    protected function readProvidedData() : array
    {
        try
        {
            $config         = Config::getInstance();
            $requestUrl     = $config->getParam('participants.usersbitrix.exchangeFileUrl');
            $queryParams    =
                [
                    'login'     => $config->getParam('participants.usersbitrix.userLogin'),
                    'password'  => $config->getParam('participants.usersbitrix.userLogin'),
                    'type'      => 'usersExport'
                ];
            $requestFullUrl = $requestUrl.'?'.http_build_query($queryParams);
            $data           = $this->getBitrixProvidedData($requestFullUrl);

            foreach ($data as $index => $item)
            {
                $data[$index] = $this->convertProvidedItemData($item);
            }

            return $data;
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            Logger::getInstance()->addWarning("Bitrix provided data reading: $error");
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
        try
        {
            foreach ($data as $index => $item)
            {
                $data[$index] = $this->convertItemDataForDelivery($item);
            }

            $config         = Config::getInstance();
            $requestUrl     = $config->getParam('participants.usersbitrix.exchangeFileUrl');
            $queryParams    =
                [
                    'login'     => $config->getParam('participants.usersbitrix.userLogin'),
                    'password'  => $config->getParam('participants.usersbitrix.userLogin'),
                    'type'      => 'usersImport',
                    'data'      => $data
                ];
            $streamContext  = stream_context_create
            ([
                'http' =>
                    [
                        'header'    => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'    => 'POST',
                        'content'   => http_build_query($queryParams)
                    ]
            ]);

            $this->postBitrixDataForDelivery($requestUrl, $streamContext);
            return true;
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            Logger::getInstance()->addWarning("Bitrix data for delivery writing: $error");
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
                ? 'caught error answer, "'.$jsonAnswer['message'].'"'
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
     * @param   resource    $streamContext  stream context
     * @throws  RuntimeException            post data error
     ************************************************************************/
    private function postBitrixDataForDelivery(string $requestUrl, $streamContext) : void
    {
        if (!is_resource($streamContext))
        {
            throw new RuntimeException('no stream context geted');
        }

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
                ? 'caught error answer, "'.$jsonAnswer['message'].'"'
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
        $itemData['ACTIVE'] = array_key_exists('ACTIVE', $itemData) && $itemData['ACTIVE'] == 'Y';

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
        $itemData['ACTIVE'] =
            array_key_exists('ACTIVE', $itemData) &&
            $itemData['ACTIVE'] === true
                ? 'Y'
                : 'N';

        return $itemData;
    }
}