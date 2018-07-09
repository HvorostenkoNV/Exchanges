<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors;

use
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
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();
        $tempProvidedData       = self::$structureGenerator->getProvidedData();
        $tempXmlData            = self::$structureGenerator->getProvidedXmlData();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo   = $tempClassesStructure[$procedureCode];
            $procedureXmlData       = $tempXmlData[$procedureCode];
            $procedure              = $this->getProcedureFilledWithParticipantsProvidedData($procedureClassesInfo, $procedureXmlData);
            $collector              = new Collector($procedure);
            $collectedData          = $collector->collectData();
            $expectedData           = $tempProvidedData[$procedureCode];
            $getedData              = [];

            foreach ($collectedData->getKeys() as $participant)
            {
                $providedData       = $collectedData->get($participant);
                $participantCode    = $participant->getCode();

                $getedData[$participantCode] = [];
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

                    $getedData[$participantCode][] = $itemData;
                }
            }

            self::assertEquals
            (
                $this->sortComplexArray($expectedData),
                $this->sortComplexArray($getedData),
                'Expect get same collected data as temp created participants data'
            );
        }
    }
    /** **********************************************************************
     * get procedure filled with participants provided data
     *
     * @param   array   $procedureClassesInfo       procedure classes structure
     * @param   array   $procedureXmlInfo           procedure XML data structure
     * @return  Procedure                           procedure
     ************************************************************************/
    private function getProcedureFilledWithParticipantsProvidedData(array $procedureClassesInfo, array $procedureXmlInfo) : Procedure
    {
        $procedure      = $this->constructProcedure($procedureClassesInfo['class']);
        $participants   = $procedure->getParticipants();

        while ($participants->valid())
        {
            $participant        = $participants->current();
            $participantCode    = $participant->getCode();
            $xml                = $procedureXmlInfo[$participantCode];

            $participant->{'xmlWithProvidedData'} = $xml;
            $participants->next();
        }

        return $procedure;
    }
    /** **********************************************************************
     * construct procedure by class name
     *
     * @param   string $className                   procedure class name
     * @return  Procedure                           procedure
     ************************************************************************/
    private function constructProcedure(string $className) : Procedure
    {
        return new $className;
    }
}