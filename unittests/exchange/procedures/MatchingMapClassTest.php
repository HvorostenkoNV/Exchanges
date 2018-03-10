<?php
declare(strict_types=1);

use
	Main\Data\QueueData,
	Main\Data\MapData,
	Main\Exchange\Procedures\Rules\MatchingMap;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\ItemData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MatchingMapClassTest extends MapDataClass
{
	protected static $mapClassName = MatchingMap::class;
	/** **********************************************************************
	 * get correct data
	 * @return  array                   correct data array
	 ************************************************************************/
	protected static function getCorrectData() : array
	{
		parent::getCorrectData();

		return
		[
			QueueData::class    => 'field1',
			MapData::class      => 'field2',
			MatchingMap::class  => 'field3'
		];
	}
	/** **********************************************************************
	 * get incorrect keys
	 * @return  array                   incorrect keys
	 ************************************************************************/
	protected static function getIncorrectKeys() : array
	{
		parent::getIncorrectKeys();

		return
		[
			'string',
			1,
			5.5,
			true,
			[1, 2, 3],
			new MatchingMap,
			NULL
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
			1,
			5.5,
			true,
			[1, 2, 3],
			new MatchingMap,
			NULL
		];
	}
}