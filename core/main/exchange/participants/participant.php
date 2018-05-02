<?php
declare(strict_types=1);

namespace Main\Exchange\Participants;

use
    Main\Exchange\Participants\Data\ProvidedData,
    Main\Exchange\Participants\Data\FieldsMap,
    Main\Exchange\Participants\Data\DeliveredData;
/** ***********************************************************************************************
 * Application participant interface
 *
 * @package exchange_exchange
 * @author  Hvorostenko
 *************************************************************************************************/
interface Participant
{
    /** **********************************************************************
     * get participant fields params
     *
     * @return  FieldsMap                   fields params
     ************************************************************************/
    public function getFields() : FieldsMap;
    /** **********************************************************************
     * get participant provided data
     *
     * @return  ProvidedData                provided data
     ************************************************************************/
    public function getProvidedData() : ProvidedData;
    /** **********************************************************************
     * provide data to the participant
     *
     * @param   DeliveredData   $data       provided data
     * @return  bool                        providing data result
     ************************************************************************/
    public function provideData(DeliveredData $data) : bool;
}