<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    DomainException,
    RuntimeException,
    InvalidArgumentException,
    SplFileInfo,
    Main\Helpers\MarkupData\XML,
    Main\Exchange\Participants\AbstractParticipant,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\DataForDelivery,
    Main\Exchange\Participants\Fields\FieldsSet;
/** ***********************************************************************************************
 * Application participant Users1C
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ParticipantForUnitTest extends AbstractParticipant
{
    /** @var SplFileInfo */
    public $xmlWithProvidedData = null;
    /** @var SplFileInfo */
    public $xmlForDelivery      = null;
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @param   FieldsSet $fields           participant fields set
     * @return  ProvidedData                data
     ************************************************************************/
    protected function readProvidedData(FieldsSet $fields) : ProvidedData
    {
        $result = new ProvidedData;
        $data   = null;

        try
        {
            $xml    = new XML($this->xmlWithProvidedData);
            $data   = $xml->read();
        }
        catch (RuntimeException $exception)
        {
            return $result;
        }

        foreach ($data as $item)
        {
            if (!is_array($item))
            {
                continue;
            }

            try
            {
                $map = new ItemData;
                foreach ($item as $key => $value)
                {
                    $field = $fields->findField($key);
                    $map->set($field, $value);
                }
                $result->push($map);
            }
            catch (InvalidArgumentException $exception)
            {

            }
        }

        return $result;
    }
    /** **********************************************************************
     * provide delivered data to the participant
     *
     * @param   DataForDelivery $data       data to write
     * @return  bool                        process result
     ************************************************************************/
    protected function provideDataForDelivery(DataForDelivery $data) : bool
    {
        $dataArray  = [];
        $xml        = new XML($this->xmlForDelivery);

        try
        {
            while (!$data->isEmpty())
            {
                $item       = $data->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $field)
                {
                    $fieldName              = $field->getParam('name');
                    $value                  = $item->get($field);
                    $validatedValue         = $field->getFieldType()->convertValueForPrint($value);
                    $itemArray[$fieldName]  = $validatedValue;
                }

                $dataArray[] = $itemArray;
            }
        }
        catch (RuntimeException $exception)
        {

        }
        catch (DomainException $exception)
        {

        }
        catch (InvalidArgumentException $exception)
        {

        }

        return $xml->write($dataArray);
    }
}