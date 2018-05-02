<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    ReflectionClass,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempFilesCreator,
    UnitTests\Core\TempDBRecordsCreator,
    Main\Helpers\DB,
    Main\Exchange\Participants\Users1C;
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
                    ]
                ]
            ],
            [
                'dbItemName'    => 'TestParticipant3',
                'className'     => 'TestParticipant3',
                'fields'        => []
            ]
        ];
    /** @var TempFilesCreator */
    private static $tempFilesCreator        = null;
    /** @var TempDBRecordsCreator */
    private static $tempDBRecordsCreator    = null;
    /** **********************************************************************
     * construct
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$tempFilesCreator     = new TempFilesCreator;
        self::$tempDBRecordsCreator = new TempDBRecordsCreator;
        $systemFields               = self::getParticipantsSystemFields();

        foreach (self::$tempParticipants as $participant)
        {
            self::$tempFilesCreator->createTempClass(Users1C::class, $participant['className']);

            $tempParticipantId = self::$tempDBRecordsCreator->createTempRecord(self::$participantsTable,
            [
                'NAME' => $participant['dbItemName']
            ]);

            foreach ($participant['fields'] as $field)
            {
                self::$tempDBRecordsCreator->createTempRecord(self::$participantsFieldsTable,
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
        self::$tempFilesCreator->dropCreatedTempFiles();
        self::$tempDBRecordsCreator->dropTempChanges();
    }
    /** **********************************************************************
     * get participants system fields
     *
     * @return  array                       participants system fields array code => id
     ************************************************************************/
    private static function getParticipantsSystemFields() : array
    {
        $result = [];
        $table  = self::$fieldsTypesTable;

        $queryResult = DB::getInstance()->query("SELECT * FROM $table");
        while (!$queryResult->isEmpty())
        {
            $item = $queryResult->pop();
            $result[$item->get('CODE')] = $item->get('ID');
        }

        return $result;
    }
    /** **********************************************************************
     * check participant fields info
     *
     * @test
     ************************************************************************/
    public function providingFieldsInfo() : void
    {
        $systemParticipantReflection    = new ReflectionClass(Users1C::class);
        $systemParticipantNamespace     = $systemParticipantReflection->getNamespaceName();

        foreach (self::$tempParticipants as $participant)
        {
            $participantClassQualifiedName  = $systemParticipantNamespace.'\\'.$participant['className'];
            $fieldsQueue                    = call_user_func([new $participantClassQualifiedName, 'getFields']);
            $tempFields                     = [];
            $currentFields                  = [];

            foreach ($participant['fields'] as $field)
            {
                $tempFields[$field['NAME']] = $field;
            }

            foreach ($fieldsQueue->getKeys() as $key)
            {
                $field          = $fieldsQueue->get($key);
                $fieldName      = call_user_func([$field, 'getName']);
                $fieldRequired  = call_user_func([$field, 'isRequired']);
                $fieldType      = call_user_func([$field, 'getType']);

                $currentFields[$fieldName] =
                [
                    'NAME'      => $fieldName,
                    'TYPE'      => $fieldType,
                    'REQUIRED'  => $fieldRequired
                ];
            }

            self::assertEquals
            (
                $tempFields,
                $currentFields,
                'Expect get temp participant fields as temp created'
            );
        }
    }
}