<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
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
    private static
        $companiesTypes         =
            [
                'CUSTOMER'      => 'Покупатель',
                'SUPPLIER'      => 'Поставщик',
                'COMPETITOR'    => 'Конкурент',
                'PARTNER'       => 'Партенр',
                'OTHER'         => 'Другое'
            ],
        $companiesScopes        =
            [
                'IT'            => 'Информационные технологии',
                'TELECOM'       => 'Телекоммуникации и связь',
                'MANUFACTURING' => 'Производство',
                'BANKING'       => 'Банковские услуги',
                'CONSULTING'    => 'Консалтинг',
                'FINANCE'       => 'Финансы',
                'GOVERNMENT'    => 'Правительство',
                'DELIVERY'      => 'Доставка',
                'ENTERTAINMENT' => 'Развлечения',
                'NOTPROFIT'     => 'Не для получения прибыли',
                'OTHER'         => 'Другое'
            ];
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
            $companiesGetedDataFolder   = $this->getParticipantFolderByParameter('participants.contractingparties.companies.1c.receivedDataPath');
            $companiesGetedDataFile     = $this->getAnyFileInFolder($companiesGetedDataFolder);
            $data                       = (new XML)->readFromFile($companiesGetedDataFile);

            $this->removeFile($companiesGetedDataFile);
            return $data;
        }
        catch (UnderflowException $exception)
        {
            $logger->addNotice("$logMessagePrefix: no prepared provided data was found");
        }
        catch (ParseDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: extracted prepared provided data parsing error, $error");
            return [];
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: removing prepared extracted provided data file error, $error");
            return [];
        }

        try
        {
            $implodedProcessedDataFolder    = $this->getParticipantFolderByParameter('participants.contractingparties.companies.1c.implodedProcessedDataPath');
            $implodedReceivedDataFolder     = $this->getParticipantFolderByParameter('participants.contractingparties.companies.1c.implodedReceivedDataPath');
            $implodedReceivedDataFile       = $this->getAnyFileInFolder($implodedReceivedDataFolder);
            $implodedReceivedData           = (new XML)->readFromFile($implodedReceivedDataFile);
            $implodedReceivedData           = count($implodedReceivedData) > 0 ? array_values(array_pop($implodedReceivedData)) : [];
            $tablesData                     =
                [
                    'companies'     => [],
                    'contacts'      => [],
                    'requisites'    => [],
                    'addresses'     => []
                ];

            foreach ($implodedReceivedData as $item)
            {
                if (is_array($item))
                {
                    $companyData    = $this->extractCompanyData($item);
                    $contactsData   = $this->extractCompanyContactsData($item);
                    $requisitesData = $this->extractCompanyRequisitesData($item);
                    $addressData    = $this->extractCompanyAddressesData($item);

                    $tablesData['companies'][]  = $companyData;
                    $tablesData['contacts']     = array_merge($tablesData['contacts'],      $contactsData);
                    $tablesData['requisites']   = array_merge($tablesData['requisites'],    $requisitesData);
                    $tablesData['addresses']    = array_merge($tablesData['addresses'],     $addressData);
                }
            }

            foreach ($tablesData as $table => $data)
            {
                $receivedDataFolder = $this->getParticipantFolderByParameter("participants.contractingparties.$table.1c.receivedDataPath");
                $clearedData        = array_filter($data, function($value)
                    {
                        return is_array($value) && count($value) > 0;
                    });

                if (count($clearedData) > 0)
                {
                    $this->saveDataIntoFolder($receivedDataFolder, $clearedData);
                }
            }

            $this->replaceFileToFolder($implodedReceivedDataFile, $implodedProcessedDataFolder);
        }
        catch (UnderflowException $exception)
        {
            $logger->addNotice("$logMessagePrefix: no imploded provided data was found");
        }
        catch (RuntimeException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: imploded provided data file processing error, $error");
        }
        catch (ParseDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: imploded provided data parsing error, $error");
        }

        return [];
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
            $companiesReturnedDataFolder    = $this->getParticipantFolderByParameter('participants.contractingparties.companies.1c.returnedDataPath');
            $companiesReturnedDataFile      = $this->getNewFileInFolder($companiesReturnedDataFolder);

            (new XML)->writeToFile($companiesReturnedDataFile, $data);
        }
        catch (UnderflowException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: answer data file creating error, $error");
        }
        catch (WriteDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: answer data file writing error, $error");
        }

        try
        {
            $implodedReturnedDataFolder = $this->getParticipantFolderByParameter('participants.contractingparties.companies.1c.implodedReturnedDataPath');
            $implodedReturnedDataFile   = $this->getNewFileInFolder($implodedReturnedDataFolder);
            $tablesData                 =
                [
                    'companies'     => [],
                    'contacts'      => [],
                    'requisites'    => [],
                    'addresses'     => []
                ];
            $tablesFiles                = [];

            foreach (array_keys($tablesData) as $table)
            {
                $dataFolder = $this->getParticipantFolderByParameter("participants.contractingparties.$table.1c.returnedDataPath");
                $dataFiles  = $this->getAllFilesInFolder($dataFolder);

                foreach ($dataFiles as $file)
                {
                    $data               = (new XML)->readFromFile($file);
                    $tablesData[$table] = array_merge($tablesData[$table], $data);
                    $tablesFiles[]      = $file;
                }
            }

            $collectedData  = $this->combineCollectedData($tablesData);
            $implodedData   = $this->constructImplodedData($collectedData);

            if (count($implodedData) > 0)
            {
                (new XML)->writeToFile($implodedReturnedDataFile, $implodedData);
            }

            foreach ($tablesFiles as $file)
            {
                $this->removeFile($file);
            }

            return true;
        }
        catch (UnderflowException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: imploded answer data file creating error, $error");
            return false;
        }
        catch (ParseDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: returned participants data files parsing error, $error");
            return false;
        }
        catch (WriteDataException $exception)
        {
            $error = $exception->getMessage();
            $logger->addWarning("$logMessagePrefix: imploded answer data file writing error, $error");
            return false;
        }
        catch (RuntimeException $exception)
        {
            $logger->addWarning("$logMessagePrefix: returned participants data files removing error error");
            return false;
        }
    }
    /** **********************************************************************
     * get participant folder by parameter
     *
     * @param   string $param               parameter full name
     * @return  SplFileInfo                 folder
     ************************************************************************/
    private function getParticipantFolderByParameter(string $param) : SplFileInfo
    {
        $config             = Config::getInstance();
        $tempFolderParam    = $config->getParam('structure.tempFolder');
        $needFolderParam    = $config->getParam($param);
        $needFolderPath     =
            DOCUMENT_ROOT.DIRECTORY_SEPARATOR.
            $tempFolderParam.DIRECTORY_SEPARATOR.
            $needFolderParam;

        return new SplFileInfo($needFolderPath);
    }
    /** **********************************************************************
     * find any file in folder
     *
     * @param   SplFileInfo $folder         folder
     * @return  SplFileInfo                 file
     * @throws  UnderflowException          no file was found
     ************************************************************************/
    private function getAnyFileInFolder(SplFileInfo $folder) : SplFileInfo
    {
        try
        {
            $folderIterator = new RecursiveDirectoryIterator
            (
                $folder->getPathname(),
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
     * get new file in folder
     *
     * @param   SplFileInfo $folder         folder
     * @return  SplFileInfo                 new file
     * @throws  UnderflowException          no imploded answer data file was created
     ************************************************************************/
    private function getNewFileInFolder(SplFileInfo $folder) : SplFileInfo
    {
        $dataFileNameTemplate   = 'answer_data_{FILE_NUMBER}';
        $dataFileIndex          = 1;

        if (!$folder->isDir())
        {
            @mkdir($folder->getPathname(), 0777, true);
        }
        if (!$folder->isDir())
        {
            throw new UnderflowException;
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

        while (true)
        {
            $dataFileName   = str_replace('{FILE_NUMBER}', $dataFileIndex, $dataFileNameTemplate);
            $dataFilePath   = $folder->getPathname().DIRECTORY_SEPARATOR.$dataFileName.'.xml';
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
     * get data from all files in folder
     *
     * @param   SplFileInfo $folder         folder
     * @return  SplFileInfo[]               files
     ************************************************************************/
    private function getAllFilesInFolder(SplFileInfo $folder) : array
    {
        try
        {
            $result         = [];
            $folderIterator = new RecursiveDirectoryIterator
            (
                $folder->getPathname(),
                FilesystemIterator::SKIP_DOTS
            );

            while ($folderIterator->valid())
            {
                $file = $folderIterator->current();

                if ($file->isFile() && $file->isReadable())
                {
                    $result[] = $file;
                }

                $folderIterator->next();
            }

            return $result;
        }
        catch (UnexpectedValueException $exception)
        {
            return [];
        }
    }
    /** **********************************************************************
     * replace file to folder
     *
     * @param   SplFileInfo $file           file
     * @param   SplFileInfo $folder         folder
     * @return  void
     * @throws  RuntimeException            file replacing error
     ************************************************************************/
    private function replaceFileToFolder(SplFileInfo $file, SplFileInfo $folder) : void
    {
        $fileOldPath    = $file->getPathname();
        $fileNewPath    = $folder->getPathname().DIRECTORY_SEPARATOR.$file->getFilename();

        if (!$folder->isDir())
        {
            @mkdir($folder->getPathname(), 0777, true);
        }
        if (!$folder->isDir())
        {
            $folderPath = $folder->getPathname();
            throw new RuntimeException("unable to create folder \"$folderPath\"");
        }

        $renameResult = @rename($fileOldPath, $fileNewPath);
        if (!$renameResult)
        {
            throw new RuntimeException("moving file from \"$fileOldPath\" to \"$fileNewPath\" failed");
        }
    }
    /** **********************************************************************
     * remove file
     *
     * @param   SplFileInfo $file           file
     * @return  void
     * @throws  RuntimeException            file removing error
     ************************************************************************/
    private function removeFile(SplFileInfo $file) : void
    {
        if (!@unlink($file->getPathname()))
        {
            throw new RuntimeException;
        }
    }
    /** **********************************************************************
     * save data into folder
     *
     * @param   SplFileInfo $folder         folder
     * @param   array       $data           data
     * @return  void
     * @throws  RuntimeException            data saving error
     ************************************************************************/
    private function saveDataIntoFolder(SplFileInfo $folder, array $data) : void
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
     * extract company data
     *
     * @param   array $itemData             common item data
     * @return  array                       company data
     ************************************************************************/
    private function extractCompanyData(array $itemData) : array
    {
        $companyId      = (string)  ($itemData['Ид']                        ?? '');
        $markDelete     = (string)  ($itemData['ПометкаУдаления']           ?? '');
        $typeRaw        = (string)  ($itemData['Роль']                      ?? '');
        $scopeRaw       = (string)  ($itemData['ОсновнойВидДеятельности']   ?? '');
        $contactsRaw    = (array)   ($itemData['Контакты']                  ?? []);
        $type           = array_search($typeRaw,    self::$companiesTypes);
        $scope          = array_search($scopeRaw,   self::$companiesScopes);
        $type           = $type  !== false  ? $type     : '';
        $scope          = $scope !== false  ? $scope    : '';
        $contacts       = $this->getContactsData($contactsRaw);

        return
            [
                'Ид'                        => $companyId,
                'ИдКонтрагента1С'           => $companyId,
                'Активность'                => $markDelete != 'true',
                'Наименование'              => (string) ($itemData['Наименование']      ?? ''),
                'Роль'                      => $type,
                'ОсновнойВидДеятельности'   => $scope,
                'ОсновнойМенеджер'          => (string) ($itemData['ОсновнойМенеджер']  ?? ''),
                'Телефон'                   => $contacts['phone'],
                'ЭлектроннаяПочта'          => $contacts['email'],
                'Сайт'                      => $contacts['site'],
                'Комментарий'               => (string) ($itemData['Комментарий']       ?? ''),
                'ИНН'                       => (string) ($itemData['ИНН']               ?? ''),
                'КодПоОКПО'                 => (string) ($itemData['КодПоОКПО']         ?? '')
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
        $companyId  = (string)  ($itemData['Ид']            ?? '');
        $people     = (array)   ($itemData['Представители'] ?? []);
        $result     = [];

        foreach ($people as $peopleData)
        {
            $contacts   = $this->getContactsData((array) $peopleData['Контакты']);
            $result[]   =
                [
                    'Ид'                        => (string) ($peopleData['Ид']              ?? ''),
                    'ИдКонтрагента1С'           => $companyId,
                    'Фамилия'                   => (string) ($peopleData['Фамилия']         ?? ''),
                    'Имя'                       => (string) ($peopleData['Имя']             ?? ''),
                    'Отчество'                  => (string) ($peopleData['Отчество']        ?? ''),
                    'ДатаРождения'              => (string) ($peopleData['ДатаРождения']    ?? ''),
                    'Телефон'                   => $contacts['phone'],
                    'ЭлектроннаяПочта'          => $contacts['email'],
                    'Сайт'                      => $contacts['site'],
                    'Должность'                 => (string) ($peopleData['Должность']       ?? '')
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
        $companyId          = (string)  ($itemData['Ид']                    ?? '');
        $fullName           = (string)  ($itemData['ПолноеНаименование']    ?? '');
        $requisiteType      = (string)  ($itemData['ЮрФизЛицо']             ?? '');
        $people             = (array)   ($itemData['Представители']         ?? []);
        $companyRequisiteId = $this->getRequisiteId('company', $companyId, $requisiteType, 1);
        $result             = [];

        switch ($requisiteType)
        {
            case 'ЮрЛицо':
                $result[] =
                    [
                        'Ид'                    => $companyRequisiteId,
                        'ИдКонтрагента1С'       => $companyId,
                        'Тип'                   => $requisiteType,
                        'ИНН'                   => (string) ($itemData['ИНН']           ?? ''),
                        'КодПоОКПО'             => (string) ($itemData['КодПоОКПО']     ?? ''),
                        'Наименование'          => (string) ($itemData['Наименование']  ?? ''),
                        'ПолноеНаименование'    => $fullName,
                        'Директор'              => (string) ($itemData['Руководитель']  ?? '')
                    ];
                break;
            case 'ФизЛицо':
                $nameExplode        = explode(' ', $fullName);
                $lastName           = $nameExplode[0]       ?? '';
                $name               = $nameExplode[1]       ?? '';
                $secondName         = $nameExplode[2]       ?? '';
                $passportRaw        = (string) ($itemData['Документ'] ?? '');
                $passportExplode    = explode(' ', $passportRaw);
                $passportSeries     = $passportExplode[0]   ?? '';
                $passportNumber     = $passportExplode[1]   ?? '';
                $result[]           =
                    [
                        'Ид'                => $companyRequisiteId,
                        'ИдКонтрагента1С'   => $companyId,
                        'Тип'               => $requisiteType,
                        'Фамилия'           => $lastName,
                        'Имя'               => $name,
                        'Отчество'          => $secondName,
                        'Серия'             => $passportSeries,
                        'Номер'             => $passportNumber,
                        'КемВыдано'         => (string) ($itemData['КемВыдано']     ?? ''),
                        'ДатаВыдачи'        => (string) ($itemData['ДатаВыдачи']    ?? '')
                    ];
                break;
            default:
        }

        foreach ($people as $peopleData)
        {
            $peopleId       = (string)  ($peopleData['Ид']          ?? '');
            $peopleContacts = (array)   ($peopleData['Контакты']    ?? []);
            $peopleContacts = array_values($peopleContacts);

            foreach ($peopleContacts as $contactIndex => $contactData)
            {
                $contactType    = (string) ($contactData['Тип'] ?? '');
                $requisiteType  = '';

                switch ($contactType)
                {
                    case 'юридический адрес':
                        $requisiteType  = 'ЮрЛицо';
                        break;
                    case 'физический адрес':
                        $requisiteType  = 'ФизЛицо';
                        break;
                    default:
                }

                if (strlen($requisiteType) > 0)
                {
                    $requisiteId    = $this->getRequisiteId('contact', $peopleId, $requisiteType, $contactIndex + 1);
                    $result[]       =
                        [
                            'Ид'                        => $requisiteId,
                            'ИдКонтактаКонтрагента1С'   => $peopleId,
                            'Тип'                       => $requisiteType
                        ];
                }
            }
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
        $addresses  = $this->getAddressesData($itemData);
        $result     = [];

        foreach ($addresses as $addressData)
        {
            $addressExtractedData =
                [
                    'Страна'            => '',
                    'Почтовый индекс'   => '',
                    'Регион'            => '',
                    'Город'             => '',
                    'Улица'             => '',
                    'Квартира'          => ''
                ];

            foreach ($addressData as $itemValue)
            {
                if (is_array($itemValue))
                {
                    $index                          = (string)  ($itemValue['Тип']      ?? '');
                    $value                          = (string)  ($itemValue['Значение'] ?? '');
                    $addressExtractedData[$index]   = $value;
                }
            }

            $result[] =
                [
                    'ИдРеквизитаКонтрагента1С'  => (string) ($addressData['ИдРеквизитаКонтрагента1С']   ?? ''),
                    'Тип'                       => (string) ($addressData['Тип']                        ?? ''),
                    'Страна'                    => $addressExtractedData['Страна'],
                    'ПочтовыйИндекс'            => $addressExtractedData['Почтовый индекс'],
                    'Область'                   => $addressExtractedData['Регион'],
                    'Город'                     => $addressExtractedData['Город'],
                    'Улица'                     => $addressExtractedData['Улица'],
                    'Квартира'                  => $addressExtractedData['Квартира']
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * get item contacts data
     *
     * @param   array $data                 item data
     * @return  array                       contacts data
     * @example
     * [
     *      'phone' => [phone, phone, phone],
     *      'email' => [email, email, email],
     *      'site'  => [site, site, site]
     * ]
     ************************************************************************/
    private function getContactsData(array $data) : array
    {
        $result =
            [
                'phone' => [],
                'email' => [],
                'site'  => []
            ];

        foreach ($data as $itemData)
        {
            $value  = (string)  ($itemData['Представление'] ?? '');
            $type   = (string)  ($itemData['Тип']           ?? '');

            if (strlen($value) > 0)
            {
                switch ($type)
                {
                    case 'Телефон контрагента':
                        $result['phone'][] = "$value|MOBILE";
                        break;
                    case 'Факс':
                        $result['phone'][] = "$value|FAX";
                        break;
                    case 'Электронная почта':
                        $result['email'][] = $value;
                        break;
                    case 'Сайт компании':
                        $result['site'][] = $value;
                        break;
                    default:
                }
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get item addresses data
     *
     * @param   array $itemData             common item data
     * @return  array                       addresses data
     ************************************************************************/
    private function getAddressesData(array $itemData) : array
    {
        $companyId  = (string)  ($itemData['Ид']            ?? '');
        $contacts   = (array)   ($itemData['Контакты']      ?? []);
        $people     = (array)   ($itemData['Представители'] ?? []);
        $result     = [];

        foreach ($contacts as $contactData)
        {
            $contactTypeRaw = (string) ($contactData['Тип'] ?? '');
            $contactType    = '';
            $requisiteType  = '';

            switch ($contactTypeRaw)
            {
                case 'Юридический адрес контрагента':
                    $contactType    = 'Юридический адрес';
                    $requisiteType  = 'ЮрЛицо';
                    break;
                case 'Физический адрес контрагента':
                    $contactType    = 'Юридический адрес';
                    $requisiteType  = 'ФизЛицо';
                    break;
                default:
            }

            if (strlen($contactType) > 0 && strlen($contactType) > 0)
            {
                $contactData    = is_array($contactData) ? $contactData : [];
                $requisiteId    = $this->getRequisiteId('company', $companyId, $requisiteType, 1);

                $contactData['Тип']                         = $contactType;
                $contactData['ИдРеквизитаКонтрагента1С']    = $requisiteId;

                $result[$requisiteId] = $contactData;
            }
        }

        foreach ($people as $peopleData)
        {
            $peopleId       = (string)  ($peopleData['Тип']         ?? '');
            $peopleContacts = (array)   ($peopleData['Контакты']    ?? []);
            $peopleContacts = array_values($peopleContacts);

            foreach ($peopleContacts as $contactIndex => $contactData)
            {
                $contactTypeRaw = (string) ($contactData['Тип'] ?? '');
                $contactType    = '';
                $requisiteType  = '';

                switch ($contactTypeRaw)
                {
                    case 'юридический адрес':
                        $contactType    = 'Юридический адрес';
                        $requisiteType  = 'ЮрЛицо';
                        break;
                    case 'физический адрес':
                        $contactType    = 'Юридический адрес';
                        $requisiteType  = 'ФизЛицо';
                        break;
                    default:
                }

                if (strlen($contactType) > 0 && strlen($contactType) > 0)
                {
                    $contactData    = is_array($contactData) ? $contactData : [];
                    $requisiteId    = $this->getRequisiteId('contact', $peopleId, $requisiteType, $contactIndex + 1);

                    $contactData['Тип']                         = $contactType;
                    $contactData['ИдРеквизитаКонтрагента1С']    = $requisiteId;

                    $result[$requisiteId] = $contactData;
                }
            }
        }

        return array_values($result);
    }
    /** **********************************************************************
     * get requisite ID
     *
     * @param   string  $parentType         parent type
     * @param   string  $parentId           parent ID
     * @param   string  $requisiteType      requisite type
     * @param   int     $index              requisite index
     * @return  string                      requisite ID
     ************************************************************************/
    private function getRequisiteId(string $parentType, string $parentId, string $requisiteType, int $index) : string
    {
        return "$parentType-$parentId|$requisiteType-$index";
    }
    /** **********************************************************************
     * combine companies collected data
     *
     * @param   array $data                 companies collected data
     * @return  array                       companies combined data
     ************************************************************************/
    private function combineCollectedData(array $data) : array
    {
        $companiesData  = array_values($data['companies']);
        $contactsData   = [];
        $requisitesData = [];
        $addressesData  = [];

        foreach ($data['contacts'] as $contactData)
        {
            $companyId                  = (string) ($contactData['ИдКонтрагента1С'] ?? '');
            $contactsData[$companyId]   = $contactsData[$companyId] ?? [];
            $contactsData[$companyId][] = $contactData;
        }

        foreach ($data['requisites'] as $requisiteData)
        {
            $companyId                      = (string)  ($requisiteData['ИдКонтрагента1С']          ?? '');
            $contactId                      = (string)  ($requisiteData['ИдКонтактаКонтрагента1С']  ?? '');
            $parentId                       = strlen($contactId) > 0 ? $contactId : $companyId;
            $requisitesData[$parentId]      = $requisitesData[$parentId] ?? [];
            $requisitesData[$parentId][]    = $requisiteData;
        }

        foreach ($data['addresses'] as $addressData)
        {
            $companyId                  = (string)  ($addressData['ИдКонтрагента1С']            ?? '');
            $contactId                  = (string)  ($addressData['ИдКонтактаКонтрагента1С']    ?? '');
            $parentId                   = strlen($contactId) > 0 ? $contactId : $companyId;
            $addressesData[$parentId]   = $addressesData[$parentId] ?? [];
            $addressesData[$parentId][] = $addressData;
        }

        foreach ($companiesData as $companyIndex => $companyData)
        {
            $companyId              = $companyData['Ид'];
            $companyRequisites      = $requisitesData[$companyId] ?? [];
            $companyAddresses       = $addressesData[$companyId]  ?? [];
            $companyContacts        = $contactsData[$companyId]   ?? [];

            foreach ($companyContacts as $contactIndex => $contactData)
            {
                $contactId                                      = $contactData['Ид'];
                $companyContacts[$contactIndex]['Реквизиты']    = $requisitesData[$contactId]   ?? [];
                $companyContacts[$contactIndex]['Адреса']       = $addressesData[$contactId]    ?? [];
            }

            $companiesData[$companyIndex]['Реквизиты']  = $companyRequisites;
            $companiesData[$companyIndex]['Адреса']     = $companyAddresses;
            $companiesData[$companyIndex]['Контакты']   = $companyContacts;
        }

        return $companiesData;
    }
    /** **********************************************************************
     * construct companies imploded data
     *
     * @param   array $data                 companies collected data
     * @return  array                       companies imploded data
     ************************************************************************/
    private function constructImplodedData(array $data) : array
    {
        $result                 = [];
        $validateRequisitesData = function($value)
        {
            $indexes =
                [
                    'Тип',      'ПолноеНаименование',   'Директор',
                    'Фамилия',  'Имя',                  'Отчество'
                ];

            foreach ($indexes as $index)
            {
                $value[$index] = (string) ($value[$index] ?? '');
            }

            return $value;
        };

        foreach ($data as $itemData)
        {
            $type               = self::$companiesTypes[$itemData['Роль']]                      ??  '';
            $scope              = self::$companiesScopes[$itemData['ОсновнойВидДеятельности']]  ??  '';
            $communicationsData = $this->constructItemCommunicationsData($itemData);
            $contactsData       = $this->constructCompanyContactsData($itemData);
            $requisites         = array_map($validateRequisitesData, (array) $itemData['Реквизиты']);
            $entityType         = '';
            $fullName           = '';
            $directorName       = '';

            foreach ($requisites as $requisiteData)
            {
                switch ($requisiteData['Тип'])
                {
                    case 'UR':
                        $entityType     = 'ЮрЛицо';
                        $fullName       = $requisiteData['ПолноеНаименование'];
                        $directorName   = $requisiteData['Директор'];
                        break;
                    case 'FIZ':
                        $entityType     = 'ФизЛицо';
                        $fullNameParts  =
                            [
                                $requisiteData['Фамилия'],
                                $requisiteData['Имя'],
                                $requisiteData['Отчество']
                            ];
                        $fullName       = trim(implode(' ', $fullNameParts));
                        $directorName   = $requisiteData['Директор'];
                        break;
                    default:
                }
            }

            $result[] =
                [
                    'Ид'                        => $itemData['Ид'],
                    'ПометкаУдаления'           => $itemData['Активность'] ? 'false' : 'true',
                    'Наименование'              => $itemData['Наименование'],
                    'Роль'                      => $type,
                    'ОсновнойВидДеятельности'   => $scope,
                    'ОсновнойМенеджер'          => $itemData['ОсновнойМенеджер'],
                    'Комментарий'               => $itemData['Комментарий'],
                    'ИНН'                       => $itemData['ИНН'],
                    'КодПоОКПО'                 => $itemData['КодПоОКПО'],
                    'ПолноеНаименование'        => $fullName,
                    'ЮрФизЛицо'                 => $entityType,
                    'Руководитель'              => $directorName,
                    'Контакты'                  => $communicationsData,
                    'Представители'             => $contactsData
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * construct company communications data
     *
     * @param   array $data                 company item data
     * @return  array                       company item communications data
     ************************************************************************/
    private function constructItemCommunicationsData(array $data) : array
    {
        $phones     = $this->filterNotEmptyStrings((array) $data['Телефон']);
        $emails     = $this->filterNotEmptyStrings((array) $data['ЭлектроннаяПочта']);
        $sites      = $this->filterNotEmptyStrings((array) $data['Сайт']);
        $addresses  = $this->filterNotEmptyArrays((array) $data['Адреса']);
        $result     = [];

        foreach ($phones as $phone)
        {
            $valueExplode   = explode('|', $phone);
            $value          = $valueExplode[0]  ?? '';
            $type           = $valueExplode[1]  ?? '';

            switch ($type)
            {
                case 'FAX':
                    $result[] =
                        [
                            'Тип'           => 'Факс',
                            'Представление' => $value
                        ];
                    break;
                default:
                    $result[] =
                        [
                            'Тип'           => 'Телефон контрагента',
                            'Представление' => $value
                        ];
            }
        }
        foreach ($emails as $email)
        {
            $result[] =
                [
                    'Тип'           => 'Электронная почта',
                    'Представление' => $email
                ];
        }
        foreach ($sites as $site)
        {
            $result[] =
                [
                    'Тип'           => 'Сайт компании',
                    'Представление' => $site
                ];
        }
        foreach ($addresses as $addressData)
        {
            $result[] =
                [
                    'Тип' => (string) ($addressData['Тип'] ?? ''),
                    [
                        'Тип'       => 'Страна',
                        'Значение'  => (string) ($addressData['Страна']         ?? '')
                    ],
                    [
                        'Тип'       => 'Почтовый индекс',
                        'Значение'  => (string) ($addressData['ПочтовыйИндекс'] ?? '')
                    ],
                    [
                        'Тип'       => 'Регион',
                        'Значение'  => (string) ($addressData['Область']        ?? '')
                    ],
                    [
                        'Тип'       => 'Город',
                        'Значение'  => (string) ($addressData['Город']          ?? '')
                    ],
                    [
                        'Тип'       => 'Улица',
                        'Значение'  => (string) ($addressData['Улица']          ?? '')
                    ],
                    [
                        'Тип'       => 'Квартира',
                        'Значение'  => (string) ($addressData['Квартира']       ?? '')
                    ]
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * construct company contacts data
     *
     * @param   array $data                 company item data
     * @return  array                       company item contacts data
     ************************************************************************/
    private function constructCompanyContactsData(array $data) : array
    {
        $contacts   = $this->filterNotEmptyArrays((array) $data['Контакты']);
        $result     = [];

        foreach ($contacts as $contactData)
        {
            $result[] =
                [
                    'Отношение'     => 'Контактное лицо',
                    'Ид'            => (string) ($contactData['Ид']             ?? ''),
                    'Фамилия'       => (string) ($contactData['Фамилия']        ?? ''),
                    'Имя'           => (string) ($contactData['Имя']            ?? ''),
                    'Отчество'      => (string) ($contactData['Отчество']       ?? ''),
                    'ДатаРождения'  => (string) ($contactData['ДатаРождения']   ?? ''),
                    'Должность'     => (string) ($contactData['Должность']      ?? ''),
                    'Контакты'      => $this->constructItemCommunicationsData($contactData)
                ];
        }

        return $result;
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