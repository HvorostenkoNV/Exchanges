<?php
declare(strict_types=1);

use
	Main\Data\MapData,
	Main\Exchange\Participants\Data\ProvidedData;
/** ***********************************************************************************************
 * Test Main\Data\QueueData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ProvidedDataClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * check functional
	 * @test
	 ************************************************************************/
	public function check() : void
	{
		$queue  = new ProvidedData;
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

		try
		{
			$queue->push('test');
			self::fail('Expect '.InvalidArgumentException::class.' error with push non '.MapData::class.' data');
		}
		catch( InvalidArgumentException $error )
		{
			self::assertTrue(true);
		}
	}
}