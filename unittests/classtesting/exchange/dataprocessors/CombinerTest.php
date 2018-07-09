<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\DataProcessors;

use
    RuntimeException,
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    UnitTests\ClassTesting\Exchange\Participants\ParticipantForUnitTest,
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\DataProcessors\Collector,
    Main\Exchange\DataProcessors\Matcher,
    Main\Exchange\DataProcessors\Combiner,
    Main\Exchange\DataProcessors\Results\CombinedData;
/** ***********************************************************************************************
 * Test Main\Exchange\DataProcessors\Combiner class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class CombinerTest extends AbstractTestCase
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
     * check combining data process
     *
     * @test
     * @throws
     ************************************************************************/
    public function combiningData() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();
        $tempXmlData            = self::$structureGenerator->getProvidedXmlData();
        $tempCombinedData       = self::$structureGenerator->getCombinedData();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo   = $tempClassesStructure[$procedureCode];
            $procedureXmlData       = $tempXmlData[$procedureCode];
            $procedure              = $this->getProcedureFilledWithParticipantsProvidedData($procedureClassesInfo, $procedureXmlData);
            $collector              = new Collector($procedure);
            $matcher                = new Matcher($procedure);
            $combiner               = new Combiner($procedure);
            $collectedData          = $collector->collectData();
            $matchedData            = $matcher->matchItems($collectedData);
            $combinedData           = $combiner->combineItems($matchedData);

            $expectedData   = $tempCombinedData[$procedureCode];
            $expectedData   = $this->convertTempCombinedData($expectedData, $procedureInfo);
            $expectedData   = $this->sortComplexArray($expectedData);

            $getedData  = $this->convertProcedureCombinedData($combinedData);
            $getedData  = $this->sortComplexArray($getedData);

            self::assertEquals
            (
                $expectedData,
                $getedData,
                'Expect get same combined data as temp created'
            );
        }
    }
    /** **********************************************************************
     * get converted procedure temp combined data
     *
     * @param   array   $procedureCombinedData  procedure combined data
     * @param   array   $procedureStructure     procedure structure
     * @return  array                           converted procedure combined data
     ************************************************************************/
    private function convertTempCombinedData(array $procedureCombinedData, array $procedureStructure) : array
    {
        foreach ($procedureCombinedData as $itemIndex => $itemData)
        {
            foreach ($itemData as $procedureFieldName => $value)
            {
                $newFieldIndexParts = [];
                $procedureFieldStructure = $procedureStructure['fields'][$procedureFieldName];
                foreach ($procedureFieldStructure as $participantCode => $participantFieldName)
                {
                    $newFieldIndexParts[] = "$participantCode--$participantFieldName";
                }
                $newFieldIndex = implode('|', $newFieldIndexParts);

                $procedureCombinedData[$itemIndex][$newFieldIndex] = $value;
                unset($procedureCombinedData[$itemIndex][$procedureFieldName]);
            }
        }

        return $procedureCombinedData;
    }
    /** **********************************************************************
     * get converted procedure combined data as array
     *
     * @param   CombinedData $combinedData      procedure combined data
     * @return  array                           converted procedure combined data
     ************************************************************************/
    private function convertProcedureCombinedData(CombinedData $combinedData) : array
    {
        $result = [];

        while ($combinedData->count() > 0)
        {
            try
            {
                $item       = $combinedData->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $procedureField)
                {
                    $value      = $item->get($procedureField);
                    $indexParts = [];

                    $procedureField->rewind();
                    while ($procedureField->valid())
                    {
                        $participantField       = $procedureField->current();
                        $participant            = $participantField->getParticipant();
                        $participantCode        = $participant->getCode();
                        $participantFieldName   = $participantField->getField()->getParam('name');

                        $indexParts[] = "$participantCode--$participantFieldName";
                        $procedureField->next();
                    }

                    $index = implode('|', $indexParts);
                    $itemArray[$index] = $value;
                }

                $result[] = $itemArray;
            }
            catch (RuntimeException $exception)
            {

            }
        }

        return $result;
    }
    /** **********************************************************************
     * get procedure filled with participants provided data
     *
     * @param   array   $procedureClassesInfo   procedure classes structure
     * @param   array   $procedureXmlInfo       procedure XML data structure
     * @return  Procedure                       procedure
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
     * @param   string $className               procedure class name
     * @return  Procedure                       procedure
     ************************************************************************/
    private function constructProcedure(string $className) : Procedure
    {
        return new $className;
    }
}