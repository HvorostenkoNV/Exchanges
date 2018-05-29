<?php
declare(strict_types=1);

namespace Main\Exchange\Procedures;

use
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Rules\FieldsMatchingRules,
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
     * get participants
     *
     * @return  ParticipantsSet             participants
     ************************************************************************/
    public function getParticipants() : ParticipantsSet;
    /** **********************************************************************
     * get participants fields matching rules
     *
     * @return  FieldsMatchingRules         participants
     ************************************************************************/
    public function getFieldsMatchingRules() : FieldsMatchingRules;
    /** **********************************************************************
     * get participants data matching rules
     *
     * @return  DataMatchingRules           participants
     ************************************************************************/
    public function getDataMatchingRules() : DataMatchingRules;
    /** **********************************************************************
     * get participants fields values combining rules
     *
     * @return  DataCombiningRules          participants
     ************************************************************************/
    public function getDataCombiningRules() : DataCombiningRules;
}