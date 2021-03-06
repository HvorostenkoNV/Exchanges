<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures;

use
    Main\Exchange\Procedures\Procedure,
    Main\Exchange\Procedures\Data\ParticipantsSet,
    Main\Exchange\Procedures\Fields\FieldsSet,
    Main\Exchange\Procedures\Rules\DataMatchingRules,
    Main\Exchange\Procedures\Rules\DataCombiningRules;
/** ***********************************************************************************************
 * Procedure stub, empty class for testing
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class ProcedureStub implements Procedure
{
    /** **********************************************************************
     * get procedure code
     *
     * @return  string                      procedure code
     ************************************************************************/
    public function getCode() : string
    {
        return '';
    }
    /** **********************************************************************
     * get participants
     *
     * @return  ParticipantsSet             participants
     ************************************************************************/
    public function getParticipants() : ParticipantsSet
    {
        return new ParticipantsSet;
    }
    /** **********************************************************************
     * get fields
     *
     * @return  FieldsSet                   fields
     ************************************************************************/
    public function getFields() : FieldsSet
    {
        return new FieldsSet;
    }
    /** **********************************************************************
     * get data matching rules
     *
     * @return  DataMatchingRules           data matching rules
     ************************************************************************/
    public function getDataMatchingRules() : DataMatchingRules
    {
        return new DataMatchingRules;
    }
    /** **********************************************************************
     * get data combining rules
     *
     * @return  DataCombiningRules          data combining rules
     ************************************************************************/
    public function getDataCombiningRules() : DataCombiningRules
    {
        return new DataCombiningRules;
    }
}