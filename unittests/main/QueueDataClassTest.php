<?php
declare(strict_types=1);

use Main\Data\QueueData;
/** ***********************************************************************************************
 * Test Main\Data\QueueData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class QueueDataClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * check functional
	 * @test
	 ************************************************************************/
	public function check() : void
	{
		$queue  = new QueueData;
		$value1 = 'Value 1';
		$value2 = 2;
		$value3 = [1 => 1, 'two' => 'value two'];
		$value4 = new QueueData;

		self::assertTrue($queue->isEmpty(),         'Incorrect Queue work');
		self::assertTrue($queue->count() === 0,     'Incorrect Queue work');

		$queue->push($value1);
		$queue->push($value2);
		$queue->push($value3);
		$queue->push($value4);

		self::assertFalse($queue->isEmpty(),        'Incorrect Queue work');
		self::assertTrue($queue->count() === 4,     'Incorrect Queue work');

		self::assertTrue($queue->pop() === $value1, 'Incorrect Queue work');
		self::assertTrue($queue->pop() === $value2, 'Incorrect Queue work');

		self::assertTrue($queue->count() === 2,     'Incorrect Queue work');

		$queue->clear();
		self::assertTrue($queue->isEmpty(),         'Incorrect Queue work');

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