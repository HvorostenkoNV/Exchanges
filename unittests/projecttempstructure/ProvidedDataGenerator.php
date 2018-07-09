<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;

use RuntimeException;
/** ***********************************************************************************************
 * Class for creating project temp provided data
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ProvidedDataGenerator
{
    private static
        $fieldIdType        = 'item-id';
    private
        $dbRecordsGenerator = null,
        $data               = [],
        $matchedData        = [],
        $usedRandomValues   = [];
    /** **********************************************************************
     * constructor
     ************************************************************************/
    public function __construct()
    {
        $this->dbRecordsGenerator = new DBRecordsGenerator;
    }
    /** **********************************************************************
     * generate provided data
     *
     * @param   array   $structure              generated logic structure
     * @param   array   $dbStructure            generated DB structure
     * @throws  RuntimeException                DB writing error
     ************************************************************************/
    public function generate(array $structure, array $dbStructure) : void
    {
        try
        {
            foreach ($structure as $procedureCode => $procedureStructure)
            {
                $procedureStructure['dataMatchingRules']    = $this->getCleanDataMatchingRules($procedureStructure);
                $procedureDbStructure                       = array_key_exists($procedureCode, $dbStructure) ? $dbStructure[$procedureCode] : [];
                $alreadyMatchedData                         = $this->generateAlreadyMatchedData($procedureStructure, $procedureDbStructure);
                $ableToMatchData                            = $this->generateAbleToMatchData($procedureStructure);
                $mixedMatchedData                           = $this->generateMixedMatchedData($procedureStructure, $procedureDbStructure);

                $this->data[$procedureCode]         = [];
                $this->matchedData[$procedureCode]  = [];
                foreach (array_keys($procedureStructure['participants']) as $participantCode)
                {
                    $this->data[$procedureCode][$participantCode] = [];
                }

                foreach (array_merge($alreadyMatchedData, $ableToMatchData, $mixedMatchedData) as $item)
                {
                    $this->matchedData[$procedureCode][] = $item;
                    foreach ($item as $participantCode => $participantItem)
                    {
                        $this->data[$procedureCode][$participantCode][] = $participantItem;
                    }
                }

                $aloneData = $this->generateAloneData($procedureStructure);
                foreach ($aloneData as $participantCode => $items)
                {
                    $availableData = $this->data[$procedureCode][$participantCode];
                    $this->data[$procedureCode][$participantCode] = array_merge($availableData, $items);
                }
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get generated data
     *
     * @return  array                           generated data
     ************************************************************************/
    public function getData() : array
    {
        return $this->data;
    }
    /** **********************************************************************
     * get generated matched data
     *
     * @return  array                           generated matched data
     ************************************************************************/
    public function getMatchedData() : array
    {
        return $this->matchedData;
    }
    /** **********************************************************************
     * clean temp data
     ************************************************************************/
    public function clean() : void
    {
        $this->dbRecordsGenerator->clean();
    }
    /** **********************************************************************
     * generate already matched in DB data
     *
     * @param   array   $procedureStructure     procedure logic structure
     * @param   array   $procedureDbStructure   procedure DB structure
     * @return  array                           matched data
     * @example
     * [
     *      [
     *          participantCode => participantData,
     *          participantCode => participantData
     *      ],
     *      [
     *          participantCode => participantData,
     *          participantCode => participantData
     *      ]
     * ]
     * @throws  RuntimeException                DB writing error
     ************************************************************************/
    private function generateAlreadyMatchedData(array $procedureStructure, array $procedureDbStructure) : array
    {
        $result                 = [];
        $availableParticipants  = [];
        $couplesCount           = rand(0, 20);

        foreach ($procedureStructure['participants'] as $participantCode => $participantStructure)
        {
            $participantIdFieldName = $this->findParticipantIdField($participantStructure['fields']);
            if (count($participantStructure['fields']) > 0 && !is_null($participantIdFieldName))
            {
                $availableParticipants[$participantCode] = null;
            }
        }

        if (count($availableParticipants) < 2)
        {
            return $result;
        }

        while ($couplesCount > 0)
        {
            $participantsCount  = rand(2, count($availableParticipants));
            $participants       = array_rand($availableParticipants, $participantsCount);
            $matchedItems       = [];
            $matchedItemsId     = [];

            foreach ($participants as $participantCode)
            {
                $participantFields      = $procedureStructure['participants'][$participantCode]['fields'];
                $participantIdFieldName = $this->findParticipantIdField($participantFields);

                $matchedItems[$participantCode]     = $this->generateItemData($participantFields);
                $matchedItemsId[$participantCode]   = $matchedItems[$participantCode][$participantIdFieldName];
            }

            try
            {
                $this->writeMatchedItemsIntoDb($procedureDbStructure, $matchedItemsId);
            }
            catch (RuntimeException $exception)
            {
                throw $exception;
            }

            $result[] = $matchedItems;
            $couplesCount--;
        }

        return $result;
    }
    /** **********************************************************************
     * generate able to match data
     *
     * @param   array $procedureStructure       procedure structure
     * @return  array                           able to match data
     * @example
     * [
     *      [
     *          participantCode => participantData,
     *          participantCode => participantData
     *      ],
     *      [
     *          participantCode => participantData,
     *          participantCode => participantData
     *      ]
     * ]
     ************************************************************************/
    private function generateAbleToMatchData(array $procedureStructure) : array
    {
        $result = [];

        foreach ($procedureStructure['dataMatchingRules'] as $rule)
        {
            $couplesCount = rand(0, 10);
            while ($couplesCount > 0)
            {
                $matchedItems       = [];
                $sameValues         = [];
                $randomParticipant  = array_rand($rule);

                foreach ($rule[$randomParticipant] as $participantFieldName)
                {
                    $participantField = $procedureStructure['participants'][$randomParticipant]['fields'][$participantFieldName];
                    $sameValues[] = $this->generateFieldRandomValue($participantField['type'], true);
                }

                foreach ($rule as $participantCode => $ruleParticipantFields)
                {
                    $participantFields              = $procedureStructure['participants'][$participantCode]['fields'];
                    $matchedItems[$participantCode] = $this->generateItemData($participantFields);

                    foreach ($ruleParticipantFields as $index => $participantFieldName)
                    {
                        $matchedItems[$participantCode][$participantFieldName] = $sameValues[$index];
                    }
                }

                $result[] = $matchedItems;
                $couplesCount--;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate mixed matched data, already matched into DB with able to match data
     *
     * @param   array   $procedureStructure     procedure logic structure
     * @param   array   $procedureDbStructure   procedure DB structure
     * @return  array                           matched data
     * @example
     * [
     *      [
     *          participantCode => participantData,
     *          participantCode => participantData
     *      ],
     *      [
     *          participantCode => participantData,
     *          participantCode => participantData
     *      ]
     * ]
     * @throws  RuntimeException                DB writing error
     ************************************************************************/
    private function generateMixedMatchedData(array $procedureStructure, array $procedureDbStructure) : array
    {
        $result = [];

        foreach ($procedureStructure['dataMatchingRules'] as $rule)
        {
            $couplesCount = rand(0, 10);
            while ($couplesCount > 0)
            {
                $matchedItems                   = [];
                $sameValues                     = [];
                $availableParticipants          = array_keys($rule);
                $writtenIntoDbItemsCount        = rand(1, count($rule) - 1);
                $otherItemsCount                = rand(1, count($rule) - $writtenIntoDbItemsCount);
                $writtenIntoDbItemsParticipants = [];
                $otherItemsParticipants         = [];
                $matchedItemsId                 = [];

                for ($index = $writtenIntoDbItemsCount; $index > 0; $index--)
                {
                    $writtenIntoDbItemsParticipants[] = array_pop($availableParticipants);
                }
                for ($index = $otherItemsCount; $index > 0; $index--)
                {
                    $otherItemsParticipants[] = array_pop($availableParticipants);
                }

                $randomParticipant = $writtenIntoDbItemsParticipants[0];
                foreach ($rule[$randomParticipant] as $participantFieldName)
                {
                    $participantField = $procedureStructure['participants'][$randomParticipant]['fields'][$participantFieldName];
                    $sameValues[] = $this->generateFieldRandomValue($participantField['type'], true);
                }

                foreach (array_merge($writtenIntoDbItemsParticipants, $otherItemsParticipants) as $participantCode)
                {
                    $ruleParticipantFields          = $rule[$participantCode];
                    $participantFields              = $procedureStructure['participants'][$participantCode]['fields'];
                    $participantIdFieldName         = $this->findParticipantIdField($participantFields);
                    $matchedItems[$participantCode] = $this->generateItemData($participantFields);

                    if (in_array($participantCode, $writtenIntoDbItemsParticipants))
                    {
                        $matchedItemsId[$participantCode] = $matchedItems[$participantCode][$participantIdFieldName];
                    }
                    foreach ($ruleParticipantFields as $index => $participantFieldName)
                    {
                        $matchedItems[$participantCode][$participantFieldName] = $sameValues[$index];
                    }
                }

                try
                {
                    $this->writeMatchedItemsIntoDb($procedureDbStructure, $matchedItemsId);
                }
                catch (RuntimeException $exception)
                {
                    throw $exception;
                }

                $result[] = $matchedItems;
                $couplesCount--;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate data with no matches
     *
     * @param   array $procedureStructure       procedure structure
     * @return  array                           data with no matches
     * @example
     * [
     *      participantCode => [participantData, participantData],
     *      participantCode => [participantData, participantData]
     * ]
     ************************************************************************/
    private function generateAloneData(array $procedureStructure) : array
    {
        $result = [];

        foreach ($procedureStructure['participants'] as $participantCode => $participantStructure)
        {
            $itemsCount = rand(0, 20);
            while ($itemsCount > 0)
            {
                if (!array_key_exists($participantCode, $result))
                {
                    $result[$participantCode] = [];
                }

                $result[$participantCode][] = $this->generateItemData($participantStructure['fields']);
                $itemsCount--;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * write matched items into DB
     *
     * @param   array   $procedureDbStructure        procedure DB structure
     * @param   array   $matchedItems           matched items
     * @throws  RuntimeException                DB writing error
     ************************************************************************/
    private function writeMatchedItemsIntoDb(array $procedureDbStructure, array $matchedItems) : void
    {
        try
        {
            $matchedItemId = $this->dbRecordsGenerator->generateRecord('matched_items',
                [
                    'PROCEDURE' => $procedureDbStructure['id']
                ]);

            foreach ($matchedItems as $participantCode => $itemId)
            {
                $participantId = $procedureDbStructure['participants'][$participantCode]['id'];
                $this->dbRecordsGenerator->generateRecord('matched_items_participants',
                    [
                        'PROCEDURE_ITEM'        => $matchedItemId,
                        'PARTICIPANT'           => $participantId,
                        'PARTICIPANT_ITEM_ID'   => $itemId
                    ]);
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }
    }
    /** **********************************************************************
     * get clean data matching rules
     *
     * @param   array $procedureStructure       procedure structure
     * @return  array                           clean data matching rules
     * @example
     * [
     *      [
     *          participantCode => [participantFieldName, participantFieldName],
     *          participantCode => [participantFieldName, participantFieldName]
     *      ],
     *      [
     *          participantCode => [participantFieldName, participantFieldName],
     *          participantCode => [participantFieldName, participantFieldName]
     *      ]
     * ]
     ************************************************************************/
    private function getCleanDataMatchingRules(array $procedureStructure) : array
    {
        $result = [];

        foreach ($procedureStructure['dataMatchingRules'] as $rule)
        {
            $newRule = [];

            foreach ($rule['fields'] as $procedureFieldName)
            {
                $procedureFieldStructure = $procedureStructure['fields'][$procedureFieldName];
                foreach ($procedureFieldStructure as $participantCode => $participantFieldName)
                {
                    $participantFields      = $procedureStructure['participants'][$participantCode]['fields'];
                    $participantIdFieldName = $this->findParticipantIdField($participantFields);
                    if (!in_array($participantCode, $rule['participants']) || is_null($participantIdFieldName))
                    {
                        unset($procedureFieldStructure[$participantCode]);
                    }
                }
                if (count($procedureFieldStructure) >= 2)
                {
                    foreach ($procedureFieldStructure as $participantCode => $participantFieldName)
                    {
                        if (!array_key_exists($participantCode, $newRule))
                        {
                            $newRule[$participantCode] = [];
                        }
                        $newRule[$participantCode][] = $participantFieldName;
                    }
                }
            }

            $participantFieldsMaxCounts = 0;
            foreach ($newRule as $participantFields)
            {
                $participantFieldsCount = count($participantFields);
                if ($participantFieldsMaxCounts == 0 || $participantFieldsMaxCounts < $participantFieldsCount)
                {
                    $participantFieldsMaxCounts = $participantFieldsCount;
                }
            }
            foreach ($newRule as $participantCode => $participantFields)
            {
                if (count($participantFields) != $participantFieldsMaxCounts)
                {
                    unset($newRule[$participantCode]);
                }
            }

            if (count($newRule) >= 2)
            {
                $result[] = $newRule;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate item data
     *
     * @param   array $fields                   participant fields Structure
     * @return  array                           item data
     ************************************************************************/
    private function generateItemData(array $fields) : array
    {
        $result = [];

        foreach ($fields as $field)
        {
            if ($field['required'] || rand(1, 2) == 2)
            {
                $result[$field['name']] = $this->generateFieldRandomValue($field['type']);
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate field random value
     *
     * @param   string  $type                   field type
     * @param   boolean $notEmpty               return not empty value
     * @return  mixed                           random value
     ************************************************************************/
    private function generateFieldRandomValue(string $type, bool $notEmpty = false)
    {
        $uniqueNonAvailableFields   = ['boolean'];
        $returnEmptyResult          = !$notEmpty && rand(1, 4) == 4;
        $returnUniqueResult         = !in_array($type, $uniqueNonAvailableFields) && !$returnEmptyResult;
        $value                      = null;

        switch ($type)
        {
            case self::$fieldIdType:
                $value = rand(1, getrandmax());
                break;
            case 'string':
                $value = $returnEmptyResult
                    ? ''
                    : 'randomValue'.rand(1, getrandmax());
                break;
            case 'number':
                $value = $returnEmptyResult
                    ? 0
                    : rand(1, getrandmax());
                break;
            case 'boolean':
                $value = rand(1, 2) == 2;
                break;
            case 'array-of-strings':
                $randomSize = $returnEmptyResult ? 0 : rand(1, 25);
                $value      = [];

                for ($index = $randomSize; $index > 0; $index--)
                {
                    $value[] = 'randomValue'.rand(1, getrandmax());
                }
                break;
            case 'array-of-numbers':
                $randomSize = $returnEmptyResult ? 0 : rand(1, 25);
                $value      = [];

                for ($index = $randomSize; $index > 0; $index--)
                {
                    $value[] = rand(1, getrandmax());
                }
                break;
            case 'array-of-booleans':
                $randomSize = $returnEmptyResult ? 0 : rand(1, 25);
                $value      = [];

                for ($index = $randomSize; $index > 0; $index--)
                {
                    $value[] = rand(1, 2) == 2;
                }
                break;
            default:
                $value = '';
        }

        $valueHash = json_encode($value);
        if ($returnUniqueResult && array_key_exists($valueHash, $this->usedRandomValues))
        {
            $value = $this->generateFieldRandomValue($type, true);
        }
        $this->usedRandomValues[$valueHash] = null;

        return $value;
    }
    /** **********************************************************************
     * find participant ID field
     *
     * @param   array $participantFields        procedure structure
     * @return  string|null                     participant ID field name
     ************************************************************************/
    private function findParticipantIdField(array $participantFields) : ?string
    {
        foreach ($participantFields as $participantFieldName => $participantFieldStructure)
        {
            if ($participantFieldStructure['type'] == self::$fieldIdType)
            {
                return $participantFieldName;
            }
        }

        return null;
    }
}