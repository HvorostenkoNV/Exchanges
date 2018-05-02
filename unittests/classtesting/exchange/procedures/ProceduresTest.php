<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    ReflectionClass,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempFilesCreator,
    UnitTests\Core\TempDBRecordsCreator,
    Main\Exchange\Procedures\UsersExchange,
    Main\Exchange\Participants\Users1C;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Procedure classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProceduresTest extends ExchangeTestCase
{
    private static
        $proceduresTable    = 'procedures',
        $participantsTable  = 'participants',
        $linkTable          = 'procedures_participants',
        $tempProcedures     =
        [
            [
                'dbItemName'    => 'TestProcedure1',
                'className'     => 'TestProcedure1',
                'participants'  =>
                [
                    [
                        'dbItemName'    => 'TestProcedureParticipant1',
                        'className'     => 'TestProcedureParticipant1'
                    ],
                    [
                        'dbItemName'    => 'TestProcedureParticipant2',
                        'className'     => 'TestProcedureParticipant2'
                    ],
                    [
                        'dbItemName'    => 'TestProcedureParticipant3',
                        'className'     => 'TestProcedureParticipant3'
                    ]
                ]
            ],
            [
                'dbItemName'    => 'TestProcedure2',
                'className'     => 'TestProcedure2',
                'participants'  =>
                [
                    [
                        'dbItemName'    => 'TestProcedureParticipant4',
                        'className'     => 'TestProcedureParticipant4'
                    ],
                    [
                        'dbItemName'    => 'TestProcedureParticipant5',
                        'className'     => 'TestProcedureParticipant5'
                    ]
                ]
            ],
            [
                'dbItemName'    => 'TestProcedure3',
                'className'     => 'TestProcedure3',
                'participants'  => []
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

        foreach (self::$tempProcedures as $procedure)
        {
            self::$tempFilesCreator->createTempClass(UsersExchange::class, $procedure['className']);
            $tempProcedureId = self::$tempDBRecordsCreator->createTempRecord(self::$proceduresTable,
            [
                'NAME' => $procedure['dbItemName']
            ]);

            foreach ($procedure['participants'] as $participant)
            {
                self::$tempFilesCreator->createTempClass(Users1C::class, $participant['className']);
                $tempParticipantId = self::$tempDBRecordsCreator->createTempRecord(self::$participantsTable,
                [
                    'NAME' => $participant['dbItemName']
                ]);

                self::$tempDBRecordsCreator->createTempRecord(self::$linkTable,
                [
                    'PROCEDURE'     => $tempProcedureId,
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
     * test getting procedures participants
     *
     * @test
     ************************************************************************/
    public function providingParticipants() : void
    {
        $systemProcedureReflection  = new ReflectionClass(UsersExchange::class);
        $systemProcedureNamespace   = $systemProcedureReflection->getNamespaceName();

        foreach (self::$tempProcedures as $procedure)
        {
            $procedureClassQualifiedName    = $systemProcedureNamespace.'\\'.$procedure['className'];
            $participantsQueue              = call_user_func([new $procedureClassQualifiedName, 'getParticipants']);
            $tempParticipants               = [];
            $currentParticipants            = [];

            foreach ($procedure['participants'] as $participant)
            {
                $tempParticipants[] = $participant['className'];
            }

            while (!$participantsQueue->isEmpty())
            {
                $participant            = $participantsQueue->pop();
                $participantReflection  = new ReflectionClass($participant);
                $currentParticipants[]  = $participantReflection->getShortName();
            }

            self::assertEquals
            (
                $tempParticipants,
                $currentParticipants,
                'Expect get temp procedure participants as temp created'
            );
        }
    }
}