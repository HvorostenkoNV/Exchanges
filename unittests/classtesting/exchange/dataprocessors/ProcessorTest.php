<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors;

use
    ReflectionClass,
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
     * check constructing object process
     *
     * @test
     * @throws
     ************************************************************************/
    public function constructing() : void
    {
        foreach (self::$structureGenerator->getStructure() as $procedureName => $procedureInfo)
        {
            $procedure  = $this->createProcedure($procedureName);
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
     * create procedure by name
     *
     * @param   string  $procedureName                  procedure name
     * @return  Procedure                               procedure
     ************************************************************************/
    private function createProcedure(string $procedureName) : Procedure
    {
        $procedureReflection    = new ReflectionClass(Procedure::class);
        $procedureNamespace     = $procedureReflection->getNamespaceName();
        $procedureQualifiedName = $procedureNamespace.'\\'.$procedureName;

        return new $procedureQualifiedName;
    }
}