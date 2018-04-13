<?php
declare(strict_types=1);

use
    Main\Data\MapData,
    Main\Data\QueueData,
    Main\Data\SetData;
/** ***********************************************************************************************
 * Test Main\Data\SetData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class SetDataClassTest extends SetDataClass
{
    protected static $setClassName = SetData::class;
    /** **********************************************************************
     * get correct data
     * @return  array                   correct data array
     ************************************************************************/
    protected static function getCorrectValues() : array
    {
        parent::getCorrectValues();

        return
        [
            new MapData,
            new QueueData,
            new SetData
        ];
    }
}