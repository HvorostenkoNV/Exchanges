<?php
declare(strict_types=1);

use
	Main\Data\MapData,
	Main\Exchange\Participants\Data\ItemData,
	Main\Exchange\Participants\Data\DeliveredData;
/** ***********************************************************************************************
 * Test Main\Data\QueueData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DeliveredDataClassTest extends QueueDataClass
{
	protected static $queueClassName = DeliveredData::class;
	/** **********************************************************************
	 * get correct data
	 * @return  array                   correct data array
	 ************************************************************************/
	protected static function getCorrectValues() : array
	{
		parent::getCorrectValues();

		return
			[
				new ItemData,
				new ItemData,
				new ItemData,
				new ItemData
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
				new DeliveredData,
				new MapData,
				NULL
			];
	}
}