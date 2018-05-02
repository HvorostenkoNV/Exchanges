<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Data;

use
    UnitTests\Core\QueueDataClass,
    Main\Data\QueueData;
/** ***********************************************************************************************
 * Test Main\Data\QueueData class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class QueueDataTest extends QueueDataClass
{
    protected static $queueClassName = QueueData::class;
    /** **********************************************************************
     * get correct data
     *
     * @return  array                   correct data array
     ************************************************************************/
    protected static function getCorrectValues() : array
    {
        parent::getCorrectValues();

        return
        [
            'string',
            10,
            10.5,
            true,
            [1, 2, 3],
            new QueueData,
            null
        ];
    }
}