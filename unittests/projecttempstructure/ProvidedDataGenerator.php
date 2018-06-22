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
        $idFieldType    = 'item-id';
    private
        $dbRecordsGenerator = null,
        $data               = [],
        $matchedData        = [];
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
     * @param   array $structure                generated logic structure
     * @param   array $dbStructure              generated DB structure
     * @throws  RuntimeException                DB writing error
     ************************************************************************/
    public function generate(array $structure, array $dbStructure) : void
    {
        try
        {
            foreach ($structure as $procedureCode => $procedureInfo)
            {
                $procedureDbInfo    = array_key_exists($procedureCode, $dbStructure) ? $dbStructure[$procedureCode] : [];
                $matchedData        = $this->generateMatchedData($procedureInfo, $procedureDbInfo);
                $ableToMatchData    = $this->generateAbleToMatchData($procedureInfo);

                $this->data[$procedureCode]         = [];
                $this->matchedData[$procedureCode]  = [];
                foreach ($procedureInfo['participants'] as $participantCode => $participantInfo)
                {
                    $this->data[$procedureCode][$participantCode] = [];
                }

                foreach (array_merge($matchedData, $ableToMatchData) as $item)
                {
                    $this->matchedData[$procedureCode][] = $item;
                    foreach ($item as $participantCode => $participantItem)
                    {
                        $this->data[$procedureCode][$participantCode][] = $participantItem;
                    }
                }

                $aloneData = $this->generateAloneData($procedureInfo);
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
     * generate matched data
     *
     * @param   array   $procedureStructure     procedure logic structure
     * @param   array   $procedureDbInfo        procedure DB structure
     * @return  array                           matched data
     * @throws  RuntimeException                DB writing error
     ************************************************************************/
    private function generateMatchedData(array $procedureStructure, array $procedureDbInfo) : array
    {
        foreach ($procedureStructure['participants'] as $participantCode => $participantInfo)
        {
            if (count($participantInfo['fields']) <= 0)
            {
                unset($procedureStructure['participants'][$participantCode]);
            }
        }

        if (count($procedureStructure['participants']) < 2)
        {
            return [];
        }

        $result         = [];
        $couplesCount   = rand(0, 10);

        try
        {
            while ($couplesCount > 0)
            {
                $participantsCount  = rand(2, count($procedureStructure['participants']));
                $participants       = array_rand($procedureStructure['participants'], $participantsCount);
                $matchedItems       = [];
                $matchedItemsId     = [];

                foreach ($participants as $participantCode)
                {
                    $participantFields              = $procedureStructure['participants'][$participantCode]['fields'];
                    $matchedItems[$participantCode] = $this->generateItemData($participantFields);

                    foreach ($matchedItems[$participantCode] as $participantFieldName => $value)
                    {
                        if ($participantFields[$participantFieldName]['type'] == self::$idFieldType)
                        {
                            $matchedItemsId[$participantCode] = $value;
                            break;
                        }
                    }
                }

                $this->writeMatchedItemsIntoDb($procedureDbInfo, $matchedItemsId);
                $result[] = $matchedItems;
                $couplesCount--;
            }
        }
        catch (RuntimeException $exception)
        {
            throw $exception;
        }

        return $result;
    }
    /** **********************************************************************
     * generate able to match data
     *
     * @param   array $procedureStructure       procedure structure
     * @return  array                           able to match data
     ************************************************************************/
    private function generateAbleToMatchData(array $procedureStructure) : array
    {
        $result = [];

        foreach ($procedureStructure['dataMatchingRules'] as $rule)
        {
            $couplesCount = rand(0, 3);

            while ($couplesCount > 0)
            {
                $matchedItems = [];

                foreach ($rule['participants'] as $participantCode)
                {
                    $participantFields = $procedureStructure['participants'][$participantCode]['fields'];
                    $matchedItems[$participantCode] = $this->generateItemData($participantFields);
                }

                foreach ($rule['fields'] as $procedureFieldName)
                {
                    $procedureField         = $procedureStructure['fields'][$procedureFieldName];
                    $randomParticipant      = array_keys($procedureField)[0];
                    $randomParticipantField = $procedureField[$randomParticipant];
                    $participantField       = $procedureStructure['participants'][$randomParticipant]['fields'][$randomParticipantField];
                    $value                  = $this->generateFieldRandomValue($participantField['type'], true);

                    foreach ($procedureField as $participantCode => $participantFieldName)
                    {
                        $matchedItems[$participantCode][$participantFieldName] = $value;
                    }
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
     ************************************************************************/
    private function generateAloneData(array $procedureStructure) : array
    {
        $result = [];

        foreach ($procedureStructure['participants'] as $participantCode => $participantInfo)
        {
            $itemsCount                 = rand(0, 20);
            $result[$participantCode]   = [];

            while ($itemsCount > 0)
            {
                $result[$participantCode][] = $this->generateItemData($participantInfo['fields']);
                $itemsCount--;
            }
        }

        return $result;
    }
    /** **********************************************************************
     * generate item data
     *
     * @param   array $fields               participant fields info
     * @return  array                       item data
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
     * @param   string  $type               field type
     * @param   boolean $notEmpty           return not empty value
     * @return  mixed                       random value
     ************************************************************************/
    private function generateFieldRandomValue(string $type, bool $notEmpty = false)
    {
        $returnEmptyResult = rand(1, 4) == 4 && !$notEmpty;

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
                return rand(1, 2) == 2;
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
                    $result[] = rand(1, 2) == 2;
                }

                return $result;
            default:
                return '';
        }
    }
    /** **********************************************************************
     * write matched items into DB
     *
     * @param   array   $procedureDbInfo    procedure DB structure
     * @param   array   $matchedItems       matched items
     * @throws  RuntimeException            DB writing error
     ************************************************************************/
    private function writeMatchedItemsIntoDb(array $procedureDbInfo, array $matchedItems) : void
    {
        try
        {
            $matchedItemId = $this->dbRecordsGenerator->generateRecord('matched_items',
            [
                'PROCEDURE' => $procedureDbInfo['id']
            ]);

            foreach ($matchedItems as $participantCode => $itemId)
            {
                $participantId = $procedureDbInfo['participants'][$participantCode]['id'];
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
}