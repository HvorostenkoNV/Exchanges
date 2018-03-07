<?php
declare(strict_types=1);

use
	Main\Data\Map,
	Main\Exchange\Procedures\Rules\MatchingRulesMapData,
	Main\Exchange\Procedures\Rules\MatchingRules;
/** ***********************************************************************************************
 * Test Main\Exchange\Participants\Data\FieldsParams class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class MatchingRulesClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * test matching rules map
	 * @test
	 ************************************************************************/
	public function matchingRulesMap() : void
	{
		$map                    = new MatchingRulesMapData;
		$invalidValueIndexes    =
		[
			'string',
			1,
			true,
			[1, 2, 3],
			new DBQueryData
		];
		$invalidValues          =
		[
			1,
			true,
			[1, 2, 3],
			new DBQueryData
		];

		self::assertTrue
		(
			$map instanceof Map,
			'MatchingRulesMapData have to implements '.Map::class.' interface'
		);

		foreach( $invalidValueIndexes as $index )
		{
			try
			{
				$map->set($index, 'testValue');
				self::fail('Expect '.InvalidArgumentException::class.' error with set value on non class name index');
			}
			catch( InvalidArgumentException $error )
			{
				self::assertTrue(true);
			}
		}

		try
		{
			$map->set(MatchingRulesMapData::class, 'testValue');
			self::assertTrue(true);
		}
		catch( InvalidArgumentException $error )
		{
			self::fail('Unexpected error with set value on class name index');
		}

		foreach( $invalidValues as $value )
		{
			try
			{
				$map->set(MatchingRulesMapData::class, $value);
				self::fail('Expect '.InvalidArgumentException::class.' error with set not string value');
			}
			catch( InvalidArgumentException $error )
			{
				self::assertTrue(true);
			}
		}

		try
		{
			$map->set(MatchingRulesMapData::class, 'testValue');
			self::assertTrue(true);
		}
		catch( InvalidArgumentException $error )
		{
			self::fail('Unexpected error with set string value');
		}
	}
	/** **********************************************************************
	 * test available fields methods
	 * @test
	 ************************************************************************/
	public function availableFields() : void
	{
		$queue  = new MatchingRules;
		$values =
		[
			new MapData,
			new MapData,
			new MapData,
			new MapData
		];

		self::assertTrue($queue->isEmpty(),     'Incorrect DBQueryData work');
		self::assertTrue($queue->count() === 0, 'Incorrect DBQueryData work');

		foreach( $values as $value )
			$queue->push($value);

		self::assertFalse($queue->isEmpty(),                'Incorrect DBQueryData work');
		self::assertTrue($queue->count() == count($values), 'Incorrect DBQueryData work');

		self::assertTrue($queue->pop() === $values[0],  'Incorrect DBQueryData work');
		self::assertTrue($queue->pop() === $values[1],  'Incorrect DBQueryData work');

		self::assertTrue($queue->count() == count($values) - 2, 'Incorrect DBQueryData work');

		$queue->clear();
		self::assertTrue($queue->isEmpty(), 'Incorrect DBQueryData work');

		try
		{
			$queue->pop();
			self::fail('Expect '.RuntimeException::class.' error with pop on empty queue');
		}
		catch( RuntimeException $error )
		{
			self::assertTrue(true);
		}
	}
}