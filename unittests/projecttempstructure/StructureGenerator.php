<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;
/** ***********************************************************************************************
 * Class for creating project temp logic structure
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class StructureGenerator
{
    private static
        $proceduresIndex    = 1,
        $participantsIndex  = 1,
        $fieldIdType        = 'item-id';
    /** **********************************************************************
     * generate logic structure
     *
     * @return  array                       generated logic structure
     ************************************************************************/
    public function generate() : array
    {
        $result = [];

        foreach ($this->generateProcedures() as $procedureCode => $procedureInfo)
        {
            $result[$procedureCode]                 = $procedureInfo;
            $result[$procedureCode]['participants'] = $this->generateParticipants();

            foreach ($result[$procedureCode]['participants'] as $participantCode => $participantInfo)
            {
                $result[$procedureCode]['participants'][$participantCode]['fields'] = $this->generateParticipantFields($participantCode);
            }

            $result[$procedureCode]['fields']               = $this->generateProcedureFields($result[$procedureCode]);
            $result[$procedureCode]                         = $this->correctParticipantFields($result[$procedureCode]);
            $result[$procedureCode]['dataMatchingRules']    = $this->generateDataMatchingRules($result[$procedureCode]);
            $result[$procedureCode]['dataCombiningRules']   = $this->generateDataCombiningRules($result[$procedureCode]);
        }

        return $result;
    }
    /** **********************************************************************
     * generate procedures
     *
     * @return  array                       generated procedures
     ************************************************************************/
    private function generateProcedures() : array
    {
        $result             = [];
        $proceduresCount    = rand(3, 10);
        $procedureBaseName  = 'UT Procedure - ';
        $procedureBaseCode  = 'UTProcedure';

        for ($index = $proceduresCount; $index > 0; $index--)
        {
            $name   = $procedureBaseName.self::$proceduresIndex;
            $code   = $procedureBaseCode.self::$proceduresIndex;

            $result[$code] =
            [
                'name'      => $name,
                'code'      => $code,
                'activity'  => rand(1, 2) == 2
            ];

            self::$proceduresIndex++;
        }

        return $result;
    }
    /** **********************************************************************
     * generate participants
     *
     * @return  array                       generated participants
     ************************************************************************/
    private function generateParticipants() : array
    {
        if (rand(1, 5) == 5)
        {
            return [];
        }

        $result                 = [];
        $participantsCount      = rand(2, 5);
        $participantBaseName    = 'UT Participant - ';
        $participantBaseCode    = 'UTParticipant';

        for ($index = $participantsCount; $index > 0; $index--)
        {
            $name   = $participantBaseName.self::$participantsIndex;
            $code   = $participantBaseCode.self::$participantsIndex;

            $result[$code] =
            [
                'name'  => $name,
                'code'  => $code
            ];

            self::$participantsIndex++;
        }

        return $result;
    }
    /** **********************************************************************
     * generate participants fields
     *
     * @param   string $participantCode     participants code
     * @return  array                       generated participants fields
     ************************************************************************/
    private function generateParticipantFields(string $participantCode) : array
    {
        if (rand(1, 5) == 5)
        {
            return [];
        }

        $result         = [];
        $fieldsCount    = rand(0, 5);
        $fieldBaseName  = "UTPartField-$participantCode-";
        $fieldsTypes    = FieldsTypesManager::getFieldsTypes();
        $idFieldName    = $fieldBaseName.'ID';

        $result[$idFieldName] =
        [
            'name'      => $idFieldName,
            'type'      => self::$fieldIdType,
            'required'  => true
        ];

        for ($index = 1; $index <= $fieldsCount; $index++)
        {
            $name = $fieldBaseName.$index;
            $result[$name] =
            [
                'name'      => $name,
                'type'      => array_rand($fieldsTypes),
                'required'  => rand(1, 2) == 2
            ];
        }

        return $result;
    }
    /** **********************************************************************
     * generate procedure fields
     *
     * @param   array $procedureStructure   generated procedure structure
     * @return  array                       generated procedure fields
     ************************************************************************/
    private function generateProcedureFields(array $procedureStructure = []) : array
    {
        foreach ($procedureStructure['participants'] as $participantCode => $participantInfo)
        {
            foreach ($participantInfo['fields'] as $fieldName => $fieldInfo)
            {
                if ($fieldInfo['type'] == self::$fieldIdType)
                {
                    unset($procedureStructure['participants'][$participantCode]['fields'][$fieldName]);
                }
            }

            if (count($procedureStructure['participants'][$participantCode]['fields']) <= 0)
            {
                unset($procedureStructure['participants'][$participantCode]);
            }
        }

        if (count($procedureStructure['participants']) < 2 || rand(1, 10) == 10)
        {
            return [];
        }

        $result         = [];
        $procedureCode  = $procedureStructure['code'];
        $fieldBaseName  = "UTPrField-$procedureCode-";
        $fieldsCounter  = 1;
        $fieldsCount    = rand(5, 15);

        while ($fieldsCount > 0 && count($procedureStructure['participants']) > 2)
        {
            $field                  = [];
            $fieldParticipantsCount = rand(2, count($procedureStructure['participants']));
            $fieldParticipants      = array_rand($procedureStructure['participants'], $fieldParticipantsCount);

            foreach ($fieldParticipants as $participantCode)
            {
                $participantFieldName       = array_rand($procedureStructure['participants'][$participantCode]['fields']);
                $field[$participantCode]    = $participantFieldName;

                unset($procedureStructure['participants'][$participantCode]['fields'][$participantFieldName]);
                if (count($procedureStructure['participants'][$participantCode]['fields']) <= 0)
                {
                    unset($procedureStructure['participants'][$participantCode]);
                }
            }

            $result[$fieldBaseName.$fieldsCounter] = $field;
            $fieldsCounter++;
        }

        return $result;
    }
    /** **********************************************************************
     * generate data matching rules
     *
     * @param   array $procedureStructure   generated procedure structure
     * @return  array                       generated data matching rules
     ************************************************************************/
    private function generateDataMatchingRules(array $procedureStructure = []) : array
    {
        $result         = [];
        $participants   = array_flip(array_keys($procedureStructure['participants']));
        $fields         = array_flip(array_keys($procedureStructure['fields']));
        $ruleBaseName   = 'UTMatchingRule-';
        $rulesCount     = rand(10, 20);

        if (count($participants) < 2 || count($fields) <= 0 || rand(1, 10) == 10)
        {
            return [];
        }

        for ($index = 1; $index <= $rulesCount; $index++)
        {
            $ruleParticipantsCount  = rand(2, count($participants));
            $ruleFieldsCount        = rand(1, count($fields));
            $ruleParticipants       = array_rand($participants, $ruleParticipantsCount);
            $ruleFields             = array_rand($fields, $ruleFieldsCount);

            $result[$ruleBaseName.$index] = json_encode
            ([
                'participants'  => $ruleParticipants,
                'fields'        => (array) $ruleFields
            ]);
        }

        $result = array_unique($result);
        foreach ($result as $index => $value)
        {
            $result[$index] = json_decode($value, true);
        }

        return $result;
    }
    /** **********************************************************************
     * generate data combining rules
     *
     * @param   array $procedureStructure   generated procedure structure
     * @return  array                       generated data combining rules
     ************************************************************************/
    private function generateDataCombiningRules(array $procedureStructure = []) : array
    {
        $result = [];

        foreach ($procedureStructure['participants'] as $participantCode => $participantInfo)
        {
            $fieldsWeight = [];

            foreach (array_keys($participantInfo['fields']) as $participantFieldName)
            {
                if (rand(1, 4) != 4)
                {
                    $fieldsWeight[$participantFieldName] = rand(0, 100);
                }
            }

            if (count($fieldsWeight) > 0)
            {
                $result[$participantCode] = $fieldsWeight;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * correct participants fields according to matching rules
     *
     * @param   array $procedureStructure   generated procedure structure
     * @return  array                       corrected procedure structure
     ************************************************************************/
    private function correctParticipantFields(array $procedureStructure) : array
    {
        foreach ($procedureStructure['fields'] as $procedureFieldName => $procedureField)
        {
            $randomParticipantCode      = array_keys($procedureField)[0];
            $randomParticipantFieldName = $procedureField[$randomParticipantCode];
            $commonFieldType            = $procedureStructure['participants'][$randomParticipantCode]['fields'][$randomParticipantFieldName]['type'];

            foreach ($procedureField as $participantCode => $participantFieldName)
            {
                $procedureStructure['participants'][$participantCode]['fields'][$participantFieldName]['type'] = $commonFieldType;
            }
        }

        return $procedureStructure;
    }
}