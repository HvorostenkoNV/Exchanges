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

        foreach ($this->generateProcedures() as $procedureCode => $procedureStructure)
        {
            $result[$procedureCode]                 = $procedureStructure;
            $result[$procedureCode]['participants'] = $this->generateParticipants();

            foreach ($result[$procedureCode]['participants'] as $participantCode => $participantStructure)
            {
                $result[$procedureCode]['participants'][$participantCode]['fields'] = $this->generateParticipantFields($participantCode);
            }

            $result[$procedureCode]['fields']               = $this->generateProcedureFields($result[$procedureCode]);
            $result[$procedureCode]                         = $this->correctParticipantFields($result[$procedureCode]);
            $result[$procedureCode]['dataMatchingRules']    = $this->generateDataMatchingRules($result[$procedureCode]);
            $result[$procedureCode]['dataCombiningRules']   = $this->generateDataCombiningRules($result[$procedureCode]);
        }

        $result = $this->addSharingIntoData($result);
        $result = $this->addIncorrectData($result);

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
        $proceduresCount    = rand(1, 10);
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
        $result                 = [];
        $participantsCount      = rand(0, 8);
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
        $result         = [];
        $fieldsTypes    = FieldsTypesManager::getFieldsTypes();
        $fieldsCount    = rand(0, 10);
        $fieldBaseName  = "UTPartField-$participantCode-";

        unset($fieldsTypes[self::$fieldIdType]);
        for ($index = 1; $index <= $fieldsCount; $index++)
        {
            $name = $fieldBaseName.$index;

            if (count($result) <= 0)
            {
                $result[$name] =
                    [
                        'name'      => $name,
                        'type'      => self::$fieldIdType,
                        'required'  => true
                    ];
            }
            else
            {
                $result[$name] =
                    [
                        'name'      => $name,
                        'type'      => array_rand($fieldsTypes),
                        'required'  => rand(1, 2) == 2
                    ];
            }
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
        $result             = [];
        $procedureCode      = $procedureStructure['code'];
        $fieldBaseName      = "UTPrField-$procedureCode-";
        $participantsFields = [];
        $fieldsCount        = rand(0, 20);

        foreach ($procedureStructure['participants'] as $participantCode => $participantStructure)
        {
            $participantAvailableFields = [];
            foreach ($participantStructure['fields'] as $participantFieldName => $participantFieldStructure)
            {
                if ($participantFieldStructure['type'] != self::$fieldIdType)
                {
                    $participantAvailableFields[$participantFieldName] = null;
                }
            }

            if (count($participantAvailableFields) > 0)
            {
                $participantsFields[$participantCode] = $participantAvailableFields;
            }
        }

        $fieldsCounter = 1;
        while ($fieldsCount > 0 && count($participantsFields) > 2)
        {
            $field                  = [];
            $fieldParticipantsCount = rand(2, count($participantsFields));
            $fieldParticipants      = array_rand($participantsFields, $fieldParticipantsCount);

            foreach ($fieldParticipants as $participantCode)
            {
                $participantFieldName       = array_rand($participantsFields[$participantCode]);
                $field[$participantCode]    = $participantFieldName;

                unset($participantsFields[$participantCode][$participantFieldName]);
                if (count($participantsFields[$participantCode]) <= 0)
                {
                    unset($participantsFields[$participantCode]);
                }
            }

            $result[$fieldBaseName.$fieldsCounter] = $field;
            $fieldsCount--;
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
        $result                 = [];
        $ruleBaseName           = 'UTMatchingRule-';
        $procedureFieldsGroups  = [];
        $rulesCount             = rand(0, 20);

        foreach ($procedureStructure['fields'] as $procedureFieldName => $procedureFieldStructure)
        {
            $procedureFieldSize = count($procedureFieldStructure);
            if (!array_key_exists($procedureFieldSize, $procedureFieldsGroups))
            {
                $procedureFieldsGroups[$procedureFieldSize] = [];
            }
            $procedureFieldsGroups[$procedureFieldSize][$procedureFieldName] = $procedureFieldStructure;
        }

        $fieldsCounter = 1;
        while ($rulesCount > 0 && count($procedureFieldsGroups) > 0)
        {
            $randProcedureFieldsGroupIndex  = array_rand($procedureFieldsGroups);
            $randProcedureFieldsGroup       = $procedureFieldsGroups[$randProcedureFieldsGroupIndex];
            $ruleFieldsCount                = rand(1, count($randProcedureFieldsGroup));
            $ruleFields                     = (array) array_rand($randProcedureFieldsGroup, $ruleFieldsCount);
            $availableParticipants          = [];

            foreach ($ruleFields as $procedureFieldName)
            {
                $procedureFieldParticipants = array_keys($randProcedureFieldsGroup[$procedureFieldName]);
                $availableParticipants      = array_merge($availableParticipants, $procedureFieldParticipants);

                unset($procedureFieldsGroups[$randProcedureFieldsGroupIndex][$procedureFieldName]);
                if (count($procedureFieldsGroups[$randProcedureFieldsGroupIndex]) <= 0)
                {
                    unset($procedureFieldsGroups[$randProcedureFieldsGroupIndex]);
                }
            }

            $availableParticipants  = array_unique($availableParticipants);
            $ruleParticipantsCount  = rand(2, count($availableParticipants));
            $ruleParticipants       = array_rand(array_flip($availableParticipants), $ruleParticipantsCount);

            $result[$ruleBaseName.$fieldsCounter] =
                [
                    'participants'  => $ruleParticipants,
                    'fields'        => $ruleFields
                ];
            $rulesCount--;
            $fieldsCounter++;
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

        foreach ($procedureStructure['participants'] as $participantCode => $participantStructure)
        {
            $fieldsWeight = [];

            foreach (array_keys($participantStructure['fields']) as $participantFieldName)
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
     * correct participants fields according procedure fields couples
     *
     * @param   array $procedureStructure   generated procedure structure
     * @return  array                       corrected procedure structure
     ************************************************************************/
    private function correctParticipantFields(array $procedureStructure) : array
    {
        $availableFieldsTypes = FieldsTypesManager::getFieldsTypes();
        unset($availableFieldsTypes[self::$fieldIdType]);
        unset($availableFieldsTypes['boolean']);

        foreach ($procedureStructure['fields'] as $procedureFieldName => $procedureFieldStructure)
        {
            $commonFieldType = array_rand($availableFieldsTypes);
            foreach ($procedureFieldStructure as $participantCode => $participantFieldName)
            {
                $procedureStructure['participants'][$participantCode]['fields'][$participantFieldName]['type'] = $commonFieldType;
            }
        }

        return $procedureStructure;
    }
    /** **********************************************************************
     * add some sharing into generated structure
     *
     * @param   array $structure            generated structure
     * @return  array                       supplemented generated structure
     * //TODO
     ************************************************************************/
    private function addSharingIntoData(array $structure) : array
    {
        return $structure;
    }
    /** **********************************************************************
     * add some incorrect data into generated structure
     *
     * @param   array $structure            generated structure
     * @return  array                       supplemented generated structure
     * //TODO
     ************************************************************************/
    private function addIncorrectData(array $structure) : array
    {
        return $structure;
    }
}