<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    UnitTests\AbstractTestCase,
    UnitTests\ProjectTempStructure\MainGenerator as TempStructureGenerator,
    Main\Exchange\Participants\Participant,
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
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo       = $tempClassesStructure[$procedureCode];
            $procedure                  = $this->constructProcedure($procedureClassesInfo['class']);
            $participantsSet            = $procedure->getParticipants();
            $expectedParticipantsSet    = array_keys($procedureInfo['participants']);
            $currentParticipantsSet     = [];

            while ($participantsSet->valid())
            {
                $participant        = $participantsSet->current();
                $participantCode    = $this->findProcedureCode($participant, $procedureClassesInfo);

                $currentParticipantsSet[] = $participantCode;
                $participantsSet->next();
            }

            self::assertEquals
            (
                $this->sortComplexArray($expectedParticipantsSet),
                $this->sortComplexArray($currentParticipantsSet),
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
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo   = $tempClassesStructure[$procedureCode];
            $procedure              = $this->constructProcedure($procedureClassesInfo['class']);
            $procedureFieldsSet     = $procedure->getFields();
            $expectedFieldsSet      = array_values($procedureInfo['fields']);
            $getedFieldsSet         = [];

            while ($procedureFieldsSet->valid())
            {
                $procedureField         = $procedureFieldsSet->current();
                $procedureFieldArray    = [];

                while ($procedureField->valid())
                {
                    $participantField   = $procedureField->current();
                    $participant        = $participantField->getParticipant();
                    $participantCode    = $this->findProcedureCode($participant, $procedureClassesInfo);
                    $field              = $participantField->getField();

                    $procedureFieldArray[$participantCode] = $field->getParam('name');
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
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo   = $tempClassesStructure[$procedureCode];
            $procedure              = $this->constructProcedure($procedureClassesInfo['class']);
            $dataMatchingRules      = $procedure->getDataMatchingRules();
            $expectedRulesSet       = array_values($procedureInfo['dataMatchingRules']);
            $getedRulesSet          = [];

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
                    $participant        = $participantSet->current();
                    $participantCode    = $this->findProcedureCode($participant, $procedureClassesInfo);

                    $rule['participants'][] = $participantCode;
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
                        $participantCode    = $this->findProcedureCode($participant, $procedureClassesInfo);
                        $field              = $participantField->getField();

                        $procedureFieldArray[$participantCode] = $field->getParam('name');
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
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo   = $tempClassesStructure[$procedureCode];
            $procedure              = $this->constructProcedure($procedureClassesInfo['class']);
            $dataCombiningRules     = $procedure->getDataCombiningRules();
            $expectedRulesSet       = $procedureInfo['dataCombiningRules'];
            $getedRulesSet          = [];

            foreach ($dataCombiningRules->getKeys() as $participantField)
            {
                $participant        = $participantField->getParticipant();
                $participantCode    = $this->findProcedureCode($participant, $procedureClassesInfo);
                $field              = $participantField->getField();
                $fieldName          = $field->getParam('name');
                $weight             = $dataCombiningRules->get($participantField);

                if (!array_key_exists($participantCode, $getedRulesSet))
                {
                    $getedRulesSet[$participantCode] = [];
                }

                $getedRulesSet[$participantCode][$fieldName] = $weight;
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
     * construct procedure by class name
     *
     * @param   string $className                   procedure class name
     * @return  Procedure                           procedure
     ************************************************************************/
    private function constructProcedure(string $className) : Procedure
    {
        return new $className;
    }
    /** **********************************************************************
     * get participant code form object
     *
     * @param   Participant $participant            participant
     * @param   array       $procedureClassesInfo   procedure classes structure
     * @return  string                              participant code
     ************************************************************************/
    private function findProcedureCode(Participant $participant, array $procedureClassesInfo) : string
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