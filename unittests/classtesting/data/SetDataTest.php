<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Data;

use
    UnitTests\Core\SetDataClass,
    Main\Data\MapData,
    Main\Data\QueueData,
    Main\Data\SetData;
/** ***********************************************************************************************
 * Test Main\Data\SetData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class SetDataTest extends SetDataClass
{
    /** **********************************************************************
     * get Queue class name
     *
     * @return  string                      Set class name
     ************************************************************************/
    public static function getSetClassName() : string
    {
        return SetData::class;
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
            new MapData,
            new QueueData,
            new SetData
        ];
    }
    /** **********************************************************************
     * get incorrect data
     *
     * @return  array                       incorrect data array
     ************************************************************************/
    public static function getIncorrectDataValues() : array
    {
        return [];
    }
}