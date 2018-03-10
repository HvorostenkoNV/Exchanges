<?php
declare(strict_types=1);

use Main\Helpers\Data\DBFieldsValues;
/** ***********************************************************************************************
 * Test Main\Helpers\Data\DBFieldsValues class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBDBFieldsValuesClassTest extends MapDataClass
{
	protected static $mapClassName = DBFieldsValues::class;
	/** **********************************************************************
	 * get correct data
	 * @return  array                   correct data array
	 ************************************************************************/
	protected static function getCorrectData() : array
	{
		parent::getCorrectData();

		return
		[
			'One'   => 'string',
			'two'   => 1,
			'three' => 1.5
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
			1,
			5.5,
			true,
			[1, 2, 3],
			new DBFieldsValues,
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
			true,
			[1, 2, 3],
			new DBFieldsValues
		];
	}
}