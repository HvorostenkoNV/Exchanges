<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Throwable,
    UnderflowException,
    RuntimeException,
    UnexpectedValueException,
    Main\Helpers\MarkupData\Exceptions\ParseDataException,
    Main\Helpers\MarkupData\Exceptions\WriteDataException,
    FilesystemIterator,
    RecursiveDirectoryIterator,
    SplFileInfo,
    Main\Helpers\Logger,
    Main\Helpers\Config,
    Main\Helpers\MarkupData\XML;
/** ***********************************************************************************************
 * Application participant ContractingParties1C
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class ContractingParties1C extends AbstractParticipant
{
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  array                       data
     ************************************************************************/
    protected function readProvidedData() : array
    {
        $logger             = Logger::getInstance();
        $logMessagePrefix   = '1C contractingparties provided data reading';

        try
        {
            $providedImplodedDataFile = $this->getProvidedImplodedDataFile();
            $this->processingProvidedImplodedDataFile($providedImplodedDataFile);

            return [];
        }
        catch (UnderflowException $exception)
        {

        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addNotice("$logMessagePrefix: provided imploded data file processing error, $error");
            return [];
        }

        try
        {
            $preparedProvidedDataFile   = $this->getPreparedProvidedDataFile();
            $data                       = (new XML)->readFromFile($preparedProvidedDataFile);

            $this->removePreparedProvidedDataFile($preparedProvidedDataFile);
            return $data;
        }
        catch (UnderflowException $exception)
        {
            $logger->addNotice("$logMessagePrefix: no provided data file was found");
            return [];
        }
        catch (ParseDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: prepared provided data parsing error, $error");
            return [];
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: removing prepared provided data file error, $error");
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
        $logger             = Logger::getInstance();
        $logMessagePrefix   = '1C contractingparties providing data for delivery';

        try
        {
            $answerFile = $this->getAnswerDataFile();
            $fullData   = $this->constructImplodedDataForDelivery($data);

            (new XML)->writeToFile($answerFile, $fullData);
            return true;
        }
        catch (UnexpectedValueException $exception)
        {
            $error = $exception->getMessage();
            $logger->addNotice("$logMessagePrefix: constructing imploded data error, $error");
            return false;
        }
        catch (WriteDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: answer data writing error, $error");
            return false;
        }
        catch (UnderflowException $exception)
        {
            $logger->addNotice("$logMessagePrefix: answer file was not created");
            return false;
        }
    }
    /** **********************************************************************
     * get provided imploded data file
     *
     * @return  SplFileInfo                 provided imploded data file
     * @throws  UnderflowException          no provided imploded data file was found
     ************************************************************************/
    private function getProvidedImplodedDataFile() : SplFileInfo
    {
        $config                             = Config::getInstance();
        $tempFolderParam                    = $config->getParam('structure.tempFolder');
        $implodedReceivedDataFolderParam    = $config->getParam('participants.contractingparties.companies.1c.implodedReceivedDataPath');
        $implodedReceivedDataFolderPath     =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $implodedReceivedDataFolderParam;
        $implodedReceivedDataFolder         = new SplFileInfo($implodedReceivedDataFolderPath);

        if (!$implodedReceivedDataFolder->isDir())
        {
            @mkdir($implodedReceivedDataFolder->getPathname(), 0777, true);
        }

        try
        {
            $folderIterator = new RecursiveDirectoryIterator
            (
                $implodedReceivedDataFolder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );

            while ($folderIterator->valid())
            {
                $file = $folderIterator->current();

                if ($file->isFile() && $file->isReadable())
                {
                    return $file;
                }

                $folderIterator->next();
            }
        }
        catch (UnexpectedValueException $exception)
        {

        }

        throw new UnderflowException;
    }
    /** **********************************************************************
     * mark provided imploded XML data file as processed
     *
     * @param   SplFileInfo $file           provided imploded XML data file
     * @throws  RuntimeException            marking provided imploded data file error
     ************************************************************************/
    private function markProvidedImplodedDataFileAsProcessed(SplFileInfo $file) : void
    {
        $config                             = Config::getInstance();
        $tempFolderParam                    = $config->getParam('structure.tempFolder');
        $processedReceivedDataFolderParam   = $config->getParam('participants.contractingparties.companies.1c.implodedProcessedDataPath');
        $processedReceivedDataFolderPath    =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $processedReceivedDataFolderParam;
        $processedReceivedDataFolder        = new SplFileInfo($processedReceivedDataFolderPath);
        $processedDataFilePath              = $processedReceivedDataFolder->getPathname().DIRECTORY_SEPARATOR.$file->getFilename();
        $processedDataFile                  = new SplFileInfo($processedDataFilePath);

        if (!$processedReceivedDataFolder->isDir())
        {
            @mkdir($processedReceivedDataFolder->getPathname(), 0777, true);
        }
        if (!$processedReceivedDataFolder->isDir())
        {
            $folderPath = $processedReceivedDataFolder->getPathname();
            throw new RuntimeException("unable to create folder \"$folderPath\"");
        }

        try
        {
            rename($file->getPathname(), $processedDataFile->getPathname());
        }
        catch (Throwable $exception)
        {
            $movingFrom = $file->getPathname();
            $movingTo   = $processedDataFile->getPathname();

            throw new RuntimeException("moving file from \"$movingFrom\" to \"$movingTo\" failed");
        }
    }
    /** **********************************************************************
     * get prepared provided XML data file
     *
     * @return  SplFileInfo                 prepared provided XML data file
     * @throws  UnderflowException          no prepared provided data file was found
     ************************************************************************/
    private function getPreparedProvidedDataFile() : SplFileInfo
    {
        $config                     = Config::getInstance();
        $tempFolderParam            = $config->getParam('structure.tempFolder');
        $receivedDataFolderParam    = $config->getParam('participants.contractingparties.companies.1c.receivedDataPath');
        $receivedDataFolderPath     =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $receivedDataFolderParam;
        $receivedDataFolder         = new SplFileInfo($receivedDataFolderPath);

        if (!$receivedDataFolder->isDir())
        {
            @mkdir($receivedDataFolder->getPathname(), 0777, true);
        }

        try
        {
            $folderIterator = new RecursiveDirectoryIterator
            (
                $receivedDataFolder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );

            while ($folderIterator->valid())
            {
                $file = $folderIterator->current();

                if ($file->isFile() && $file->isReadable())
                {
                    return $file;
                }

                $folderIterator->next();
            }
        }
        catch (UnexpectedValueException $exception)
        {

        }

        throw new UnderflowException;
    }
    /** **********************************************************************
     * get answer XML data file
     *
     * @return  SplFileInfo                 answer XML data file
     * @throws  UnderflowException          no answer data file was created
     ************************************************************************/
    private function getAnswerDataFile() : SplFileInfo
    {
        $config                     = Config::getInstance();
        $tempFolderParam            = $config->getParam('structure.tempFolder');
        $returnedDataFolderParam    = $config->getParam('participants.contractingparties.companies.1c.returnedDataPath');
        $returnedDataFolderPath     =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $returnedDataFolderParam;
        $returnedDataFolder         = new SplFileInfo($returnedDataFolderPath);
        $dataFileNameTemplate       = 'answer_data_{FILE_NUMBER}';
        $dataFileIndex              = 1;

        if (!$returnedDataFolder->isDir())
        {
            @mkdir($returnedDataFolder->getPathname(), 0777, true);
        }
        if (!$returnedDataFolder->isDir())
        {
            throw new UnderflowException;
        }

        try
        {
            $filesIterator  = new FilesystemIterator
            (
                $returnedDataFolder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );
            $dataFileIndex += iterator_count($filesIterator);
        }
        catch (UnexpectedValueException $exception)
        {

        }

        while (true)
        {
            $dataFileName   = str_replace('{FILE_NUMBER}', $dataFileIndex, $dataFileNameTemplate);
            $dataFilePath   = $returnedDataFolder->getPathname().DIRECTORY_SEPARATOR.$dataFileName.'.xml';
            $dataFile       = new SplFileInfo($dataFilePath);

            if (!$dataFile->isFile())
            {
                return $dataFile;
            }

            $dataFileIndex++;
        }

        throw new UnderflowException;
    }
    /** **********************************************************************
     * remove prepared provided XML data file
     *
     * @param   SplFileInfo $file           prepared provided XML data file
     * @throws  RuntimeException            removing prepared provided data file error
     ************************************************************************/
    private function removePreparedProvidedDataFile(SplFileInfo $file) : void
    {
        $deletingSuccess = false;

        try
        {
            $deletingSuccess = unlink($file->getPathname());
        }
        catch (Throwable $exception)
        {

        }

        if (!$deletingSuccess)
        {
            throw new RuntimeException;
        }
    }
    /** **********************************************************************
     * processing provided imploded XML data file
     *
     * @param   SplFileInfo $file           provided imploded XML data file
     * @throws  RuntimeException            processing provided imploded data file error
     ************************************************************************/
    private function processingProvidedImplodedDataFile(SplFileInfo $file) : void
    {
        try
        {
            $xmlData                    = (new XML)->readFromFile($file);
            $xmlData                    = array_values(array_pop($xmlData));
            $config                     = Config::getInstance();
            $tempFolderParam            = $config->getParam('structure.tempFolder');
            $companiesDataFolderParam   = $config->getParam('participants.contractingparties.companies.1c.receivedDataPath');
            $contactsDataFolderParam    = $config->getParam('participants.contractingparties.contacts.1c.receivedDataPath');
            $requisitesDataFolderParam  = $config->getParam('participants.contractingparties.requisites.1c.receivedDataPath');
            $addressesDataFolderParam   = $config->getParam('participants.contractingparties.addresses.1c.receivedDataPath');
            $tempFolderPath             = DOCUMENT_ROOT.DIRECTORY_SEPARATOR.$tempFolderParam;
            $companiesDataFolder        = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.$companiesDataFolderParam);
            $contactsDataFolder         = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.$contactsDataFolderParam);
            $requisitesDataFolder       = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.$requisitesDataFolderParam);
            $addressesDataFolder        = new SplFileInfo($tempFolderPath.DIRECTORY_SEPARATOR.$addressesDataFolderParam);
            $companiesData              = [];
            $contactsData               = [];
            $requisitesData             = [];
            $addressData                = [];

            foreach ($xmlData as $item)
            {
                if (is_array($item))
                {
                    $item                   = $this->validateImplodedItemData($item);
                    $companyData            = $this->extractCompanyData($item);
                    $companyContactsData    = $this->extractCompanyContactsData($item);
                    $companyRequisitesData  = $this->extractCompanyRequisitesData($item);
                    $companyAddressData     = $this->extractCompanyAddressesData($item);

                    if (count($companyData) > 0)
                    {
                        $companiesData[] = $companyData;
                    }
                    if (count($companyContactsData) > 0)
                    {
                        $contactsData = array_merge($contactsData, $companyContactsData);
                    }
                    if (count($companyRequisitesData) > 0)
                    {
                        $requisitesData = array_merge($requisitesData, $companyRequisitesData);
                    }
                    if (count($companyAddressData) > 0)
                    {
                        $addressData = array_merge($addressData, $companyAddressData);
                    }
                }
            }

            $this->saveExtractedData($companiesDataFolder, $companiesData);
            $this->saveExtractedData($contactsDataFolder, $contactsData);
            $this->saveExtractedData($requisitesDataFolder, $requisitesData);
            $this->saveExtractedData($addressesDataFolder, $addressData);
            $this->markProvidedImplodedDataFileAsProcessed($file);
        }
        catch (ParseDataException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * save extracted data
     *
     * @param   SplFileInfo $folder         folder for saving data file
     * @param   array       $data           extracted data
     * @throws  RuntimeException            saving extracted data error
     ************************************************************************/
    private function saveExtractedData(SplFileInfo $folder, array $data) : void
    {
        $dataFileNameTemplate   = 'data_{FILE_NUMBER}';
        $dataFileIndex          = 1;
        $dataFile               = null;

        if (!$folder->isDir())
        {
            @mkdir($folder->getPathname(), 0777, true);
        }
        if (!$folder->isDir())
        {
            $folderPath = $folder->getPathname();
            throw new RuntimeException("unable to create folder \"$folderPath\" for saving companies exploded data");
        }

        try
        {
            $filesIterator  = new FilesystemIterator
            (
                $folder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );
            $dataFileIndex += iterator_count($filesIterator);
        }
        catch (UnexpectedValueException $exception)
        {

        }

        while (!$dataFile)
        {
            $dataFileName   = str_replace('{FILE_NUMBER}', $dataFileIndex, $dataFileNameTemplate);
            $dataFilePath   = $folder->getPathname().DIRECTORY_SEPARATOR.$dataFileName.'.xml';
            $dataFile       = new SplFileInfo($dataFilePath);

            if (!$dataFile->isFile())
            {
                break;
            }

            $dataFileIndex++;
        }

        try
        {
            (new XML)->writeToFile($dataFile, $data);
        }
        catch (WriteDataException $exception)
        {
            $error      = $exception->getMessage();
            $filePath   = $dataFile->getPathname();

            throw new RuntimeException("exploded data file \"$filePath\" creating error, $error");
        }
    }
    /** **********************************************************************
     * construct imploded data for delivery
     *
     * @param   array $companyData          company data
     * @return  array                       imploded data for delivery
     * @throws  UnexpectedValueException    saving extracted data error
     ************************************************************************/
    private function constructImplodedDataForDelivery(array $companyData) : array
    {
        return [];
    }
    /** **********************************************************************
     * validate imploded item data
     *
     * @param   array $itemData             imploded item data
     * @return  array                       validated imploded item data
     ************************************************************************/
    private function validateImplodedItemData(array $itemData) : array
    {
        $workFieldsStrings          =
            [
                'Ид', 'ПометкаУдаления', 'Наименование',
                'Роль', 'ИНН', 'КодПоОКПО',
                'ЮрФизЛицо', 'ПолноеНаименование', 'Руководитель',
                'ОсновнойВидДеятельности', 'ОсновнойМенеджер', 'Комментарий',
                'Документ', 'КемВыдано' , 'ДатаВыдачи'
            ];
        $workFieldsArrays           =
            [
                'Контакты', 'Представители'
            ];
        $contactsWorkFieldsStrings  =
            [
                'Тип', 'Представление'
            ];
        $peopleWorkFieldsStrings    =
            [
                'Ид',
                'Фамилия', 'Имя', 'Отчество',
                'ДатаРождения', 'Должность'
            ];

        foreach ($workFieldsStrings as $field)
        {
            if (!array_key_exists($field, $itemData))
            {
                $itemData[$field] = '';
            }
        }
        foreach ($workFieldsArrays as $field)
        {
            if (!array_key_exists($field, $itemData) || !is_array($itemData[$field]))
            {
                $itemData[$field] = [];
            }
        }

        foreach ($itemData['Контакты'] as $index => $contactData)
        {
            foreach ($contactsWorkFieldsStrings as $field)
            {
                if (!array_key_exists($field, $contactData))
                {
                    $itemData['Контакты'][$index][$field] = '';
                }
            }
        }

        foreach ($itemData['Представители'] as $index => $peopleData)
        {
            foreach ($peopleWorkFieldsStrings as $field)
            {
                if (!array_key_exists($field, $peopleData))
                {
                    $itemData['Представители'][$index][$field] = '';
                }
            }
            $itemData['Представители'][$index]['Контакты'] =
                array_key_exists('Контакты', $peopleData) &&
                is_array($peopleData['Контакты'])
                    ? $peopleData['Контакты']
                    : [];

            foreach ($itemData['Представители'][$index]['Контакты'] as $subIndex => $contactData)
            {
                foreach ($contactsWorkFieldsStrings as $field)
                {
                    if (!array_key_exists($field, $contactData))
                    {
                        $itemData['Представители'][$index]['Контакты'][$subIndex][$field] = '';
                    }
                }
            }
        }

        return $itemData;
    }
    /** **********************************************************************
     * extract company data
     *
     * @param   array $itemData             common item data
     * @return  array                       company data
     ************************************************************************/
    private function extractCompanyData(array $itemData) : array
    {
        $companyTypes           =
            [
                'Покупатель'    => 'CUSTOMER',
                'Поставщик'     => 'PROVIDER',
                'Конкурент'     => 'COMPETITOR',
                'Партенр'       => 'PARTNER'
            ];
        $companyDefaultType     = 'OTHER';
        $companyScopes          =
            [
                'Информационные технологии' => 'INFORMATION_TECHNOLOGY',
                'Производство'              => 'PRODUCTION',
                'Финансы'                   => 'FINANCE'
            ];
        $companyDefaultScope    = 'OTHER';
        $companyPhones          = [];
        $companyEmails          = [];
        $companySites           = [];

        foreach ($itemData['Контакты'] as $contactData)
        {
            $contactValue = $contactData['Представление'];
            switch ($contactData['Тип'])
            {
                case 'Телефон контрагента':
                    $companyPhones[] = "$contactValue|Мобильный";
                    break;
                case 'Факс':
                    $companyPhones[] = "$contactValue|Факс";
                    break;
                case 'Электронная почта':
                    $companyEmails[] = $contactValue;
                    break;
                case 'Сайт компании':
                    $companySites[] = $contactValue;
                    break;
                default:
            }
        }

        return
            [
                'Ид'                        => $itemData['Ид'],
                'ИдКонтрагента1С'           => $itemData['Ид'],
                'Активность'                => $itemData['ПометкаУдаления'] == 'false' ? 'Y' : 'N',
                'Наименование'              => $itemData['Наименование'],
                'Роль'                      => array_key_exists($itemData['Роль'], $companyTypes)
                    ? $companyTypes[$itemData['Роль']]
                    : $companyDefaultType,
                'ОсновнойВидДеятельности'   => array_key_exists($itemData['ОсновнойВидДеятельности'], $companyScopes)
                    ? $companyScopes[$itemData['Роль']]
                    : $companyDefaultScope,
                'ОсновнойМенеджер'          => $itemData['ОсновнойМенеджер'],
                'Телефон'                   => $companyPhones,
                'ЭлектроннаяПочта'          => $companyEmails,
                'Сайт'                      => $companySites,
                'Комментарий'               => $itemData['Комментарий'],
                'ИНН'                       => $itemData['ИНН'],
                'КодПоОКПО'                 => $itemData['КодПоОКПО']
            ];
    }
    /** **********************************************************************
     * extract company contacts data
     *
     * @param   array $itemData             common item data
     * @return  array                       company contacts data
     ************************************************************************/
    private function extractCompanyContactsData(array $itemData) : array
    {
        $result = [];

        foreach ($itemData['Представители'] as $peopleData)
        {
            $peoplePhones   = [];
            $peopleEmails   = [];
            $peopleSites    = [];

            foreach ($peopleData['Контакты'] as $peopleContactData)
            {
                $value = $peopleContactData['Представление'];
                switch ($peopleContactData['Тип'])
                {
                    case 'Телефон контрагента':
                        $peoplePhones[] = "$value|Телефон";
                        break;
                    case 'Факс':
                        $peoplePhones[] = "$value|Факс";
                        break;
                    case 'e-mail':
                        $peopleEmails[] = $value;
                        break;
                    case 'Сайт компании':
                        $peopleSites[] = $value;
                        break;
                    default:
                }
            }

            $result[] =
                [
                    'Ид'                        => $peopleData['Ид'],
                    'ИдКонтактаКонтрагента1С'   => $peopleData['Ид'],
                    'ИдКонтрагента1С'           => $itemData['Ид'],
                    'Фамилия'                   => $peopleData['Фамилия'],
                    'Имя'                       => $peopleData['Имя'],
                    'Отчество'                  => $peopleData['Отчество'],
                    'ДатаРождения'              => $peopleData['ДатаРождения'],
                    'Телефон'                   => $peoplePhones,
                    'ЭлектроннаяПочта'          => $peopleEmails,
                    'Сайт'                      => $peopleSites,
                    'Должность'                 => $peopleData['Должность']
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * extract company requisites data
     *
     * @param   array $itemData             common item data
     * @return  array                       company requisites data
     ************************************************************************/
    private function extractCompanyRequisitesData(array $itemData) : array
    {
        $companyId  = $itemData['Ид'];
        $result     = [];

        switch ($itemData['ЮрФизЛицо'])
        {
            case 'ЮрЛицо':
                $requisiteId    = "company-$companyId|UR-1";
                $result[]       =
                    [
                        'Ид'                        => $requisiteId,
                        'ИдРеквизитаКонтрагента1С'  => $requisiteId,
                        'ИдКонтрагента1С'           => $companyId,
                        'Тип'                       => 'UR',
                        'ИНН'                       => $itemData['ИНН'],
                        'КодПоОКПО'                 => $itemData['КодПоОКПО'],
                        'Наименование'              => $itemData['Наименование'],
                        'ПолноеНаименование'        => $itemData['ПолноеНаименование'],
                        'Директор'                  => $itemData['Руководитель']
                    ];
                break;
            case 'ФизЛицо':
                $requisiteId        = "company-$companyId|FIZ-1";
                $nameExplode        = explode(' ', $itemData['ПолноеНаименование']);
                $passportExplode    = explode(' ', $itemData['Документ']);
                $result[]           =
                    [
                        'Ид'                        => $requisiteId,
                        'ИдРеквизитаКонтрагента1С'  => $requisiteId,
                        'ИдКонтрагента1С'           => $companyId,
                        'Тип'                       => 'FIZ',
                        'Фамилия'                   => $nameExplode[0],
                        'Имя'                       => $nameExplode[1],
                        'Отчество'                  => $nameExplode[2],
                        'Серия'                     => $passportExplode[0],
                        'Номер'                     => $passportExplode[1],
                        'КемВыдано'                 => $itemData['КемВыдано'],
                        'ДатаВыдачи'                => $itemData['ДатаВыдачи']
                    ];
                break;
            default:
        }

        return $result;
    }
    /** **********************************************************************
     * extract company addresses data
     *
     * @param   array $itemData             common item data
     * @return  array                       company addresses data
     ************************************************************************/
    private function extractCompanyAddressesData(array $itemData) : array
    {
        $companyId              = $itemData['Ид'];
        $companyAddressTypes    =
            [
                'Юридический адрес контрагента' => 'UR',
                'Физический адрес контрагента'  => 'FIZ',
            ];
        $addressFields          =
            [
                'Страна'            => 'Страна',
                'Почтовый индекс'   => 'ПочтовыйИндекс',
                'Регион'            => 'Область',
                'Город'             => 'Город',
                'Улица'             => 'Улица',
                'Квартира'          => 'Квартира'
            ];
        $addressData            =
            [
                'company'   => [],
                'people'    => []
            ];
        $result                 = [];

        foreach ($itemData['Контакты'] as $contactData)
        {
            if (array_key_exists($contactData['Тип'], $companyAddressTypes))
            {
                if (!array_key_exists($companyId, $addressData['company']))
                {
                    $addressData['company'][$companyId] = [];
                }

                $addressData['company'][$companyId][] = $contactData;
            }
        }

        foreach ($itemData['Представители'] as $index => $peopleData)
        {
            foreach ($peopleData['Контакты'] as $contactData)
            {
                if (array_key_exists($contactData['Тип'], $companyAddressTypes))
                {
                    if (!array_key_exists($peopleData['Ид'], $addressData['company']))
                    {
                        $addressData['people'][$peopleData['Ид']] = [];
                    }

                    $addressData['people'][$peopleData['Ид']][] = $contactData;
                }
            }
        }

        foreach ($addressData as $parentType => $addressTypeData)
        {
            foreach ($addressTypeData as $parentId => $parentAddresses)
            {
                foreach ($parentAddresses as $itemIndex => $itemData)
                {
                    $addressIndex   = $itemIndex + 1;
                    $addressType    = $companyAddressTypes[$itemData['Тип']];
                    $addressId      = "$parentType-$parentId|$addressType-$addressIndex";
                    $addressData    =
                        [
                            'Ид'                        => $addressId,
                            'ИдАдресаКонтрагента1С'     => $addressId,
                            'ИдКонтрагента1С'           => $companyId,
                            'ИдКонтактаКонтрагента1С'   => '',
                            'Тип'                       => $addressType
                        ];

                    foreach ($itemData as $index => $value)
                    {
                        if
                        (
                            is_array($value)                                &&
                            array_key_exists('Тип', $value)                 &&
                            array_key_exists($value['Тип'], $addressFields) &&
                            array_key_exists('Значение', $value)
                        )
                        {
                            $addressData[$addressFields[$value['Тип']]] = $value['Значение'];
                        }
                    }

                    $result[] = $addressData;
                }
            }
        }

        return $result;
    }
}