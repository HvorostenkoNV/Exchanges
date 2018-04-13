<?php
declare(strict_types=1);

use Main\Exchange\Procedures\Rules\MatchingRulesQueue;
/** ***********************************************************************************************
 * Test Main\Helpers\Data\DBQueryResult class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MatchingRulesQueueClassTest extends QueueDataClass
{
    protected static $queueClassName = MatchingRulesQueue::class;
    /** **********************************************************************
     * get correct data
     * @return  array                   correct data array
     ************************************************************************/
    protected static function getCorrectValues() : array
    {
        parent::getCorrectValues();

        return
        [
            ['string', 'string'],
            ['string', 'string'],
            ['string', 'string']
        ];
    }
    /** **********************************************************************
     * get incorrect values
     * @return  array                   incorrect values
     ************************************************************************/
    protected static function getIncorrectValues() : array
    {
        parent::getIncorrectValues();

        return
        [
            'string',
            1,
            1.5,
            true,
            [1, 2, 3],
            [],
            new MatchingRulesQueue,
            NULL
        ];
    }
}