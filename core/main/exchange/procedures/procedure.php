<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\FieldsSet,
    Main\Exchange\Procedures\Rules\DataMatchingRules,
    Main\Exchange\Procedures\Rules\DataCombiningRules;
/** ***********************************************************************************************
 * Application procedure interface
 *
 * @package exchange_exchange_procedures
 * @author  Hvorostenko
 *************************************************************************************************/
interface Procedure
{
    /** **********************************************************************
     * get procedure code
     *
     * @return  string                      procedure code
     ************************************************************************/
    public function getCode() : string;
    /** **********************************************************************
     * get participants
     *
     * @return  ParticipantsSet             participants
     ************************************************************************/
    public function getParticipants() : ParticipantsSet;
    /** **********************************************************************
     * get fields
     *
     * @return  FieldsSet                   fields
     ************************************************************************/
    public function getFields() : FieldsSet;
    /** **********************************************************************
     * get data matching rules
     *
     * @return  DataMatchingRules           data matching rules
     ************************************************************************/
    public function getDataMatchingRules() : DataMatchingRules;
    /** **********************************************************************
     * get data combining rules
     *
     * @return  DataCombiningRules          data combining rules
     ************************************************************************/
    public function getDataCombiningRules() : DataCombiningRules;
}