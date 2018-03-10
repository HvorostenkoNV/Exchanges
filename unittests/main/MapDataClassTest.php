<?php
declare(strict_types=1);

use Main\Data\MapData;
/** ***********************************************************************************************
 * Test Main\Data\MapData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class MapDataClassTest extends MapDataClass
{
	protected static $mapClassName = MapData::class;
	/** **********************************************************************
	 * get correct data
	 * @return  array                   correct data array
	 ************************************************************************/
	protected static function getCorrectData() : array
	{
		parent::getCorrectData();

		return
		[
			1       => 'string',
			'two'   => 2,
			'three' => 2.5,
			4       => true,
			5       => [1, 2, 3],
			6       => new MapData,
			7       => NULL
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
			[1, 2, 3],
			new MapData,
			true,
			5.5,
			NULL
		];
	}
}