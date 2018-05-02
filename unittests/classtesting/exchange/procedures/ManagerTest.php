<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    ReflectionClass,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempFilesCreator,
    UnitTests\Core\TempDBRecordsCreator,
    Main\Data\MapData,
    Main\Exchange\Procedures\UsersExchange,
    Main\Exchange\Procedures\Manager;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Manager class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ManagerTest extends ExchangeTestCase
{
    private static
        $proceduresTable    = 'procedures',
        $tempProcedures     =
        [
            [
                'dbItemName'    => 'TestManagerProcedure1',
                'className'     => 'TestManagerProcedure1',
                'activity'      => true
            ],
            [
                'dbItemName'    => 'TestManagerProcedure2',
                'className'     => 'TestManagerProcedure2',
                'activity'      => true
            ],
            [
                'dbItemName'    => 'TestManagerProcedure3',
                'className'     => 'TestManagerProcedure3',
                'activity'      => false
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

            self::$tempDBRecordsCreator->createTempRecord(self::$proceduresTable,
            [
                'NAME'      => $procedure['dbItemName'],
                'ACTIVITY'  => $procedure['activity'] ? 'Y' : 'N'
            ]);
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
     * check getting procedures with different filter params
     *
     * @test
     ************************************************************************/
    public function providingProcedures() : void
    {
        $tempAllProcedures      = [];
        $tempActiveProcedures   = [];
        $allProcedures          = [];
        $activeProcedures       = [];

        foreach (self::$tempProcedures as $procedure)
        {
            $tempAllProcedures[] = $procedure['className'];
            if ($procedure['activity'])
            {
                $tempActiveProcedures[] = $procedure['className'];
            }
        }

        $queue = Manager::getProcedures(new MapData(['NAME' => $tempAllProcedures]));
        while (!$queue->isEmpty())
        {
            $procedure              = $queue->pop();
            $procedureReflection    = new ReflectionClass(get_class($procedure));
            $allProcedures[]        = $procedureReflection->getShortName();
        }

        $queue = Manager::getProcedures(new MapData(['NAME' => $tempAllProcedures, 'ACTIVITY' => true]));
        while (!$queue->isEmpty())
        {
            $procedure              = $queue->pop();
            $procedureReflection    = new ReflectionClass(get_class($procedure));
            $activeProcedures[]     = $procedureReflection->getShortName();
        }

        self::assertEquals
        (
            $tempAllProcedures,
            $allProcedures,
            'Geted procedures not equal temp created'
        );
        self::assertEquals
        (
            $tempActiveProcedures,
            $activeProcedures,
            'Geted active procedures not equal temp created'
        );
    }
    /** **********************************************************************
     * check getting procedure by name
     *
     * @test
     ************************************************************************/
    public function providingProcedureByName() : void
    {
        $randTempProcedure  = self::$tempProcedures[array_rand(self::$tempProcedures)];
        $filter             = new MapData
        ([
            'NAME'      => $randTempProcedure['dbItemName'],
            'ACTIVITY'  => $randTempProcedure['activity']
        ]);
        $queueResult        = Manager::getProcedures($filter);

        self::assertEquals
        (
            1,
            $queueResult->count(),
            'Expect get one query row with filter by name'
        );

        if ($queueResult->count() == 1)
        {
            $procedure              = $queueResult->pop();
            $procedureReflection    = new ReflectionClass(get_class($procedure));
            $procedureClassName     = $procedureReflection->getShortName();

            self::assertEquals
            (
                $randTempProcedure['dbItemName'],
                $procedureClassName,
                'Geted procedure not equals expected'
            );
        }
    }
}