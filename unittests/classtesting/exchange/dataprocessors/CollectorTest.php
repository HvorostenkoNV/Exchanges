<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors;

use
    ReflectionClass,
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantForUnitTest,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\DataProcessors\Collector;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Collector class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class CollectorTest extends AbstractTestCase
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
        self::$structureGenerator->setParticipantParentClass(ParticipantForUnitTest::class);
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
     * check collecting data process
     *
     * @test
     * @throws
     ************************************************************************/
    public function collectingData() : void
    {
        foreach (self::$structureGenerator->getStructure() as $procedureName => $procedureInfo)
        {
            $procedure = $this->createProcedure($procedureName);
            $this->setParticipantsTempProvidedData($procedure);

            $collector      = new Collector($procedure);
            $collectedData  = $collector->collectData();
            $expectedData   = [];
            $getedData      = [];

            foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
            {
                $data = self::$structureGenerator->getParticipantData($participantName);
                $expectedData[$participantName] = $data;
            }

            foreach ($collectedData->getKeys() as $participant)
            {
                $participantName    = $this->getObjectClassShortName($participant);
                $providedData       = $collectedData->get($participant);

                $getedData[$participantName] = [];
                while (!$providedData->isEmpty())
                {
                    $item       = $providedData->pop();
                    $itemData   = [];

                    foreach ($item->getKeys() as $field)
                    {
                        $fieldName              = $field->getParam('name');
                        $value                  = $item->get($field);
                        $itemData[$fieldName]   = $value;
                    }

                    $getedData[$participantName][] = $itemData;
                }
            }

            self::assertEquals
            (
                $expectedData,
                $getedData,
                'Expect get same collected data as temp created participants data'
            );
        }
    }
    /** **********************************************************************
     * create procedure by name
     *
     * @param   string $procedureName                   procedure name
     * @return  Procedure                               procedure
     ************************************************************************/
    private function createProcedure(string $procedureName) : Procedure
    {
        $procedureReflection    = new ReflectionClass(Procedure::class);
        $procedureNamespace     = $procedureReflection->getNamespaceName();
        $procedureQualifiedName = $procedureNamespace.'\\'.$procedureName;

        return new $procedureQualifiedName;
    }
    /** **********************************************************************
     * get object class short name
     *
     * @param   object $object                          object
     * @return  string                                  object class short name
     ************************************************************************/
    private function getObjectClassShortName($object) : string
    {
        $objectReflection = new ReflectionClass($object);

        return $objectReflection->getShortName();
    }
    /** **********************************************************************
     * check collecting data process
     *
     * @param   Procedure $procedure                    procedure
     ************************************************************************/
    private function setParticipantsTempProvidedData(Procedure $procedure) : void
    {
        $participants = $procedure->getParticipants();

        while ($participants->valid())
        {
            $participant        = $participants->current();
            $participantName    = $this->getObjectClassShortName($participant);
            $xml                = self::$structureGenerator->getParticipantXml($participantName);

            $participant->{'xmlWithProvidedData'} = $xml;
            $participants->next();
        }
    }
}