<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\FieldsTypes;

use
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\ArrayOfBooleansField;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\FieldsTypes\ArrayOfBooleansField classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ArrayOfBooleansFieldTest extends ParticipantFieldClass
{
    /** **********************************************************************
     * get field class name
     *
     * @return  string                      field class name
     ************************************************************************/
    public static function getFieldClassName() : string
    {
        return ArrayOfBooleansField::class;
    }
    /** **********************************************************************
     * get values for validation
     *
     * @return  array                       values for validation
     ************************************************************************/
    public static function getValuesForValidation() : array
    {
        return
        [
            [1,             [true]],
            [1.5,           []],
            [0,             [false]],
            ['1',           [true]],
            ['1.5',         []],
            ['0',           [false]],
            ['someString',  []],
            ['',            []],
            ['Y',           [true]],
            ['N',           [false]],
            ['y',           [true]],
            ['n',           [false]],
            [true,          [true]],
            [false,         [false]],
            [[],            []],
            [
                [1,     2.5,    0,      'someString',   '', true,   false,  [], new MapData,    null],
                [true,          false,                      true,   false]
            ],
            [new MapData,   []],
            [null,          []]
        ];
    }
    /** **********************************************************************
     * get values for print converting
     *
     * @return  array                       values for print converting
     * @example
     *                                      [value, expected value]
     *                                      [value] - if no expected value? expect exception
     ************************************************************************/
    public static function getValuesForPrintConverting() : array
    {
        return
        [
            [1,             []],
            [1.5,           []],
            [0,             []],
            ['1',           []],
            ['1.5',         []],
            ['0',           []],
            ['someString',  []],
            ['',            []],
            ['Y',           []],
            ['N',           []],
            ['y',           []],
            ['n',           []],
            [true,          ['Y']],
            [false,         ['N']],
            [[],            []],
            [
                [1,     2.5,    0,      'someString',   '', true,   false,  [], new MapData,    null],
                [                                           'Y',    'N']
            ],
            [new MapData,   []],
            [null,          []]
        ];
    }
}