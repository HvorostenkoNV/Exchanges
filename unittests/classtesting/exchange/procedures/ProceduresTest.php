<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    ReflectionClass,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempFilesGenerator,
    UnitTests\Core\TempDBRecordsGenerator,
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

        self::$tempFilesGenerator       = new TempFilesGenerator;
        self::$tempDBRecordsGenerator   = new TempDBRecordsGenerator;

        foreach (self::$tempProcedures as $procedure)
        {
            self::$tempFilesGenerator->createTempClass(UsersExchange::class, $procedure['className']);
            $tempProcedureId = self::$tempDBRecordsGenerator->createTempRecord(self::$proceduresTable,
            [
                'NAME' => $procedure['dbItemName']
            ]);

            foreach ($procedure['participants'] as $participant)
            {
                self::$tempFilesGenerator->createTempClass(Users1C::class, $participant['className']);
                $tempParticipantId = self::$tempDBRecordsGenerator->createTempRecord(self::$participantsTable,
                [
                    'NAME' => $participant['dbItemName']
                ]);

                self::$tempDBRecordsGenerator->createTempRecord(self::$linkTable,
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
        self::$tempFilesGenerator->dropCreatedTempData();
        self::$tempDBRecordsGenerator->dropTempChanges();
    }
    /** **********************************************************************
     * test getting procedures participants
     *
     * @test
     * @throws
     ************************************************************************/
    public function providingParticipants() : void
    {
        $systemProcedureReflection  = new ReflectionClass(UsersExchange::class);
        $systemProcedureNamespace   = $systemProcedureReflection->getNamespaceName();

        foreach (self::$tempProcedures as $procedure)
        {
            $procedureClassQualifiedName    = $systemProcedureNamespace.'\\'.$procedure['className'];
            $participantsSet                = call_user_func([new $procedureClassQualifiedName, 'getParticipants']);
            $tempParticipants               = [];
            $currentParticipants            = [];

            foreach ($procedure['participants'] as $participant)
            {
                $tempParticipants[] = $participant['className'];
            }

            while ($participantsSet->valid())
            {
                $participant            = $participantsSet->current();
                $participantReflection  = new ReflectionClass($participant);
                $currentParticipants[]  = $participantReflection->getShortName();

                $participantsSet->next();
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