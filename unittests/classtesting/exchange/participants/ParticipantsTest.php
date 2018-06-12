<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    ReflectionClass,
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
     * check getting participant fields
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingFields() : void
    {
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
            {
                $participant        = $this->createParticipant($participantName);
                $participantFields  = $participant->getFields();
                $expectedFieldsSet  = $participantInfo['fields'];
                $getedFieldsSet     = [];

                while ($participantFields->valid())
                {
                    $field = $participantFields->current();

                    $getedFieldsSet[$field->getParam('name')] =
                    [
                        'name'      => $field->getParam('name'),
                        'type'      => $field->getParam('type'),
                        'required'  => $field->getParam('required')
                    ];

                    $participantFields->next();
                }

                self::assertEquals
                (
                    $expectedFieldsSet,
                    $getedFieldsSet,
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
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
            {
                $participant    = $this->createParticipant($participantInfo['name']);
                $xml            = self::$structureGenerator->getParticipantXml($participantName);
                $expectedData   = self::$structureGenerator->getParticipantData($participantName);
                $getedData      = [];

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
                    $expectedData,
                    $getedData,
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
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantInfo)
            {
                $participant = $this->createParticipant($participantInfo['name']);

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
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
            {
                $participant    = $this->createParticipant($participantInfo['name']);
                $participantXml = self::$structureGenerator->getParticipantXml($participantName);
                $xmlForDelivery = new SplFileInfo(__DIR__.DIRECTORY_SEPARATOR.'tempXmlFile.xml');

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
     * create participant by name
     *
     * @param   string  $participantName    participant name
     * @return  Participant                 participant
     ************************************************************************/
    private function createParticipant(string $participantName) : Participant
    {
        $procedureReflection    = new ReflectionClass(Participant::class);
        $procedureNamespace     = $procedureReflection->getNamespaceName();
        $procedureQualifiedName = $procedureNamespace.'\\'.$participantName;

        return new $procedureQualifiedName;
    }
}