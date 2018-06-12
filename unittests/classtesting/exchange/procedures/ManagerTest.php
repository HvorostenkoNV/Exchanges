<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    ReflectionClass,
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    Main\Data\MapData,
    Main\Exchange\Procedures\Manager;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Manager class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ManagerTest extends AbstractTestCase
{
    /** @var TempStructureGenerator */
    private static $structureGenerator = null;
    /** **********************************************************************
     * construct
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$structureGenerator = new TempStructureGenerator;
        self::$structureGenerator->generate();
    }
    /** **********************************************************************
     * destruct
     ************************************************************************/
    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();

        self::$structureGenerator->clean();
    }
    /** **********************************************************************
     * check getting procedures by name
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingProceduresByName() : void
    {
        $expectedProcedures = [];
        $getedProcedures    = [];

        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            $expectedProcedures[] = $procedureInfo['name'];
        }

        $filter = new MapData;
        $filter->set('NAME', $expectedProcedures);
        $set = Manager::getProcedures($filter);

        while ($set->valid())
        {
            $procedure          = $set->current();
            $procedureName      = $this->getObjectClassShortName($procedure);
            $getedProcedures[]  = $procedureName;
            $set->next();
        }

        asort($expectedProcedures);
        asort($getedProcedures);
        $expectedProcedures = array_values($expectedProcedures);
        $getedProcedures    = array_values($getedProcedures);

        self::assertEquals
        (
            $expectedProcedures,
            $getedProcedures,
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
        $expectedProcedures = [];
        $getedProcedures    = [];

        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            if ($procedureInfo['activity'])
            {
                $expectedProcedures[] = $procedureInfo['name'];
            }
        }

        $filter = new MapData;
        $filter->set('NAME',        $expectedProcedures);
        $filter->set('ACTIVITY',    true);
        $set = Manager::getProcedures($filter);

        while ($set->valid())
        {
            $procedure          = $set->current();
            $procedureName      = $this->getObjectClassShortName($procedure);
            $getedProcedures[]  = $procedureName;
            $set->next();
        }

        asort($expectedProcedures);
        asort($getedProcedures);
        $expectedProcedures = array_values($expectedProcedures);
        $getedProcedures    = array_values($getedProcedures);

        self::assertEquals
        (
            $expectedProcedures,
            $getedProcedures,
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
        $randomStructure            = self::$structureGenerator->getStructure();
        $randomTempProcedureInfo    = $randomStructure[array_rand($randomStructure)];
        $filter                     = new MapData;

        $filter->set('NAME',        $randomTempProcedureInfo['name']);
        $filter->set('ACTIVITY',    $randomTempProcedureInfo['activity']);
        $set = Manager::getProcedures($filter);

        self::assertEquals
        (
            1,
            $set->count(),
            'Expect get one query row with filter by name'
        );

        $procedure      = $set->current();
        $procedureName  = $this->getObjectClassShortName($procedure);

        self::assertEquals
        (
            $randomTempProcedureInfo['name'],
            $procedureName,
            'Geted procedure not equals expected'
        );
    }
    /** **********************************************************************
     * get object class short name
     *
     * @param   object  $object                         object
     * @return  string                                  object class short name
     ************************************************************************/
    private function getObjectClassShortName($object) : string
    {
        $objectReflection = new ReflectionClass($object);

        return $objectReflection->getShortName();
    }
}