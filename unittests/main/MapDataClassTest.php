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
		$map            = new MapData;
		$values         =
		[
			1       => 'One',
			'two'   => 'Two',
			'three' => 3,
			4       => 4
		];
		$randValuesKeys = array_rand($values, 2);

		self::assertTrue($map->isEmpty(),       'Incorrect Map work');
		self::assertTrue($map->count() === 0,   'Incorrect Map work');

		foreach( $values as $index => $value )
			$map->set($index, $value);

		self::assertFalse($map->isEmpty(),                  'Incorrect Map work');
		self::assertTrue($map->count() === count($values),  'Incorrect Map work');

		foreach( $values as $index => $value )
			self::assertTrue($map->get($index) === $value, 'Incorrect Map work');

		$map->delete($randValuesKeys[0]);
		$map->delete($randValuesKeys[1]);
		self::assertTrue($map->count() === count($values) - 2, 'Incorrect Map work');

		$map->clear();
		self::assertTrue($map->isEmpty(), 'Incorrect Map work');

		$map = new MapData($values);
		self::assertTrue($map->count() === count($values),  'Incorrect Map work');
		foreach( $values as $index => $value )
			self::assertTrue($map->get($index) === $value, 'Incorrect Map work');

		self::assertEquals($map->getKeys(), array_keys($values),    'Incorrect Map work');
	}
}