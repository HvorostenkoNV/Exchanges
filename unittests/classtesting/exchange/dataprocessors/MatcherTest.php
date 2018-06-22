<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors;

use
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantForUnitTest,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\DataProcessors\Collector,
    Main\Exchange\DataProcessors\Matcher;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Collector class
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
     * check matching data process
     *
     * @test
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
            $expectedData           = $tempProvidedMatchedData[$procedureCode];
            $getedData              = [];

            $matchedData = $matcher->matchItems($collectedData);
            while ($matchedData->count() > 0)
            {
                $item       = $matchedData->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $participant)
                {
                    $participantItem        = $item->get($participant);
                    $participantCode        = $this->getParticipantCode($participant, $procedureClassesInfo);
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
    private function getProcedureFilledWithParticipantsProvidedData($procedureClassesInfo, $procedureXmlInfo) : Procedure
    {
        $procedure      = $this->constructProcedure($procedureClassesInfo['class']);
        $participants   = $procedure->getParticipants();

        while ($participants->valid())
        {
            $participant        = $participants->current();
            $participantCode    = $this->getParticipantCode($participant, $procedureClassesInfo);
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
    /** **********************************************************************
     * get participant code form object
     *
     * @param   Participant $participant                participant
     * @param   array       $procedureClassesInfo       procedure classes structure
     * @return  string                                  participant code
     ************************************************************************/
    private function getParticipantCode(Participant $participant, array $procedureClassesInfo) : string
    {
        $participantClassName = get_class($participant);

        foreach ($procedureClassesInfo['participants'] as $participantCode => $participantInfo)
        {
            if ($participantInfo['class'] == $participantClassName)
            {
                return $participantCode;
            }
        }

        return '';
    }
}