<?php
declare(strict_types=1);

namespace UnitTests\ProjectTempStructure;
/** ***********************************************************************************************
 * Class for creating project temp combined data
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class CombinedDataGenerator
{
    /** **********************************************************************
     * generate combined data
     *
     * @param   array   $structure              generated logic structure
     * @param   array   $providedStructure      generated provided structure
     * @return  array                           generated combined data
     ************************************************************************/
    public function generate(array $structure, array $providedStructure) : array
    {
        $result = [];

        foreach ($structure as $procedureCode => $procedureStructure)
        {
            $result[$procedureCode] = [];
            $procedureProvidedData  = array_key_exists($procedureCode, $providedStructure)
                ? $providedStructure[$procedureCode]
                : [];

            foreach ($procedureProvidedData as $itemsGroup)
            {
                $itemData = [];

                foreach ($procedureStructure['fields'] as $procedureFieldName => $procedureFieldStructure)
                {
                    $values = [];
                    foreach ($procedureFieldStructure as $participantCode => $participantFieldName)
                    {
                        if (!array_key_exists($participantCode, $itemsGroup) || !array_key_exists($participantFieldName, $itemsGroup[$participantCode]))
                        {
                            continue;
                        }

                        $value  = $itemsGroup[$participantCode][$participantFieldName];
                        $weight =
                            array_key_exists($participantCode, $procedureStructure['dataCombiningRules']) &&
                            array_key_exists($participantFieldName, $procedureStructure['dataCombiningRules'][$participantCode])
                                ? (int) $procedureStructure['dataCombiningRules'][$participantCode][$participantFieldName]
                                : 0;

                        $values[$weight] = $value;
                    }

                    $maxWeight  = count($values) > 0                    ? max(array_keys($values))  : 0;
                    $finalValue = array_key_exists($maxWeight, $values) ? $values[$maxWeight]       : null;

                    $itemData[$procedureFieldName] = $finalValue;
                }

                if (count($itemData) > 0)
                {
                    $result[$procedureCode][] = $itemData;
                }
            }
        }

        return $result;
    }
}