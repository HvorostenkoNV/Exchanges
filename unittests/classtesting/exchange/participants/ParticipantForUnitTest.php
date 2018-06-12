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
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Application participant Users1C
 *
 * @package     exchange_unit_tests
 * @author      Hvorostenko
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
     * @return  ProvidedData                data
     ************************************************************************/
    protected function readProvidedData() : ProvidedData
    {
        $result = new ProvidedData;

        try
        {
            $xml    = new XML($this->xmlWithProvidedData);
            $data   = $xml->read();
            $fields = $this->getFields();

            foreach ($data as $item)
            {
                $map = new ItemData;

                if (is_array($item))
                {
                    foreach ($item as $key => $value)
                    {
                        $field = $fields->findField($key);
                        $map->set($field, $value);
                    }
                }

                $result->push($map);
            }
        }
        catch (RuntimeException $exception)
        {

        }
        catch (InvalidArgumentException $exception)
        {

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