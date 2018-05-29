<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants;

use
    DomainException,
    RuntimeException,
    InvalidArgumentException,
    SplFileInfo,
    Main\Helpers\MarkupData\XML,
    Main\Exchange\Participants\AbstractParticipants,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\DataForDelivery;
/** ***********************************************************************************************
 * Application participant Users1C
 *
 * @property    SplFileInfo $tempXmlFromUnitTest
 * @package     exchange_unit_tests
 * @author      Hvorostenko
 *************************************************************************************************/
class ParticipantForUnitTest extends AbstractParticipants
{
    /** @var SplFileInfo */
    public $tempXmlFromUnitTest     = null;
    /** @var SplFileInfo */
    public $createdTempXmlAnswer    = null;
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @return  ProvidedData                data
     ************************************************************************/
    protected function readProvidedData() : ProvidedData
    {
        $result = new ProvidedData;
        $xml    = new XML($this->tempXmlFromUnitTest);
        $data   = null;
        $fields = $this->getFields();

        try
        {
            $data = $xml->read();
        }
        catch (RuntimeException $exception)
        {
            return $result;
        }

        foreach ($data as $item)
        {
            if (is_array($item))
            {
                $map = new ItemData;

                foreach ($item as $key => $value)
                {
                    try
                    {
                        $field = $fields->findField($key);
                        $map->set($field, $value);
                    }
                    catch (InvalidArgumentException $exception)
                    {

                    }
                }

                if ($map->count() > 0)
                {
                    try
                    {
                        $result->push($map);
                    }
                    catch (InvalidArgumentException $exception)
                    {

                    }
                }
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
        $xml        = new XML($this->createdTempXmlAnswer);

        try
        {
            while (!$data->isEmpty())
            {
                $item       = $data->pop();
                $itemArray  = [];

                foreach ($item->getKeys() as $field)
                {
                    try
                    {
                        $fieldName              = $field->getParam('name');
                        $value                  = $item->get($field);
                        $validatedValue         = $field->getFieldType()->convertValueForPrint($value);
                        $itemArray[$fieldName]  = $validatedValue;
                    }
                    catch (DomainException $exception)
                    {

                    }
                    catch (InvalidArgumentException $exception)
                    {

                    }
                }

                if (count($itemArray) > 0)
                {
                    $dataArray[] = $itemArray;
                }
            }
        }
        catch (RuntimeException $exception)
        {

        }

        return $xml->write($dataArray);
    }
}