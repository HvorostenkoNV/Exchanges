<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\FieldsTypes;

use
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\ArrayOfNumbersField;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\FieldsTypes\ArrayOfStringsField classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ArrayOfNumbersFieldTest extends ParticipantFieldClass
{
    /** **********************************************************************
     * get field class name
     *
     * @return  string                      field class name
     ************************************************************************/
    public static function getFieldClassName() : string
    {
        return ArrayOfNumbersField::class;
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
            [1,             [1]],
            [1.5,           [1.5]],
            [0,             [0]],
            ['1',           [1]],
            ['1.5',         [1.5]],
            ['0',           [0]],
            ['someString',  []],
            ['',            []],
            ['Y',           []],
            ['N',           []],
            ['y',           []],
            ['n',           []],
            [true,          [1]],
            [false,         [0]],
            [[],            []],
            [
                [1, 2.5,    0,  'someString',   '', true,   false,  [], new MapData,  null],
                [1, 2.5,    0,                      1,      0]
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
            [1,             ['1']],
            [1.5,           ['1.5']],
            [0,             ['0']],
            ['1',           []],
            ['1.5',         []],
            ['0',           []],
            ['someString',  []],
            ['',            []],
            ['Y',           []],
            ['N',           []],
            ['y',           []],
            ['n',           []],
            [true,          []],
            [false,         []],
            [[],            []],
            [
                [1,     2.5,    0,  'someString',   '', true,   false,  [], new MapData,  null],
                ['1',   '2.5',  '0']
            ],
            [new MapData,   []],
            [null,          []]
        ];
    }
}