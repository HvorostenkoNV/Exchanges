<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    ReflectionClass,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempFilesGenerator,
    UnitTests\Core\TempDBRecordsGenerator,
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

            self::$tempDBRecordsGenerator->createTempRecord(self::$proceduresTable,
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
        self::$tempFilesGenerator->dropCreatedTempData();
        self::$tempDBRecordsGenerator->dropTempChanges();
    }
    /** **********************************************************************
     * check getting procedures by name
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingProceduresByName() : void
    {
        $tempProcedures = [];
        $procedures     = [];

        foreach (self::$tempProcedures as $procedure)
        {
            $tempProcedures[] = $procedure['className'];
        }

        $filter = new MapData;
        $filter->set('NAME', $tempProcedures);
        $set = Manager::getProcedures($filter);

        while ($set->valid())
        {
            $procedure              = $set->current();
            $procedureClass         = get_class($procedure);
            $procedureReflection    = new ReflectionClass($procedureClass);
            $procedures[]           = $procedureReflection->getShortName();

            $set->next();
        }

        self::assertEquals
        (
            $tempProcedures,
            $procedures,
            'Geted procedures not equal temp expected'
        );
    }
    /** **********************************************************************
     * check getting procedures by activity
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingProceduresByActivity() : void
    {
        $tempProcedures = [];
        $procedures     = [];

        foreach (self::$tempProcedures as $procedure)
        {
            if ($procedure['activity'])
            {
                $tempProcedures[] = $procedure['className'];
            }
        }

        $filter = new MapData;
        $filter->set('NAME',        $tempProcedures);
        $filter->set('ACTIVITY',    true);
        $set = Manager::getProcedures($filter);

        while ($set->valid())
        {
            $procedure              = $set->current();
            $procedureClass         = get_class($procedure);
            $procedureReflection    = new ReflectionClass($procedureClass);
            $procedures[]           = $procedureReflection->getShortName();

            $set->next();
        }

        self::assertEquals
        (
            $tempProcedures,
            $procedures,
            'Geted procedures not equal temp expected'
        );
    }
    /** **********************************************************************
     * check getting one procedure by name
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingOneProcedureByName() : void
    {
        $randTempProcedure  = self::$tempProcedures[array_rand(self::$tempProcedures)];
        $filter             = new MapData;

        $filter->set('NAME',        $randTempProcedure['dbItemName']);
        $filter->set('ACTIVITY',    $randTempProcedure['activity']);
        $set = Manager::getProcedures($filter);

        self::assertEquals
        (
            1,
            $set->count(),
            'Expect get one query row with filter by name'
        );

        $procedure              = $set->current();
        $procedureClass         = get_class($procedure);
        $procedureReflection    = new ReflectionClass($procedureClass);
        $procedureClassName     = $procedureReflection->getShortName();

        self::assertEquals
        (
            $randTempProcedure['dbItemName'],
            $procedureClassName,
            'Geted procedure not equals expected'
        );
    }
}