<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    Main\Data\MapData,
    Main\Exchange\Procedures\Manager,
    Main\Exchange\Procedures\Procedure;
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
     * check getting procedures by code
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingProceduresByCode() : void
    {
        $expectedProcedures     = [];
        $getedProcedures        = [];
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureInfo)
        {
            $expectedProcedures[] = $procedureInfo['code'];
        }

        $filter = new MapData;
        $filter->set('CODE', $expectedProcedures);
        $set = Manager::getProcedures($filter);

        while ($set->valid())
        {
            $procedure      = $set->current();
            $procedureCode  = $this->findProcedureCode($procedure, $tempClassesStructure);

            $getedProcedures[] = $procedureCode;
            $set->next();
        }

        self::assertEquals
        (
            $this->sortComplexArray($expectedProcedures),
            $this->sortComplexArray($getedProcedures),
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
        $expectedProcedures     = [];
        $getedProcedures        = [];
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureInfo)
        {
            if ($procedureInfo['activity'])
            {
                $expectedProcedures[] = $procedureInfo['code'];
            }
        }

        $filter = new MapData;
        $filter->set('CODE',        $expectedProcedures);
        $filter->set('ACTIVITY',    true);
        $set = Manager::getProcedures($filter);

        while ($set->valid())
        {
            $procedure          = $set->current();
            $procedureCode      = $this->findProcedureCode($procedure, $tempClassesStructure);
            $getedProcedures[]  = $procedureCode;
            $set->next();
        }

        self::assertEquals
        (
            $this->sortComplexArray($expectedProcedures),
            $this->sortComplexArray($getedProcedures),
            'Geted procedures not equal temp expected'
        );
    }
    /** **********************************************************************
     * check getting one procedure by code
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingOneProcedureByCode() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureInfo)
        {
            $filter = new MapData;
            $filter->set('CODE',        $procedureInfo['code']);
            $filter->set('ACTIVITY',    $procedureInfo['activity']);
            $set = Manager::getProcedures($filter);

            self::assertEquals
            (
                1,
                $set->count(),
                'Expect get one query row with filter by code'
            );

            $procedure      = $set->current();
            $procedureCode  = $this->findProcedureCode($procedure, $tempClassesStructure);

            self::assertEquals
            (
                $procedureInfo['code'],
                $procedureCode,
                'Geted procedure not equals expected'
            );
        }
    }
    /** **********************************************************************
     * get procedure code form object
     *
     * @param   Procedure   $procedure          procedure
     * @param   array       $tempClassesInfo    temp classes structure
     * @return  string                          procedure code
     ************************************************************************/
    private function findProcedureCode(Procedure $procedure, array $tempClassesInfo) : string
    {
        $procedureClassName = get_class($procedure);

        foreach ($tempClassesInfo as $procedureCode => $procedureInfo)
        {
            if ($procedureInfo['class'] == $procedureClassName)
            {
                return $procedureCode;
            }
        }

        return '';
    }
}