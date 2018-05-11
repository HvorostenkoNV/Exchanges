<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange\Participants\FieldsTypes;

use
    Main\Data\MapData,
    Main\Exchange\Participants\FieldsTypes\ArrayOfStringsField;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\FieldsTypes\ArrayOfStringsField classes
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ArrayOfStringsFieldTest extends ParticipantFieldClass
{
    /** **********************************************************************
     * get field class name
     *
     * @return  string                      field class name
     ************************************************************************/
    public static function getFieldClassName() : string
    {
        return ArrayOfStringsField::class;
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
            [1,             ['1']],
            [1.5,           ['1.5']],
            [0,             ['0']],
            ['1',           ['1']],
            ['1.5',         ['1.5']],
            ['0',           ['0']],
            ['someString',  ['someString']],
            ['',            []],
            ['Y',           ['Y']],
            ['N',           ['N']],
            ['y',           ['y']],
            ['n',           ['n']],
            [true,          ['Y']],
            [false,         ['N']],
            [[],            []],
            [
                [1,     2.5,    0,      'someString',   '', true,   false,  [], new MapData,    null],
                ['1',   '2.5',  '0',    'someString',       'Y',    'N']
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
            ['1',           ['1']],
            ['1.5',         ['1.5']],
            ['0',           ['0']],
            ['someString',  ['someString']],
            ['',            []],
            ['Y',           ['Y']],
            ['N',           ['N']],
            ['y',           ['y']],
            ['n',           ['n']],
            [true,          []],
            [false,         []],
            [[],            []],
            [
                [1,     2.5,    0,      'someString',   '', true,   false,  [], new MapData,    null],
                [                       'someString'                                                ]
            ],
            [new MapData,   []],
            [null,          []]
        ];
    }
}