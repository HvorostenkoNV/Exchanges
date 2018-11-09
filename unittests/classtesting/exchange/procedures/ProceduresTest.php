<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
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
     *
     * @return void
     * @throws
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$structureGenerator = new TempStructureGenerator;
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
     * check getting procedure code
     *
     * @test
     * @return void
     * @throws
     ************************************************************************/
    public function gettingCode() : void
    {
        $tempStructure          = self::$structureGenerator->getStructure();
        $tempClassesStructure   = self::$structureGenerator->getClassesStructure();

        foreach ($tempStructure as $procedureCode => $procedureInfo)
        {
            $procedureClassesInfo       = $tempClassesStructure[$procedureCode];
            $procedure                  = $this->constructProcedure($procedureClassesInfo['class']);
            $procedureCode              = $procedure->getCode();

            self::assertNotEmpty
            (
                $procedureCode,
                'Expect procedure provides not empty code'
            );
        }
    }
    /** **********************************************************************
     * check getting procedures participants
     *
     * @test
     * @return void
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
                $participant = $participantsSet->current();
                $currentParticipantsSet[] = $participant->getCode();
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
     * @return void
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
                    $participantField       = $procedureField->current();
                    $participant            = $participantField->getParticipant();
                    $participantCode        = $participant->getCode();
                    $participantFieldName   = $participantField->getField()->getParam('name');

                    $procedureFieldArray[$participantCode] = $participantFieldName;
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
     * @return void
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
                    $participant = $participantSet->current();
                    $rule['participants'][] = $participant->getCode();
                    $participantSet->next();
                }

                while ($fieldSet->valid())
                {
                    $procedureField         = $fieldSet->current();
                    $procedureFieldArray    = [];

                    $procedureField->rewind();
                    while ($procedureField->valid())
                    {
                        $participantField       = $procedureField->current();
                        $participant            = $participantField->getParticipant();
                        $participantCode        = $participant->getCode();
                        $participantFieldName   = $participantField->getField()->getParam('name');

                        $procedureFieldArray[$participantCode] = $participantFieldName;
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
     * @return void
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
                $participant            = $participantField->getParticipant();
                $participantCode        = $participant->getCode();
                $participantFieldName   = $participantField->getField()->getParam('name');
                $weight                 = $dataCombiningRules->get($participantField);

                if (!array_key_exists($participantCode, $getedRulesSet))
                {
                    $getedRulesSet[$participantCode] = [];
                }

                $getedRulesSet[$participantCode][$participantFieldName] = $weight;
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
}