<?php
declare(strict_types=1);

use Main\Data\MapData;
/** ***********************************************************************************************
 * Test Main\Data\MapData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MapDataClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * check functional
	 * @test
	 ************************************************************************/
	public function check() : void
	{
		$map    = new MapData;
		$values =
		[
			1       => 'One',
			'two'   => 'Two',
			'three' => 3,
			4       => 4
		];

		self::assertTrue($map->isEmpty(),       'Incorrect Map work');
		self::assertTrue($map->count() === 0,   'Incorrect Map work');

		foreach( $values as $index => $value )
			$map->set($index, $value);

		self::assertFalse($map->isEmpty(),                  'Incorrect Map work');
		self::assertTrue($map->count() === count($values),  'Incorrect Map work');

		foreach( $values as $index => $value )
			self::assertTrue($map->get($index) === $value, 'Incorrect Map work');

		$map->delete(array_rand($values));
		$map->delete(array_rand($values));
		self::assertTrue($map->count() === count($values) - 2, 'Incorrect Map work');

		$map->clear();
		self::assertTrue($map->isEmpty(), 'Incorrect Queue work');

		$map = new MapData($values);
		self::assertTrue($map->count() === count($values),  'Incorrect Map work');
		foreach( $values as $index => $value )
			self::assertTrue($map->get($index) === $value, 'Incorrect Map work');

		$keys = $map->getKeys();
		self::assertTrue($keys->count() === count($values), 'Incorrect Map work');
		while( !$keys->isEmpty() )
			self::assertTrue(array_key_exists($keys->pop(), $values), 'Incorrect Map work');
	}
}