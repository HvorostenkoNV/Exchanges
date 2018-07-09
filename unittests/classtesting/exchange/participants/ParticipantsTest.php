<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    SplFileInfo,
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    Main\Exchange\Participants\Participant,
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Participant classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ParticipantsTest extends AbstractTestCase
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
     * check getting participant code
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingCode() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
            {
                $participantClassName   = $tempClassesStructure[$procedureCode]['participants'][$participantCode]['class'];
                $participant            = $this->constructParticipant($participantClassName);
                $participantCode        = $participant->getCode();

                self::assertNotEmpty
                (
                    $participantCode,
                    'Expect participant provides not empty code'
                );
            }
        }
    }
    /** **********************************************************************
     * check getting participant fields
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingFields() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
            {
                $participantClassName   = $tempClassesStructure[$procedureCode]['participants'][$participantCode]['class'];
                $participant            = $this->constructParticipant($participantClassName);
                $participantFields      = $participant->getFields();
                $expectedFieldsSet      = $participantInfo['fields'];
                $getedFieldsSet         = [];

                while ($participantFields->valid())
                {
                    $field      = $participantFields->current();
                    $fieldName  = $field->getParam('name');

                    $getedFieldsSet[$fieldName] =
                    [
                        'name'      => $fieldName,
                        'type'      => $field->getParam('type'),
                        'required'  => $field->getParam('required')
                    ];

                    $participantFields->next();
                }

                self::assertEquals
                (
                    $this->sortComplexArray($expectedFieldsSet),
                    $this->sortComplexArray($getedFieldsSet),
                    'Expect get participant fields as temp created'
                );
            }
        }
    }
    /** **********************************************************************
     * check participant getting provided data
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingProvidedData() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();
        $tempProvidedData       = self::$structureGenerator->getProvidedData();
        $tempXmlData            = self::$structureGenerator->getProvidedXmlData();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
            {
                $participantClassName   = $tempClassesStructure[$procedureCode]['participants'][$participantCode]['class'];
                $participant            = $this->constructParticipant($participantClassName);
                $xml                    = $this->constructParticipantXmlData($tempXmlData[$procedureCode], $participantCode);
                $expectedData           = $tempProvidedData[$procedureCode][$participantCode];
                $getedData              = [];

                $participant->{'xmlWithProvidedData'} = $xml;
                $providedData = $participant->getProvidedData();

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

                    $getedData[] = $itemData;
                }

                self::assertEquals
                (
                    $this->sortComplexArray($expectedData),
                    $this->sortComplexArray($getedData),
                    'Expect get same data as temp created'
                );
            }
        }
    }
    /** **********************************************************************
     * check participant getting incorrect provided data
     *
     * @test
     * @depends gettingProvidedData
     * @throws
     ************************************************************************/
    public function gettingIncorrectProvidedData() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
            {
                $participantClassName   = $tempClassesStructure[$procedureCode]['participants'][$participantCode]['class'];
                $participant            = $this->constructParticipant($participantClassName);

                $participant->{'xmlWithProvidedData'} = new SplFileInfo('someIncorrectFilePath');
                $providedData = $participant->getProvidedData();

                self::assertEquals
                (
                    0,
                    $providedData->count(),
                    'Expect get empty provided data with reading incorrect xml file'
                );
            }
        }
    }
    /** **********************************************************************
     * check participant providing data process
     *
     * @test
     * @depends gettingProvidedData
     * @throws
     ************************************************************************/
    public function providingData() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();
        $tempXmlData            = self::$structureGenerator->getProvidedXmlData();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
            {
                $participantClassName   = $tempClassesStructure[$procedureCode]['participants'][$participantCode]['class'];
                $participant            = $this->constructParticipant($participantClassName);
                $participantXml         = $this->constructParticipantXmlData($tempXmlData[$procedureCode], $participantCode);
                $xmlForDelivery         = new SplFileInfo(__DIR__.DIRECTORY_SEPARATOR.'tempXmlFile.xml');

                $participant->{'xmlWithProvidedData'} = $participantXml;
                $providedData = $participant->getProvidedData();

                $dataForDelivery = new DataForDelivery;
                while (!$providedData->isEmpty())
                {
                    $dataForDelivery->push($providedData->pop());
                }

                fopen($xmlForDelivery->getPathname(), 'w');
                $participant->{'xmlForDelivery'} = $xmlForDelivery;
                $participant->deliveryData($dataForDelivery);

                $receivedContent    = $participantXml->openFile('r')->fread($participantXml->getSize());
                $deliveredContent   = $xmlForDelivery->openFile('r')->fread($xmlForDelivery->getSize());
                unlink($xmlForDelivery->getPathname());

                self::assertEquals
                (
                    $receivedContent,
                    $deliveredContent,
                    'Delivered xml expect to be same as received'
                );
            }
        }
    }
    /** **********************************************************************
     * construct participant by class name
     *
     * @param   string $className           participant class name
     * @return  Participant                 participant
     ************************************************************************/
    private function constructParticipant(string $className) : Participant
    {
        return new $className;
    }
    /** **********************************************************************
     * get participant XML data file
     *
     * @param   array   $procedureXmlInfo   temp XML data files structure
     * @param   string  $participantCode    participant code
     * @return  SplFileInfo                 participant XML data file
     ************************************************************************/
    private function constructParticipantXmlData(array $procedureXmlInfo, string $participantCode) : ?SplFileInfo
    {
        return $procedureXmlInfo[$participantCode];
    }
}