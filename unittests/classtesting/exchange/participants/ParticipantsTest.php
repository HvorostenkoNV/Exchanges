<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    ReflectionClass,
    SplFileInfo,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempFilesGenerator,
    UnitTests\Core\TempDBRecordsGenerator,
    Main\Helpers\DB,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Participant classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ParticipantsTest extends ExchangeTestCase
{
    private static
        $participantsTable          = 'participants',
        $participantsFieldsTable    = 'participants_fields',
        $fieldsTypesTable           = 'participants_fields_types',
        $tempParticipants           =
        [
            [
                'dbItemName'    => 'TestParticipant1',
                'className'     => 'TestParticipant1',
                'fields'        =>
                [
                    [
                        'NAME'      => 'name',
                        'TYPE'      => 'string',
                        'REQUIRED'  => true
                    ],
                    [
                        'NAME'      => 'code',
                        'TYPE'      => 'string',
                        'REQUIRED'  => true
                    ],
                    [
                        'NAME'      => 'important',
                        'TYPE'      => 'boolean',
                        'REQUIRED'  => false
                    ],
                    [
                        'NAME'      => 'someStringsValues',
                        'TYPE'      => 'array-of-strings',
                        'REQUIRED'  => true
                    ],
                    [
                        'NAME'      => 'someBooleansValues',
                        'TYPE'      => 'array-of-booleans',
                        'REQUIRED'  => true
                    ]
                ]
            ],
            [
                'dbItemName'    => 'TestParticipant2',
                'className'     => 'TestParticipant2',
                'fields'        =>
                [
                    [
                        'NAME'      => 'name',
                        'TYPE'      => 'string',
                        'REQUIRED'  => false
                    ],
                    [
                        'NAME'      => 'code',
                        'TYPE'      => 'string',
                        'REQUIRED'  => false
                    ],
                    [
                        'NAME'      => 'someIntegersValues',
                        'TYPE'      => 'array-of-numbers',
                        'REQUIRED'  => true
                    ]
                ]
            ],
            [
                'dbItemName'    => 'TestParticipant3',
                'className'     => 'TestParticipant3',
                'fields'        => []
            ]
        ];
    /** @var TempFilesGenerator */
    private static $tempFilesGenerator      = null;
    /** @var TempDBRecordsGenerator */
    private static $tempDBRecordsGenerator  = null;
    /** **********************************************************************
     * construct
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $systemFields           = self::getParticipantsSystemFields();
        $tempFilesGenerator     = new TempFilesGenerator;
        $tempDBRecordsGenerator = new TempDBRecordsGenerator;
        $participantClassName   = ParticipantForUnitTest::class;

        self::$tempFilesGenerator       = $tempFilesGenerator;
        self::$tempDBRecordsGenerator   = $tempDBRecordsGenerator;

        foreach (self::$tempParticipants as $participant)
        {
            $tempFilesGenerator->createTempClass($participantClassName, $participant['className']);

            $tempParticipantId = $tempDBRecordsGenerator->createTempRecord(self::$participantsTable,
            [
                'NAME' => $participant['dbItemName']
            ]);

            foreach ($participant['fields'] as $field)
            {
                $tempDBRecordsGenerator->createTempRecord(self::$participantsFieldsTable,
                [
                    'NAME'          => $field['NAME'],
                    'TYPE'          => $systemFields[$field['TYPE']],
                    'IS_REQUIRED'   => $field['REQUIRED'] ? 'Y' : 'N',
                    'PARTICIPANT'   => $tempParticipantId
                ]);
            }
        }
    }
    /** **********************************************************************
     * destruct
     ************************************************************************/
    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();
        self::$tempFilesGenerator->dropCreatedTempData();
        self::$tempDBRecordsGenerator->dropTempChanges();
    }
    /** **********************************************************************
     * get participants system fields
     *
     * @return  array                       participants system fields array code => id
     * @throws
     ************************************************************************/
    private static function getParticipantsSystemFields() : array
    {
        $result         = [];
        $table          = self::$fieldsTypesTable;
        $queryResult    = DB::getInstance()->query("SELECT * FROM $table");

        while (!$queryResult->isEmpty())
        {
            $item = $queryResult->pop();
            $result[$item->get('CODE')] = $item->get('ID');
        }

        return $result;
    }
    /** **********************************************************************
     * check participant provides fields info
     *
     * @test
     * @throws
     ************************************************************************/
    public function providingFieldsInfo() : void
    {
        foreach (self::$tempParticipants as $participant)
        {
            $participantObject  = $this->createParticipantObject($participant['className']);
            $participantFields  = $participantObject->getFields();
            $tempFields         = [];
            $currentFields      = [];

            foreach ($participant['fields'] as $field)
            {
                $tempFields[$field['NAME']] = $field;
            }

            while ($participantFields->valid())
            {
                $field = $participantFields->current();

                $currentFields[$field->getParam('name')] =
                [
                    'NAME'      => $field->getParam('name'),
                    'TYPE'      => $field->getParam('type'),
                    'REQUIRED'  => $field->getParam('required')
                ];

                $participantFields->next();
            }

            self::assertEquals
            (
                $tempFields,
                $currentFields,
                'Expect get participant fields as temp created'
            );
        }
    }
    /** **********************************************************************
     * check participant getting provided data process
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingProvidedData() : void
    {
        foreach (self::$tempParticipants as $participant)
        {
            $participantObject          = $this->createParticipantObject($participant['className']);
            $participantProvidedData    = [];
            $tempData                   = $this->createParticipantTempData($participant['fields']);
            $tempXml                    = self::$tempFilesGenerator->createTempXml($tempData);

            $participantObject->{'tempXmlFromUnitTest'} = $tempXml;
            $providedData = $participantObject->getProvidedData();

            while (!$providedData->isEmpty())
            {
                $item       = $providedData->pop();
                $itemData   = [];

                foreach ($item->getKeys() as $field)
                {
                    $fieldName              = $field->getParam('name');
                    $value                  = $item->get($field);
                    $itemData[$fieldName]   = $value;
                }

                $participantProvidedData[] = $itemData;
            }

            self::assertEquals
            (
                $tempData,
                $participantProvidedData,
                'Expect get same data as temp created'
            );
        }
    }
    /** **********************************************************************
     * check incorrect participant providing provided data process
     *
     * @test
     * @depends gettingProvidedData
     * @throws
     ************************************************************************/
    public function incorrectGettingProvidedData() : void
    {
        foreach (self::$tempParticipants as $participant)
        {
            $participantObject = $this->createParticipantObject($participant['className']);
            $participantObject->{'tempXmlFromUnitTest'} = new SplFileInfo('someIncorrectFilePath');
            $providedData = $participantObject->getProvidedData();

            self::assertEquals
            (
                0,
                $providedData->count(),
                'Expect get empty provided data with reading incorrect xml file'
            );
        }
    }
    /** **********************************************************************
     * check participant providing data process
     *
     * @test
     * @depends gettingProvidedData
     * @throws
     ************************************************************************/
    public function providingData() : void
    {
        foreach (self::$tempParticipants as $participant)
        {
            $participantObject  = $this->createParticipantObject($participant['className']);
            $tempData           = $this->createParticipantTempData($participant['fields']);
            $receivedXml        = self::$tempFilesGenerator->createTempXml($tempData);
            $deliveredXml       = self::$tempFilesGenerator->createTempXml([['test' => 'test']]);

            $participantObject->{'tempXmlFromUnitTest'}     = $receivedXml;
            $participantObject->{'createdTempXmlAnswer'}    = $deliveredXml;

            $providedData       = $participantObject->getProvidedData();
            $dataForDelivery    = new DataForDelivery;

            while (!$providedData->isEmpty())
            {
                $dataForDelivery->push($providedData->pop());
            }
            $participantObject->deliveryData($dataForDelivery);

            $receivedXmlContent     = $receivedXml->openFile('r')->fread($receivedXml->getSize());
            $deliveredXmlContent    = $deliveredXml->openFile('r')->fread($deliveredXml->getSize());

            self::assertEquals
            (
                $receivedXmlContent,
                $deliveredXmlContent,
                'Delivered xml expect to be same as received'
            );
        }
    }
    /** **********************************************************************
     * create participant object by name
     *
     * @param   string  $name               participant short name
     * @return  Participant                 participant
     ************************************************************************/
    private function createParticipantObject(string $name) : Participant
    {
        $systemParticipantReflection    = new ReflectionClass(ParticipantForUnitTest::class);
        $systemParticipantNamespace     = $systemParticipantReflection->getNamespaceName();
        $participantClassName           = $systemParticipantNamespace.'\\'.$name;

        return new $participantClassName;
    }
    /** **********************************************************************
     * check participant provides provided data
     *
     * @param   array   $fields             participant fields info
     * @return  array                       participant temp data
     * @throws
     ************************************************************************/
    private function createParticipantTempData(array $fields) : array
    {
        $result     = [];
        $itemsCount = rand(2, 10);

        if (count($fields) <= 0)
        {
            return $result;
        }

        for ($index = 1; $index <= $itemsCount; $index++)
        {
            $item = [];

            foreach ($fields as $field)
            {
                if ($field['REQUIRED'] || rand(0, 1) === 0)
                {
                    $item[$field['NAME']] = FieldsTypesManager::getField($field['TYPE'])->getRandomValue();
                }
            }

            $result[] = $item;
        }

        return $result;
    }
}