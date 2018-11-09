<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors;

use
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator    as TempStructureGenerator,
    Main\Exchange\DataProcessors\Collector          as SystemProcessor,
    Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Processor class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProcessorTest extends AbstractTestCase
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
     * check constructing object process
     *
     * @test
     * @return void
     * @throws
     ************************************************************************/
    public function constructing() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedure  = $this->constructProcedure($tempClassesStructure[$procedureCode]);
            $processor  = new SystemProcessor($procedure);

            self::assertEquals
            (
                $procedure,
                $processor->getProcedure(),
                'Expect get same procedure as was seted'
            );
        }
    }
    /** **********************************************************************
     * construct procedure
     *
     * @param   array $procedureClassesInfo         procedure classes structure
     * @return  Procedure|null                      procedure
     ************************************************************************/
    private function constructProcedure(array $procedureClassesInfo) : ?Procedure
    {
        $className = $procedureClassesInfo['class'];

        return new $className;
    }
}