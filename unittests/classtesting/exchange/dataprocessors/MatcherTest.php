<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors;

use
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantForUnitTest,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\DataProcessors\Collector,
    Main\Exchange\DataProcessors\Matcher;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Matcher class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MatcherTest extends AbstractTestCase
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
        self::$structureGenerator->setParticipantParentClass(ParticipantForUnitTest::class);
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
     * check matching data process
     *
     * @test
     * @return void
     * @throws
     ************************************************************************/
    public function matchingData() : void
    {
        $tempStructure              = self::$structureGenerator->getStructure();
        $tempClassesStructure       = self::$structureGenerator->getClassesStructure();
        $tempProvidedMatchedData    = self::$structureGenerator->getProvidedMatchedData();
        $tempXmlData                = self::$structureGenerator->getProvidedXmlData();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo   = $tempClassesStructure[$procedureCode];
            $procedureXmlData       = $tempXmlData[$procedureCode];
            $procedure              = $this->getProcedureFilledWithParticipantsProvidedData($procedureClassesInfo, $procedureXmlData);
            $collector              = new Collector($procedure);
            $matcher                = new Matcher($procedure);
            $collectedData          = $collector->collectData();
            $matchedData            = $matcher->matchItems($collectedData);
            $expectedData           = $tempProvidedMatchedData[$procedureCode];
            $getedData              = [];

            while ($matchedData->count() > 0)
            {
                $item       = $matchedData->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $participant)
                {
                    $participantItem        = $item->get($participant);
                    $participantCode        = $participant->getCode();
                    $participantItemArray   = [];

                    foreach ($participantItem->getKeys() as $field)
                    {
                        $fieldName  = $field->getParam('name');
                        $value      = $participantItem->get($field);
                        $participantItemArray[$fieldName] = $value;
                    }

                    $itemArray[$participantCode] = $participantItemArray;
                }

                $getedData[] = $itemArray;
            }

            self::assertEquals
            (
                $this->sortComplexArray($expectedData),
                $this->sortComplexArray($getedData),
                'Expect get same matched data as temp created'
            );
        }
    }
    /** **********************************************************************
     * get procedure filled with participants provided data
     *
     * @param   array   $procedureClassesInfo           procedure classes structure
     * @param   array   $procedureXmlInfo               procedure XML data structure
     * @return  Procedure                               procedure
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
     * @param   string $className                       procedure class name
     * @return  Procedure                               procedure
     ************************************************************************/
    private function constructProcedure(string $className) : Procedure
    {
        return new $className;
    }
}