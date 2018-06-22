<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Fields;

use
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    Main\Data\MapData,
    Main\Exchange\Procedures\Fields\ParticipantField,
    Main\Exchange\Participants\Fields\Field,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Fields\ParticipantField class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ParticipantFieldTest extends AbstractTestCase
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
     * get params for field construct
     *
     * @return  array                       params for field construct
     ************************************************************************/
    public static function getParamsForFieldConstruct() : array
    {
        $result                 = [];
        $fieldsTypes            = FieldsTypesManager::getAvailableFieldsTypes();
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
            {
                $fieldParams = new MapData;
                $fieldParams->set('name', $participantCode.'Field');
                $fieldParams->set('type', $fieldsTypes[array_rand($fieldsTypes)]);

                $participantClassName = $tempClassesStructure[$procedureCode]['participants'][$participantCode]['class'];
                $result[] = [new $participantClassName, new Field($fieldParams)];
            }
        }

        return $result;
    }
    /** **********************************************************************
     * check getting field components
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingComponents() : void
    {
        foreach (self::getParamsForFieldConstruct() as $arrayInfo)
        {
            $participant        = $arrayInfo[0];
            $field              = $arrayInfo[1];
            $participantField   = new ParticipantField($participant, $field);

            self::assertEquals
            (
                $participant,
                $participantField->getParticipant(),
                'Expect get same participant as was seted'
            );

            self::assertEquals
            (
                $field,
                $participantField->getField(),
                'Expect get same field as was seted'
            );
        }
    }
}