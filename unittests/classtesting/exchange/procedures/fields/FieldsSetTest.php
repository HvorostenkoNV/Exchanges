<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Procedures\Fields;

use
    UnitTests\ClassTesting\Data\SetDataAbstractTest,
    Main\Data\MapData,
    Main\Exchange\Procedures\Fields\ProcedureField,
    Main\Exchange\Procedures\Fields\FieldsSet;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Fields\FieldsSet class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class FieldsSetTest extends SetDataAbstractTest
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Set class name
     ************************************************************************/
    public static function getSetClassName() : string
    {
        return FieldsSet::class;
    }
    /** **********************************************************************
     * get correct data
     *
     * @return  array                       correct data array
     ************************************************************************/
    public static function getCorrectDataValues() : array
    {
        return
        [
            new ProcedureField,
            new ProcedureField
        ];
    }
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data array
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return
        [
            new MapData,
            new FieldsSet
        ];
    }
}