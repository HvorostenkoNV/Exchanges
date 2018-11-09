<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Data;

use
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    UnitTests\ClassTesting\Data\SetDataAbstractTest,
    Main\Exchange\Procedures\Data\ProceduresSet;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Data\ProceduresSet class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProceduresSetTest extends SetDataAbstractTest
{
    /** @var TempStructureGenerator */
    private static $structureGenerator = null;
    /** **********************************************************************
     * construct
     *
     * @return void
     * @throws
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$structureGenerator = new TempStructureGenerator;
        self::$structureGenerator->generate();
    }
    /** **********************************************************************
     * destruct
     *
     * @return void
     ************************************************************************/
    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();

        self::$structureGenerator->clean();
    }
    /** **********************************************************************
     * get set class name
     *
     * @return  string                      set class name
     ************************************************************************/
    public static function getSetClassName() : string
    {
        return ProceduresSet::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        $result                 = [];
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassName = $tempClassesStructure[$procedureCode]['class'];
            $result[] = new $procedureClassName;
        }

        return $result;
    }
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data array
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return
        [
            'string',
            '',
            2,
            2.5,
            0,
            true,
            false,
            [1, 2, 3],
            ['string', '', 2.5, 0, true, false],
            [],
            new ProceduresSet,
            null
        ];
    }
}