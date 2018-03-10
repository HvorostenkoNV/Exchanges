<?php
declare(strict_types=1);

use Main\Data\Map;
/** ***********************************************************************************************
 * Parent class for testing Main\Data\Map classes
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
abstract class MapDataClass extends ExchangeTestCase
{
	protected static $mapClassName = '';
	/** **********************************************************************
	 * check empty object
	 * @test
	 * @return  Map                     empty Map data
	 ************************************************************************/
	public function emptyObject() : Map
	{
		$map = self::createMapObject();

		self::assertTrue
		(
			$map->isEmpty(),
			'New '.static::$mapClassName.' object is not empty'
		);
		self::assertEquals
		(
			0, $map->count(),
			'New '.static::$mapClassName.' object values count is not zero'
		);

		return $map;
	}
	/** **********************************************************************
	 * check read/write operations
	 * @test
	 * @depends emptyObject
	 * @param   Map $map                empty   Map data
	 * @return  Map                     filled  Map data
	 ************************************************************************/
	public function readWriteOperations(Map $map) : Map
	{
		$values = static::getCorrectData();

		if( count($values) <= 0 )
		{
			self::assertTrue(true);
			return $map;
		}

		foreach( $values as $index => $value )
			$map->set($index, $value);

		self::assertFalse
		(
			$map->isEmpty(),
			'Filled '.static::$mapClassName.' is empty'
		);
		self::assertEquals
		(
			count($values), $map->count(),
			'Filled '.static::$mapClassName.' values count is not equal items count put'
		);

		foreach( $values as $index => $value )
			self::assertEquals
			(
				$value, $map->get($index),
				'Value put before into '.static::$mapClassName.' not equals received'
			);

		self::assertEquals
		(
			array_keys($values), $map->getKeys(),
			'Received keys from '.static::$mapClassName.' is not equal put before'
		);

		return $map;
	}
	/** **********************************************************************
	 * check incorrect read/write operations
	 * @test
	 * @depends emptyObject
	 * @param   Map $map                empty Map data
	 ************************************************************************/
	public function incorrectReadWriteOperations(Map $map) : void
	{
		$correctData        = static::getCorrectData();
		$setedKeys          = $map->getKeys();
		$incorrectKeys      = static::getIncorrectKeys();
		$incorrectValues    = static::getIncorrectValues();
		$unknownKey         = 'unknownKey';
		$correctKey         = count($correctData) > 0   ? array_rand($correctData)  : NULL;
		$correctValue       = $correctKey               ? $correctData[$correctKey] : NULL;

		while( in_array($unknownKey, $setedKeys) )
			$unknownKey .= '1';

		self::assertNull
		(
			$map->get($unknownKey),
			'Received value in '.static::$mapClassName.' by incorrect key is not NULL'
		);

		if( count($incorrectKeys) > 0 && $correctValue )
			foreach( $incorrectKeys as $key )
			{
				try
				{
					$map->set($key, $correctValue);
					self::fail('Expect '.InvalidArgumentException::class.' exception in '.static::$mapClassName.' on seting value by incorrect key '.var_export($key, true));
				}
				catch( InvalidArgumentException $error )
				{
					self::assertTrue(true);
				}
			}

		if( count($incorrectValues) > 0 && $correctKey )
			foreach( $incorrectValues as $value )
			{
				try
				{
					$map->set($correctKey, $value);
					self::fail('Expect '.InvalidArgumentException::class.' exception in '.static::$mapClassName.' on seting incorrect value '.var_export($value, true));
				}
				catch( InvalidArgumentException $error )
				{
					self::assertTrue(true);
				}
			}
	}
	/** **********************************************************************
	 * check clearing operations
	 * @test
	 * @depends readWriteOperations
	 * @param   Map $map                filled Map data
	 ************************************************************************/
	public function clearingOperations(Map $map) : void
	{
		$keys = $map->getKeys();

		$map->delete($keys[array_rand($keys)]);
		self::assertEquals
		(
			count($keys) - 1, $map->count(),
			static::$mapClassName.' values count not less by one after delete one item'
		);

		$map->clear();
		self::assertTrue
		(
			$map->isEmpty(),
			static::$mapClassName.' is not empty after call "clear" method'
		);
		self::assertEquals
		(
			0, $map->count(),
			static::$mapClassName.' values count is not zero after call "clear" method'
		);
	}
	/** **********************************************************************
	 * check alternative create syntax
	 * @test
	 * @depends readWriteOperations
	 ************************************************************************/
	public function alternativeCreateSyntax() : void
	{
		$values = static::getCorrectData();
		$map    = self::createMapObject($values);

		self::assertFalse
		(
			$map->isEmpty(),
			static::$mapClassName.' created by array is empty'
		);
		self::assertEquals
		(
			count($values), $map->count(),
			static::$mapClassName.' values count is not equal array count created on'
		);

		foreach( $values as $index => $value )
			self::assertEquals
			(
				$value, $map->get($index),
				'Value form array '.static::$mapClassName.' created on not equals received'
			);
	}
	/** **********************************************************************
	 * get correct data
	 * @return  array                   correct data array
	 ************************************************************************/
	protected static function getCorrectData() : array
	{
		return [];
	}
	/** **********************************************************************
	 * get incorrect keys
	 * @return  array                   incorrect keys
	 ************************************************************************/
	protected static function getIncorrectKeys() : array
	{
		return [];
	}
	/** **********************************************************************
	 * get incorrect values
	 * @return  array                   incorrect values
	 ************************************************************************/
	protected static function getIncorrectValues() : array
	{
		return [];
	}
	/** **********************************************************************
	 * get new map object
	 * @param   array   $data           start data
	 * @return  Map                     new map object
	 ************************************************************************/
	final protected static function createMapObject(array $data = []) : Map
	{
		if( count($data) > 0 )  return new static::$mapClassName($data);
		else                    return new static::$mapClassName;
	}
}