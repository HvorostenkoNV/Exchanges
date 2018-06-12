<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    ReflectionClass,
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    Main\Exchange\Procedures\Procedure;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Procedure classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProceduresTest extends AbstractTestCase
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
     * check getting procedures participants
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingParticipants() : void
    {
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            $participantsSet            = $this->createProcedure($procedureInfo['name'])->getParticipants();
            $expectedParticipantsSet    = array_keys($procedureInfo['participants']);
            $currentParticipantsSet     = [];

            while ($participantsSet->valid())
            {
                $participant                = $participantsSet->current();
                $currentParticipantsSet[]   = $this->getObjectClassShortName($participant);
                $participantsSet->next();
            }

            self::assertEquals
            (
                $expectedParticipantsSet,
                $currentParticipantsSet,
                'Expect get temp procedure participants same as temp created'
            );
        }
    }
    /** **********************************************************************
     * check getting procedure fields
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingFields() : void
    {
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            $procedureFieldsSet = $this->createProcedure($procedureInfo['name'])->getFields();
            $expectedFieldsSet  = array_values($procedureInfo['fields']);
            $getedFieldsSet     = [];

            while ($procedureFieldsSet->valid())
            {
                $procedureField         = $procedureFieldsSet->current();
                $procedureFieldArray    = [];

                while ($procedureField->valid())
                {
                    $participantField   = $procedureField->current();
                    $participant        = $participantField->getParticipant();
                    $participantName    = $this->getObjectClassShortName($participant);
                    $field              = $participantField->getField();

                    $procedureFieldArray[$participantName] = $field->getParam('name');
                    $procedureField->next();
                }

                $getedFieldsSet[] = $procedureFieldArray;
                $procedureFieldsSet->next();
            }

            self::assertEquals
            (
                $this->sortComplexArray($expectedFieldsSet),
                $this->sortComplexArray($getedFieldsSet),
                'Geted procedure fields not equals expected'
            );
        }
    }
    /** **********************************************************************
     * check getting procedures data matching rules
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingDataMatchingRules() : void
    {
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            $dataMatchingRules  = $this->createProcedure($procedureInfo['name'])->getDataMatchingRules();
            $expectedRulesSet   = array_values($procedureInfo['dataMatchingRules']);
            $getedRulesSet      = [];

            foreach ($expectedRulesSet as $ruleIndex => $ruleInfo)
            {
                foreach ($ruleInfo['fields'] as $index => $fieldName)
                {
                    $expectedRulesSet[$ruleIndex]['fields'][$index] = $procedureInfo['fields'][$fieldName];
                }
            }

            foreach ($dataMatchingRules->getKeys() as $participantSet)
            {
                $fieldSet   = $dataMatchingRules->get($participantSet);
                $rule       =
                [
                    'participants'  => [],
                    'fields'        => []
                ];

                while ($participantSet->valid())
                {
                    $participant = $participantSet->current();
                    $rule['participants'][] = $this->getObjectClassShortName($participant);
                    $participantSet->next();
                }

                while ($fieldSet->valid())
                {
                    $procedureField         = $fieldSet->current();
                    $procedureFieldArray    = [];

                    $procedureField->rewind();
                    while ($procedureField->valid())
                    {
                        $participantField   = $procedureField->current();
                        $participant        = $participantField->getParticipant();
                        $participantName    = $this->getObjectClassShortName($participant);
                        $field              = $participantField->getField();

                        $procedureFieldArray[$participantName] = $field->getParam('name');
                        $procedureField->next();
                    }

                    $rule['fields'][] = $procedureFieldArray;
                    $fieldSet->next();
                }

                $getedRulesSet[] = $rule;
            }

            self::assertEquals
            (
                $this->sortComplexArray($expectedRulesSet),
                $this->sortComplexArray($getedRulesSet),
                'Geted procedure data matching rules not equals expected'
            );
        }
    }
    /** **********************************************************************
     * check getting procedures data combining rules
     *
     * @test
     * @throws
     ************************************************************************/
    public function gettingDataCombiningRules() : void
    {
        foreach (self::$structureGenerator->getStructure() as $procedureInfo)
        {
            $dataCombiningRules = $this->createProcedure($procedureInfo['name'])->getDataCombiningRules();
            $expectedRulesSet   = $procedureInfo['dataCombiningRules'];
            $getedRulesSet      = [];

            foreach ($dataCombiningRules->getKeys() as $participantField)
            {
                $participant        = $participantField->getParticipant();
                $participantName    = $this->getObjectClassShortName($participant);
                $field              = $participantField->getField();
                $fieldName          = $field->getParam('name');
                $weight             = $dataCombiningRules->get($participantField);

                if (!array_key_exists($participantName, $getedRulesSet))
                {
                    $getedRulesSet[$participantName] = [];
                }

                $getedRulesSet[$participantName][$fieldName] = $weight;
            }

            self::assertEquals
            (
                $this->sortComplexArray($expectedRulesSet),
                $this->sortComplexArray($getedRulesSet),
                'Geted procedure data combining rules not equals expected'
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
    /** **********************************************************************
     * get object class short name
     *
     * @param   object  $object                         object
     * @return  string                                  object class short name
     ************************************************************************/
    private function getObjectClassShortName($object) : string
    {
        $objectReflection = new ReflectionClass($object);

        return $objectReflection->getShortName();
    }
    /** **********************************************************************
     * sort complex array
     *
     * @param   array   $array                          array
     * @return  array                                   sorted array
     ************************************************************************/
    private function sortComplexArray(array $array) : array
    {
        foreach ($array as $index => $value)
        {
            if (is_array($value))
            {
                $value = $this->sortComplexArray($value);
            }

            $array[json_encode($value)] = $value;
            unset($array[$index]);
        }

        asort($array);

        return array_values($array);
    }
}