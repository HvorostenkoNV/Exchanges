<?php
declare(strict_types=1);

namespace UnitTests\TempDataGeneration;

use Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager;
/** ***********************************************************************************************
 * Class for creating project temp structure
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class StructureGenerator
{
    private static
        $generatedProceduresIndex   = 0,
        $generatedParticipantsIndex = 0;
    private
        $procedures                 = [],
        $participants               = [],
        $proceduresParticipants     = [],
        $participantsFields         = [],
        $proceduresFields           = [],
        $proceduresMatchingRules    = [],
        $proceduresCombiningRules   = [];
    /** **********************************************************************
     * generate project temp structure
     *
     * @return void
     ************************************************************************/
    public function generate() : void
    {
        $proceduresData     = $this->generateProcedures();
        $participantsData   = $this->generateParticipants();
        $proceduresCode     = array_keys($proceduresData);
        $participantsCode   = array_keys($participantsData);

        $this->procedures               = $proceduresData;
        $this->participants             = $participantsData;
        $this->proceduresParticipants   = $this->distributeParticipantsToProcedures($proceduresCode, $participantsCode);

        foreach ($participantsCode as $participantCode)
        {
            $this->participantsFields[$participantCode] = $this->generateParticipantFields();
        }

        foreach ($proceduresCode as $procedureCode)
        {
            $participantsFields = [];
            foreach ($this->proceduresParticipants[$procedureCode] as $participantCode)
            {
                $participantsFields[$participantCode] = $this->participantsFields[$participantCode];
            }

            $fields         = $this->generateProcedureFields($participantsFields);
            $matchingRules  = $this->generateProcedureMatchingRules($fields);
            $combiningRules = $this->generateProcedureCombiningRules($participantsFields);

            $this->proceduresFields[$procedureCode]         = $fields;
            $this->proceduresMatchingRules[$procedureCode]  = $matchingRules;
            $this->proceduresCombiningRules[$procedureCode] = $combiningRules;
        }
    }
    /** **********************************************************************
     * get generated procedures
     *
     * @return  array                       generated procedures structure
     * @example
     *  [
     *      procedureCode   => procedureStructure,
     *      procedureCode   => procedureStructure,
     *  ]
     ************************************************************************/
    public function getProcedures() : array
    {
        return $this->procedures;
    }
    /** **********************************************************************
     * get generated participants
     *
     * @return  string[]                    generated participants structure
     * @example
     *  [
     *      participantCode => participantStructure,
     *      participantCode => participantStructure
     *  ]
     ************************************************************************/
    public function getParticipants() : array
    {
        return $this->participants;
    }
    /** **********************************************************************
     * get generated procedure participants
     *
     * @param   string $procedureCode       procedure code
     * @return  string[]                    generated procedure participants
     ************************************************************************/
    public function getProcedureParticipants(string $procedureCode) : array
    {
        return array_key_exists($procedureCode, $this->proceduresParticipants)
            ? $this->proceduresParticipants[$procedureCode]
            : [];
    }
    /** **********************************************************************
     * get generated participants fields
     *
     * @param   string $participantCode     participant code
     * @return  array                       participants fields structure
     * @example
     *  [
     *      participantFieldName    => participantFieldStructure,
     *      participantFieldName    => participantFieldStructure
     *  ]
     ************************************************************************/
    public function getParticipantFields(string $participantCode) : array
    {
        return array_key_exists($participantCode, $this->participantsFields)
            ? $this->participantsFields[$participantCode]
            : [];
    }
    /** **********************************************************************
     * get generated procedure fields
     *
     * @param   string $procedureCode       procedure code
     * @return  array                       procedure fields structure
     * @example
     *  [
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ],
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ]
     *  ]
     ************************************************************************/
    public function getProcedureFields(string $procedureCode) : array
    {
        return array_key_exists($procedureCode, $this->proceduresFields)
            ? $this->proceduresFields[$procedureCode]
            : [];
    }
    /** **********************************************************************
     * get temp generated procedure matching rules structure
     *
     * @param   string $procedureCode       procedure code
     * @return  array                       procedure matching rules structure
     * @example
     *  [
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ],
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ]
     *  ]
     ************************************************************************/
    public function getProcedureMatchingRules(string $procedureCode) : array
    {
        return array_key_exists($procedureCode, $this->proceduresMatchingRules)
            ? $this->proceduresMatchingRules[$procedureCode]
            : [];
    }
    /** **********************************************************************
     * get generated procedure combining rules
     *
     * @param   string  $procedureCode      procedure code
     * @return  array                       procedure combining rules structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ]
     *  ]
     ************************************************************************/
    public function getProcedureCombiningRules(string $procedureCode) : array
    {
        return array_key_exists($procedureCode, $this->proceduresCombiningRules)
            ? $this->proceduresCombiningRules[$procedureCode]
            : [];
    }
    /** **********************************************************************
     * generate procedures structure
     *
     * @return  array                       procedures structure
     * @example
     *  [
     *      procedureCode   => procedureStructure,
     *      procedureCode   => procedureStructure
     *  ]
     ************************************************************************/
    private function generateProcedures() : array
    {
        $result         = [];
        $count          = rand(20, 30);
        $nameTemplate   = 'Temp Procedure - {INDEX}';
        $codeTemplate   = 'TempProcedure{INDEX}';

        for ($index = $count; $index > 0; $index--)
        {
            self::$generatedProceduresIndex++;

            $name   = str_replace('{INDEX}', self::$generatedProceduresIndex, $nameTemplate);
            $code   = str_replace('{INDEX}', self::$generatedProceduresIndex, $codeTemplate);

            $result[$code] =
                [
                    'name'      => $name,
                    'code'      => $code,
                    'activity'  => rand(1, 2) == 2
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * generate participants structure
     *
     * @return  array                       participants structure
     * @example
     *  [
     *      participantCode => participantStructure,
     *      participantCode => participantStructure
     *  ]
     ************************************************************************/
    private function generateParticipants() : array
    {
        $result         = [];
        $count          = rand(30, 50);
        $nameTemplate   = 'Temp Participant - {INDEX}';
        $codeTemplate   = 'TempParticipant{INDEX}';

        for ($index = $count; $index > 0; $index--)
        {
            self::$generatedParticipantsIndex++;

            $name   = str_replace('{INDEX}', self::$generatedParticipantsIndex, $nameTemplate);
            $code   = str_replace('{INDEX}', self::$generatedParticipantsIndex, $codeTemplate);

            $result[$code] =
                [
                    'name'  => $name,
                    'code'  => $code
                ];
        }

        return $result;
    }
    /** **********************************************************************
     * distribute participants to procedures
     *
     * @param   string[]    $procedures     generated procedures code array
     * @param   string[]    $participants   generated participants code array
     * @return  array                       distributed participants to procedures
     * @example
     *  [
     *      procedureCode   => [participantCode, participantCode],
     *      procedureCode   => [participantCode, participantCode]
     *  ]
     ************************************************************************/
    private function distributeParticipantsToProcedures(array $procedures, array $participants) : array
    {
        $result                             = [];
        $availableProcedures                = array_flip($procedures);
        $availableParticipants              = array_flip($participants);
        $emptyProceduresCount               = (int) ceil(count($availableProcedures) / 10);
        $proceduresWithOneParticipantCount  = (int) ceil(count($availableProcedures) / 10);
        $repeatedParticipantsCount          = (int) ceil(count($availableParticipants) / 10);

        foreach ($availableProcedures as $procedureCode => $value)
        {
            $procedureParticipantsCount = rand(2, 6);
            $procedureParticipants      = count($availableParticipants) >= $procedureParticipantsCount
                ? array_rand($availableParticipants, $procedureParticipantsCount)
                : [];
            $result[$procedureCode]     = $procedureParticipants;
        }

        $emptyProcedures = count($availableProcedures) > $emptyProceduresCount
            ? (array) array_rand($availableProcedures, $emptyProceduresCount)
            : [];
        foreach ($emptyProcedures as $procedureCode)
        {
            $result[$procedureCode] = [];
            unset($availableProcedures[$procedureCode]);
        }

        $proceduresWithOneParticipant = count($availableProcedures) > $proceduresWithOneParticipantCount
            ? (array) array_rand($availableProcedures, $proceduresWithOneParticipantCount)
            : [];
        foreach ($proceduresWithOneParticipant as $procedureCode)
        {
            $procedureParticipants  = $result[$procedureCode];
            $randomParticipant      = $procedureParticipants[array_rand($procedureParticipants)];
            $result[$procedureCode] = [$randomParticipant];
            unset($availableProcedures[$procedureCode]);
        }

        $repeatedParticipants = count($availableParticipants) > $repeatedParticipantsCount
            ? (array) array_rand($availableParticipants, $repeatedParticipantsCount)
            : [];
        foreach ($repeatedParticipants as $participantCode)
        {
            $proceduresGroupSize    = rand(2, 4);
            $proceduresGroup        = count($availableProcedures) >= $proceduresGroupSize
                ? array_rand($availableProcedures, $proceduresGroupSize)
                : [];

            foreach ($proceduresGroup as $procedureCode)
            {
                if (!in_array($participantCode, $result[$procedureCode]))
                {
                    $indexToChange = array_rand($result[$procedureCode]);
                    $result[$procedureCode][$indexToChange] = $participantCode;
                    unset($availableProcedures[$procedureCode]);
                }
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate participants fields structure
     *
     * @return  array                       participants fields structure
     * @example
     *  [
     *      participantFieldName    => participantFieldStructure,
     *      participantFieldName    => participantFieldStructure
     *  ]
     ************************************************************************/
    private function generateParticipantFields() : array
    {
        if (rand(1, 20) == 20)
        {
            return [];
        }

        $result         = [];
        $nameTemplate   = 'Field-{TYPE}-{INDEX}';
        $hasIdField     = rand(1, 20) != 20;

        foreach (FieldsTypesManager::getAvailableFieldsTypes() as $type)
        {
            $isIdField      = $type == FieldsTypesManager::ID_FIELD_TYPE;
            $fieldsCount    = $isIdField ? 1 : rand(0, 4);

            if ($isIdField && !$hasIdField)
            {
                continue;
            }

            for ($index = $fieldsCount; $index > 0; $index--)
            {
                $name = str_replace(['{TYPE}', '{INDEX}'], [$type, $index], $nameTemplate);
                $result[$name] =
                    [
                        'name'      => $name,
                        'type'      => $type,
                        'required'  => $isIdField ? true : rand(1, 2) == 2
                    ];
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate procedure fields structure
     *
     * @param   array $participantsFields   participants fields structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldStructure,
     *              participantFieldName    => participantFieldStructure
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldStructure,
     *              participantFieldName    => participantFieldStructure
     *          ]
     *  ]
     * @return  array                       procedure fields structure
     * @example
     *  [
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ],
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ]
     *  ]
     ************************************************************************/
    private function generateProcedureFields(array $participantsFields) : array
    {
        if (rand(1, 30) == 30)
        {
            return [];
        }

        $result                     = [];
        $nameTemplate               = 'Field-{INDEX}';
        $fieldsCount                = rand(5, 10);
        $groupedParticipantsFields  = $this->groupParticipantsFieldsByType($participantsFields);

        foreach ($groupedParticipantsFields as $fieldType => $fieldsCouples)
        {
            if (count($fieldsCouples) < 2 || $fieldType == FieldsTypesManager::ID_FIELD_TYPE)
            {
                unset($groupedParticipantsFields[$fieldType]);
            }
        }

        while ($fieldsCount > 0 && count($groupedParticipantsFields) > 0)
        {
            $randomFieldType    = array_rand($groupedParticipantsFields);
            $participantsCount  = rand(2, count($groupedParticipantsFields[$randomFieldType]));
            $randomParticipants = array_rand($groupedParticipantsFields[$randomFieldType], $participantsCount);
            $procedureField     = [];

            foreach ($randomParticipants as $randomParticipantCode)
            {
                if (!array_key_exists($randomFieldType, $groupedParticipantsFields))
                {
                    continue;
                }

                $randomParticipantFieldIndex            = array_rand($groupedParticipantsFields[$randomFieldType][$randomParticipantCode]);
                $procedureField[$randomParticipantCode] = $groupedParticipantsFields[$randomFieldType][$randomParticipantCode][$randomParticipantFieldIndex];

                unset($groupedParticipantsFields[$randomFieldType][$randomParticipantCode][$randomParticipantFieldIndex]);
                if (count($groupedParticipantsFields[$randomFieldType][$randomParticipantCode]) <= 0)
                {
                    unset($groupedParticipantsFields[$randomFieldType][$randomParticipantCode]);
                }
                if (count($groupedParticipantsFields[$randomFieldType]) < 2)
                {
                    unset($groupedParticipantsFields[$randomFieldType]);
                }
            }

            if (count($procedureField) >= 2)
            {
                $fieldName          = str_replace('{INDEX}', count($result) + 1, $nameTemplate);
                $result[$fieldName] = $procedureField;
                $fieldsCount--;
            }
        }
echo'<pre>';
print_r($result);
echo'<pre>';
        return $result;
    }
    /** **********************************************************************
     * get generated procedure matching rules
     *
     * @param   array $procedureFields      procedure fields structure
     * @example
     *  [
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ],
     *      procedureFieldName  =>
     *          [
     *              participantCode => participantFieldName,
     *              participantCode => participantFieldName
     *          ]
     *  ]
     * @return  array                       procedure matching rules structure
     * @example
     *  [
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ],
     *      [
     *          'participants'  => [participantCode, participantCode],
     *          'fields'        => [procedureField, procedureField]
     *      ]
     *  ]
     ************************************************************************/
    private function generateProcedureMatchingRules(array $procedureFields) : array
    {
        if (rand(1, 40) == 40)
        {
            return [];
        }

        $result     = [];
        $rulesCount = rand(5, 10);

        while ($rulesCount > 0 && count($procedureFields) > 0)
        {
            $ruleProcedureFieldsCount   = rand(1, count($procedureFields) >= 4 ? 4 : count($procedureFields));
            $ruleProcedureFields        = (array) array_rand($procedureFields, $ruleProcedureFieldsCount);
            $ruleAvailableParticipants  = [];
            $ruleParticipants           = [];

            foreach ($ruleProcedureFields as $procedureFieldName)
            {
                $procedureFieldParticipants = array_keys($procedureFields[$procedureFieldName]);
                $ruleAvailableParticipants  = array_merge($ruleAvailableParticipants, $procedureFieldParticipants);
            }

            foreach ($ruleAvailableParticipants as $participantCode)
            {
                $participantExistInAllFields = true;
                foreach ($ruleProcedureFields as $procedureFieldName)
                {
                    if (!array_key_exists($participantCode, $procedureFields[$procedureFieldName]))
                    {
                        $participantExistInAllFields = false;
                        break;
                    }
                }

                if ($participantExistInAllFields)
                {
                    $ruleParticipants[] = $participantCode;
                }
            }

            $ruleParticipants = array_unique($ruleParticipants);
            if (count($ruleParticipants) >= 2)
            {
                $ruleParticipantsCount  = rand(2, count($ruleParticipants));
                $ruleParticipants       = array_slice($ruleParticipants, 0, $ruleParticipantsCount);

                $result[] =
                    [
                        'participants'  => $ruleParticipants,
                        'fields'        => $ruleProcedureFields
                    ];

                foreach ($ruleProcedureFields as $procedureFieldName)
                {
                    unset($procedureFields[$procedureFieldName]);
                }
                $rulesCount--;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get generated procedure combining rules
     *
     * @param   array $participantsFields   participants fields structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldStructure,
     *              participantFieldName    => participantFieldStructure
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldStructure,
     *              participantFieldName    => participantFieldStructure
     *          ]
     *  ]
     * @return  array                       procedure combining rules structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => weight,
     *              participantFieldName    => weight
     *          ]
     *  ]
     ************************************************************************/
    private function generateProcedureCombiningRules(array $participantsFields) : array
    {
        if (rand(1, 30) == 30)
        {
            return [];
        }

        $result = [];

        foreach ($participantsFields as $participantCode => $participantFields)
        {
            if (count($participantFields) <= 0 || rand(1, 10) == 10)
            {
                continue;
            }

            foreach ($participantFields as $participantFieldName => $participantFieldStructure)
            {
                if (rand(1, 10) == 10 || $participantFieldStructure['type'] == FieldsTypesManager::ID_FIELD_TYPE)
                {
                    continue;
                }

                if (!array_key_exists($participantCode, $result))
                {
                    $result[$participantCode] = [];
                }
                $result[$participantCode][$participantFieldName] = rand(0, 100);
            }
        }

        return $result;
    }
    /** **********************************************************************
     * get participants fields grouped by type
     *
     * @param   array $participantsFields   participants fields structure
     * @example
     *  [
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldStructure,
     *              participantFieldName    => participantFieldStructure
     *          ],
     *      participantCode =>
     *          [
     *              participantFieldName    => participantFieldStructure,
     *              participantFieldName    => participantFieldStructure
     *          ]
     *  ]
     * @return  array                       participants fields grouped by type
     * @example
     *  [
     *      fieldType   =>
     *          [
     *              participantCode => [participantFieldName, participantFieldName],
     *              participantCode => [participantFieldName, participantFieldName]
     *          ],
     *      fieldType   =>
     *          [
     *              participantCode => [participantFieldName, participantFieldName],
     *              participantCode => [participantFieldName, participantFieldName]
     *          ]
     *  ]
     ************************************************************************/
    private function groupParticipantsFieldsByType(array $participantsFields) : array
    {
        $result = [];

        foreach ($participantsFields as $participantCode => $participantFields)
        {
            foreach ($participantFields as $participantFieldName => $participantFieldStructure)
            {
                $fieldType = $participantFieldStructure['type'];
                if (!array_key_exists($fieldType, $result))
                {
                    $result[$fieldType] = [];
                }
                if (!array_key_exists($participantCode, $result[$fieldType]))
                {
                    $result[$fieldType][$participantCode] = [];
                }
                $result[$fieldType][$participantCode][] = $participantFieldName;
            }
        }

        return $result;
    }
}