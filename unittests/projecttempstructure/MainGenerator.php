<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use
    RuntimeException,
    ReflectionException,
    PDO,
    SplFileInfo,
    ReflectionClass,
    Main\Exchange\Participants\AbstractParticipant,
    Main\Exchange\Procedures\AbstractProcedure;
/** ***********************************************************************************************
 * Class for creating project temp structure
 * creates temp structure, DB records, classes and XML files for imitating provided data
 * using in UNIT-testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class MainGenerator
{
    private static
        $tempProceduresIndex    = 1,
        $tempParticipantsIndex  = 1;
    private
        $pdo                        = null,
        $tempDBGenerator            = null,
        $tempClassesGenerator       = null,
        $tempXmlGenerator           = null,
        $proceduresParentClass      = '',
        $participantsParentClass    = '',
        $fieldsTypes                = [],
        $tempStructure              = [],
        $participantsTempData       = [],
        $participantsXmlFiles       = [];
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        $this->pdo                  = $this->getPDO();
        $this->tempDBGenerator      = new DBGenerator;
        $this->tempClassesGenerator = new ClassesGenerator;
        $this->tempXmlGenerator     = new XmlGenerator;
        $this->fieldsTypes          = $this->getFieldsTypes();
    }
    /** **********************************************************************
     * generate project temp structure
     * @throws  RuntimeException                    generating error
     ************************************************************************/
    public function generate() : void
    {
        $this->clean();

        try
        {
            $this->tempStructure = $this->generateTempStructure();
            $this->generateTempDbRecords($this->tempStructure);
            $this->generateTempClasses($this->tempStructure);

            foreach ($this->tempStructure as $procedureInfo)
            {
                foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
                {
                    $data   = $this->generateTempProvidedData($participantInfo);
                    $xml    = $this->tempXmlGenerator->createXml($data);

                    $this->participantsTempData[$participantName]   = $data;
                    $this->participantsXmlFiles[$participantName]   = $xml;
                }
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * set procedure parent class
     *
     * @param   string $className                   procedure parent class
     ************************************************************************/
    public function setProcedureParentClass(string $className) : void
    {
        $this->proceduresParentClass = $className;
    }
    /** **********************************************************************
     * set participant parent class
     *
     * @param   string $className                   participant parent class
     ************************************************************************/
    public function setParticipantParentClass(string $className) : void
    {
        $this->participantsParentClass = $className;
    }
    /** **********************************************************************
     * get generated temp structure
     *
     * @return  array                               seeded structure
     ************************************************************************/
    public function getStructure() : array
    {
        return $this->tempStructure;
    }
    /** **********************************************************************
     * get participant temp data
     *
     * @param   string $participantName             participant name
     * @return  array                               participant temp data
     ************************************************************************/
    public function getParticipantData(string $participantName) : array
    {
        return array_key_exists($participantName, $this->participantsTempData)
            ? $this->participantsTempData[$participantName]
            : [];
    }
    /** **********************************************************************
     * get participant generated temp XML
     *
     * @param   string $participantName             participant name
     * @return  SplFileInfo|null                    participant generated temp XML
     ************************************************************************/
    public function getParticipantXml(string $participantName) : ?SplFileInfo
    {
        return array_key_exists($participantName, $this->participantsXmlFiles)
            ? $this->participantsXmlFiles[$participantName]
            : null;
    }
    /** **********************************************************************
     * clean temp structure
     ************************************************************************/
    public function clean() : void
    {
        $this->tempStructure        = [];
        $this->participantsTempData = [];
        $this->participantsXmlFiles = [];

        $this->tempDBGenerator->dropTempChanges();
        $this->tempClassesGenerator->clean();
        $this->tempXmlGenerator->clean();
    }
    /** **********************************************************************
     * get PDO statement
     *
     * @return  PDO                                 PDO connection statement
     ************************************************************************/
    private function getPDO() : PDO
    {
        $host       = $GLOBALS['DB_HOST'];
        $name       = $GLOBALS['DB_NAME'];
        $login      = $GLOBALS['DB_LOGIN'];
        $password   = $GLOBALS['DB_PASSWORD'];

        return new PDO
        (
            "mysql:dbname=$name;host=$host",
            $login,
            $password
        );
    }
    /** **********************************************************************
     * get participants fields types
     *
     * @return  array                               participants fields types
     ************************************************************************/
    private function getFieldsTypes() : array
    {
        $result         = [];
        $preparedQuery  = $this->pdo->prepare('SELECT * FROM fields_types');

        $preparedQuery->execute();

        $queryResult = $preparedQuery->fetchAll(PDO::FETCH_ASSOC);
        foreach ($queryResult as $item)
        {
            $result[$item['CODE']] = $item['ID'];
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp structure
     *
     * @return  array                               temp structure
     ************************************************************************/
    private function generateTempStructure() : array
    {
        $result = [];

        foreach ($this->generateTempStructureProcedures() as $procedure)
        {
            $procedureName                  = $procedure['name'];
            $procedure['participants']      = [];
            $procedure['fields']            = [];
            $procedure['dataMatchingRules'] = [];
            $result[$procedureName]         = $procedure;

            foreach ($this->generateTempStructureParticipants() as $participant)
            {
                $participantName                                            = $participant['name'];
                $participant['fields']                                      = [];
                $result[$procedureName]['participants'][$participantName]   = $participant;

                foreach ($this->generateTempStructureParticipantFields() as $field)
                {
                    $result[$procedureName]['participants'][$participantName]['fields'][$field['name']] = $field;
                }
            }

            foreach ($this->generateTempStructureProceduresFields($result[$procedureName]['participants']) as $index => $field)
            {
                $result[$procedureName]['fields']['field-'.($index + 1)] = $field;
            }

            foreach ($this->generateTempStructureDataMatchingRules($result[$procedureName]) as $index => $rule)
            {
                $result[$procedureName]['dataMatchingRules']['rule-'.($index + 1)] = $rule;
            }

            $result[$procedureName]['dataCombiningRules'] = $this->generateTempStructureDataCombiningRules($result[$procedureName]);
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp structure procedures
     *
     * @return  array                               temp structure procedures
     ************************************************************************/
    private function generateTempStructureProcedures() : array
    {
        $proceduresCount    = rand(3, 10);
        $procedureBaseName  = 'UnitTestTempProcedure';
        $result             = [];

        for ($index = $proceduresCount; $index > 0; $index--)
        {
            $result[] =
            [
                'name'      => $procedureBaseName.self::$tempProceduresIndex,
                'activity'  => rand(0, 1) === 1
            ];
            self::$tempProceduresIndex++;
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp structure participants
     *
     * @return  array                               temp structure participants
     ************************************************************************/
    private function generateTempStructureParticipants() : array
    {
        if (rand(0, 4) === 4)
        {
            return [];
        }

        $participantsCount      = rand(2, 5);
        $participantBaseName    = 'UnitTestTempParticipant';
        $result                 = [];

        for ($index = $participantsCount; $index > 0; $index--)
        {
            $result[] =
            [
                'name' => $participantBaseName.self::$tempParticipantsIndex
            ];
            self::$tempParticipantsIndex++;
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp structure participants fields
     *
     * @return  array                               temp structure participants fields
     ************************************************************************/
    private function generateTempStructureParticipantFields() : array
    {
        if (rand(0, 4) === 4)
        {
            return [];
        }

        $fieldsCount    = rand(1, 5);
        $fieldBaseName  = 'UnitTestTempParticipantField';
        $result         = [];

        for ($index = 1; $index <= $fieldsCount; $index++)
        {
            $result[] =
            [
                'name'      => $fieldBaseName.$index,
                'type'      => array_rand($this->fieldsTypes),
                'required'  => rand(0, 1) === 1
            ];
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp structure procedure fields
     *
     * @param   array $participantsInfo             generated temp participants info
     * @return  array                               temp structure procedure fields
     ************************************************************************/
    private function generateTempStructureProceduresFields(array $participantsInfo = []) : array
    {
        foreach ($participantsInfo as $index => $participant)
        {
            if (count($participant['fields']) <= 0)
            {
                unset($participantsInfo[$index]);
            }
        }

        if (count($participantsInfo) < 2 || rand(0, 9) === 9)
        {
            return [];
        }

        $result         = [];
        $fieldsCount    = rand(5, 15);

        while ($fieldsCount > 0 && count($participantsInfo) >= 2)
        {
            $field                  = [];
            $fieldParticipantsCount = rand(2, count($participantsInfo));
            $fieldParticipants      = array_rand($participantsInfo, $fieldParticipantsCount);

            foreach ($fieldParticipants as $participant)
            {
                $participantField = array_rand($participantsInfo[$participant]['fields']);
                unset($participantsInfo[$participant]['fields'][$participantField]);

                if (count($participantsInfo[$participant]['fields']) <= 0)
                {
                    unset($participantsInfo[$participant]);
                }

                $field[$participant] = $participantField;
            }

            $result[] = $field;
            $fieldsCount--;
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp structure data matching rules
     *
     * @param   array $procedureInfo                generated temp procedure info
     * @return  array                               temp structure data matching rules
     ************************************************************************/
    private function generateTempStructureDataMatchingRules(array $procedureInfo = []) : array
    {
        $participants   = array_flip(array_keys($procedureInfo['participants']));
        $fields         = array_flip(array_keys($procedureInfo['fields']));
        $rulesCount     = rand(10, 20);
        $result         = [];

        if (count($participants) < 2 || count($fields) <= 0 || rand(0, 9) === 9)
        {
            return [];
        }

        for ($index = 1; $index <= $rulesCount; $index++)
        {
            $ruleParticipantsCount  = rand(2, count($participants));
            $ruleFieldsCount        = rand(1, count($fields));
            $ruleParticipants       = array_rand($participants, $ruleParticipantsCount);
            $ruleFields             = array_rand($fields, $ruleFieldsCount);

            $result[] = json_encode
            ([
                'participants'  => $ruleParticipants,
                'fields'        => is_array($ruleFields) ? $ruleFields : [$ruleFields]
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
     * generate temp structure data combining rules
     *
     * @param   array $procedureInfo                generated temp procedure info
     * @return  array                               temp structure data combining rules
     ************************************************************************/
    private function generateTempStructureDataCombiningRules(array $procedureInfo = []) : array
    {
        $result = [];

        foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
        {
            $fieldsWeight = [];

            foreach (array_keys($participantInfo['fields']) as $fieldName)
            {
                if (rand(0, 3) != 3)
                {
                    $fieldsWeight[$fieldName] = rand(0, 100);
                }
            }

            if (count($fieldsWeight) > 0)
            {
                $result[$participantName] = $fieldsWeight;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp DB records
     *
     * @param   array $structure                    structure seeding based on
     ************************************************************************/
    private function generateTempDbRecords(array $structure) : void
    {
        $createdProcedures = $this->generateTempDbProcedures($structure);

        foreach ($structure as $procedureName => $procedureInfo)
        {
            $procedureId                = $createdProcedures[$procedureName];
            $createdParticipants        = $this->generateTempDbParticipants($procedureId, $procedureInfo);
            $createdParticipantsFields  = $this->generateTempDbParticipantsFields($createdParticipants, $procedureInfo);
            $createdProcedureFields     = $this->generateTempDbProcedureFields($procedureId, $createdParticipantsFields, $procedureInfo);
            $this->generateTempDbMatchingRules($procedureId, $createdParticipants, $createdProcedureFields, $procedureInfo);
            $this->generateTempDbCombiningRules($procedureId, $createdParticipantsFields, $procedureInfo);
        }
    }
    /** **********************************************************************
     * generate temp DB procedures
     *
     * @param   array $structure                    structure
     * @return  array                               created procedures
     * @example
     * [
     *      procedureName   => procedureId,
     *      procedureName   => procedureId
     * ]
     ************************************************************************/
    private function generateTempDbProcedures(array $structure) : array
    {
        $result = [];

        foreach ($structure as $procedureName => $procedureInfo)
        {
            $procedureId = $this->tempDBGenerator->createTempRecord('procedures',
            [
                'NAME'      => $procedureInfo['name'],
                'ACTIVITY'  => $procedureInfo['activity'] ? 'Y' : 'N'
            ]);

            $result[$procedureName] = $procedureId;
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp DB participants
     *
     * @param   int     $procedureId                procedure ID
     * @param   array   $procedureInfo              procedure structure
     * @return  array                               created participants
     * @example
     * [
     *      participantName => participantId,
     *      participantName => participantId
     * ]
     ************************************************************************/
    private function generateTempDbParticipants(int $procedureId, array $procedureInfo) : array
    {
        $result = [];

        foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
        {
            $participantId = $this->tempDBGenerator->createTempRecord('participants',
            [
                'NAME' => $participantName
            ]);

            $this->tempDBGenerator->createTempRecord('procedures_participants',
            [
                'PROCEDURE'     => $procedureId,
                'PARTICIPANT'   => $participantId
            ]);

            $result[$participantName] = $participantId;
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp DB participants fields
     *
     * @param   array   $createdParticipants        created participants, NAME => ID
     * @param   array   $procedureInfo              procedure structure
     * @return  array                               created participants fields
     * @example
     * [
     *      participantName =>
     *      [
     *          fieldName   => fieldId,
     *          fieldName   => fieldId
     *      ],
     *      participantName =>
     *      [
     *          fieldName   => fieldId,
     *          fieldName   => fieldId
     *      ]
     * ]
     ************************************************************************/
    private function generateTempDbParticipantsFields(array $createdParticipants, array $procedureInfo) : array
    {
        $result = [];

        foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
        {
            $participantId                          = $createdParticipants[$participantName];
            $participantsFields[$participantName]   = [];

            foreach ($participantInfo['fields'] as $fieldName => $fieldInfo)
            {
                $fieldId = $this->tempDBGenerator->createTempRecord('participants_fields',
                [
                    'NAME'          => $fieldInfo['name'],
                    'TYPE'          => $this->fieldsTypes[$fieldInfo['type']],
                    'IS_REQUIRED'   => $fieldInfo['required'] ? 'Y' : 'N',
                    'PARTICIPANT'   => $participantId
                ]);

                $result[$participantName][$fieldName] = $fieldId;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp DB fields
     *
     * @param   int     $procedureId                procedure ID
     * @param   array   $createdParticipantsFields  created participants fields
     * @param   array   $procedureInfo              procedure structure
     * @return  array                               created fields
     * @example
     * [
     *      fieldName   => fieldId,
     *      fieldName   => fieldId
     * ]
     ************************************************************************/
    private function generateTempDbProcedureFields(int $procedureId, array $createdParticipantsFields, array $procedureInfo) : array
    {
        $result = [];

        foreach ($procedureInfo['fields'] as $fieldName => $fieldInfo)
        {
            $procedureFieldId = $this->tempDBGenerator->createTempRecord('procedures_fields',
            [
                'PROCEDURE' => $procedureId
            ]);

            foreach ($fieldInfo as $participantName => $participantFieldName)
            {
                $participantFieldId = $createdParticipantsFields[$participantName][$participantFieldName];
                $this->tempDBGenerator->createTempRecord('procedures_participants_fields',
                [
                    'PROCEDURE_FIELD'   => $procedureFieldId,
                    'PARTICIPANT_FIELD' => $participantFieldId
                ]);
            }

            $result[$fieldName] = $procedureFieldId;
        }

        return $result;
    }
    /** **********************************************************************
     * generate temp DB rules
     *
     * @param   int     $procedureId                procedure ID
     * @param   array   $createdParticipants        created participants
     * @param   array   $createdProcedureFields     created procedure fields
     * @param   array   $procedureInfo              procedure structure
     ************************************************************************/
    private function generateTempDbMatchingRules(int $procedureId, array $createdParticipants, array $createdProcedureFields, array $procedureInfo) : void
    {
        foreach ($procedureInfo['dataMatchingRules'] as $rule)
        {
            $ruleId = $this->tempDBGenerator->createTempRecord('procedures_data_matching_rules',
            [
                'PROCEDURE' => $procedureId
            ]);

            foreach ($rule['participants'] as $participant)
            {
                $this->tempDBGenerator->createTempRecord('procedures_data_matching_rules_participants',
                [
                    'PARTICIPANT'   => $createdParticipants[$participant],
                    'RULE'          => $ruleId
                ]);
            }

            foreach ($rule['fields'] as $field)
            {
                $this->tempDBGenerator->createTempRecord('procedures_data_matching_rules_fields',
                [
                    'PROCEDURE_FIELD'   => $createdProcedureFields[$field],
                    'RULE'              => $ruleId
                ]);
            }
        }
    }
    /** **********************************************************************
     * generate temp DB rules
     *
     * @param   int     $procedureId                procedure ID
     * @param   array   $createdParticipantsFields  created participants fields
     * @param   array   $procedureInfo              procedure structure
     ************************************************************************/
    private function generateTempDbCombiningRules(int $procedureId, array $createdParticipantsFields, array $procedureInfo) : void
    {
        foreach ($procedureInfo['dataCombiningRules'] as $participantName => $fields)
        {
            foreach ($fields as $fieldName => $weight)
            {
                $fieldId = $createdParticipantsFields[$participantName][$fieldName];
                $this->tempDBGenerator->createTempRecord('procedures_data_combining_rules',
                [
                    'PROCEDURE'         => $procedureId,
                    'PARTICIPANT_FIELD' => $fieldId,
                    'WEIGHT'            => $weight
                ]);
            }
        }
    }
    /** **********************************************************************
     * generate temp classes
     *
     * @param   array $structure                    structure seeding based on
     * @throws  RuntimeException                    temp classes generating error
     ************************************************************************/
    private function generateTempClasses(array $structure) : void
    {
        try
        {
            $abstractProcedureReflection    = new ReflectionClass(AbstractProcedure::class);
            $abstractParticipantReflection  = new ReflectionClass(AbstractParticipant::class);
            $abstractProcedureNamespace     = $abstractProcedureReflection->getNamespaceName();
            $abstractParticipantNamespace   = $abstractParticipantReflection->getNamespaceName();
            $parentProcedure                = strlen($this->proceduresParentClass)      ? $this->proceduresParentClass      : AbstractProcedure::class;
            $parentParticipant              = strlen($this->participantsParentClass)    ? $this->participantsParentClass    : AbstractParticipant::class;

            foreach ($structure as $procedureName => $procedureInfo)
            {
                $this->tempClassesGenerator->create("$abstractProcedureNamespace\\$procedureName", $parentProcedure);

                foreach ($procedureInfo['participants'] as $participantName => $participantInfo)
                {
                    $this->tempClassesGenerator->create("$abstractParticipantNamespace\\$participantName", $parentParticipant);
                }
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
        catch (ReflectionException $exception)
        {
            throw new RuntimeException($exception->getMessage());
        }
    }
    /** **********************************************************************
     * generate participant temp provided data
     *
     * @param   array $participantInfo              participant generated temp data
     * @return  array                               participant temp provided data
     ************************************************************************/
    private function generateTempProvidedData(array $participantInfo) : array
    {
        $result     = [];
        $itemsCount = rand(5, 15);

        for ($index = $itemsCount; $index > 0; $index--)
        {
            $item = [];

            foreach ($participantInfo['fields'] as $field)
            {
                if ($field['required'] || rand(0, 1) === 0)
                {
                    $item[$field['name']] = $this->generateFieldRandomValue($field['type']);
                }
            }

            $result[] = $item;
        }

        return $result;
    }
    /** **********************************************************************
     * generate field random value
     *
     * @param   string $type                        field type
     * @return  mixed                               random value
     ************************************************************************/
    private function generateFieldRandomValue(string $type)
    {
        $returnEmptyResult = rand(0, 3) === 3;

        switch ($type)
        {
            case 'item-id':
                return rand(1, getrandmax());
            case 'string':
                return $returnEmptyResult
                    ? ''
                    : 'randomValue'.rand(1, getrandmax());
            case 'number':
                return $returnEmptyResult
                    ? 0
                    : rand(1, getrandmax());
            case 'boolean':
                return rand(0, 1) == 1;
            case 'array-of-strings':
                if ($returnEmptyResult)
                {
                    return [];
                }

                $randomSize = rand(1, 15);
                $result     = [];

                for ($index = $randomSize; $index > 0; $index--)
                {
                    $result[] = 'randomValue'.rand(1, getrandmax());
                }

                return $result;
            case 'array-of-numbers':
                if ($returnEmptyResult)
                {
                    return [];
                }

                $randomSize = rand(1, 15);
                $result     = [];

                for ($index = $randomSize; $index > 0; $index--)
                {
                    $result[] = rand(1, getrandmax());
                }

                return $result;
            case 'array-of-booleans':
                if ($returnEmptyResult)
                {
                    return [];
                }

                $randomSize = rand(1, 15);
                $result     = [];

                for ($index = $randomSize; $index > 0; $index--)
                {
                    $result[] = rand(0, 1) == 1;
                }

                return $result;
            default:
                return '';
        }
    }
}