<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    RuntimeException,
    UnexpectedValueException,
    InvalidArgumentException,
    RecursiveDirectoryIterator,
    RecursiveIteratorIterator,
    Main\Helpers\MarkupData\XML,
    Main\Exchange\Participants\Data\ItemData,
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\DataForDelivery,
    Main\Exchange\Participants\Fields\FieldsSet;
/** ***********************************************************************************************
 * Application participant UsersBitrix
 *
 * @package exchange_exchange_participants
 * @author  Hvorostenko
 *************************************************************************************************/
class UsersBitrix extends AbstractParticipant
{
    /** **********************************************************************
     * read participant provided data and get it
     *
     * @param   FieldsSet $fields           participant fields set
     * @return  ProvidedData                data
     ************************************************************************/
    protected function readProvidedData(FieldsSet $fields) : ProvidedData
    {
        $result     = new ProvidedData;
        $folder     = '/var/www/temp/bitrix1cadusersexchange/received/bitrix';
        $needFile   = null;
        $data       = null;

        try
        {
            $directory  = new RecursiveDirectoryIterator($folder);
            $iterator   = new RecursiveIteratorIterator($directory);

            while ($iterator->valid())
            {
                $file = $iterator->current();

                if ($file->isFile() && $file->getExtension() == 'xml' && $file->isReadable())
                {
                    $needFile = $file;
                    break;
                }

                $iterator->next();
            }
        }
        catch (UnexpectedValueException $exception)
        {
            return $result;
        }
        catch (RuntimeException $exception)
        {
            return $result;
        }

        try
        {
            $xml    = new XML($needFile);
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
     * TODO
     ************************************************************************/
    protected function provideDataForDelivery(DataForDelivery $data) : bool
    {
        return false;
    }
}