<?php
declare(strict_types=1);

use
	Main\Data\Queue,
	Main\Data\MapData,
	Main\Helpers\DBQueryData;
/** ***********************************************************************************************
 * Test Main\Data\QueueData class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBQueryDataClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * check functional
	 * @test
	 ************************************************************************/
	public function check() : void
	{
		$queue          = new DBQueryData;
		$invalidValues  =
		[
			'string',
			1,
			true,
			[1, 2, 3],
			new DBQueryData
		];

		self::assertTrue
		(
			$queue instanceof Queue,
			'DBQueryData have to implements '.Queue::class.' interface'
		);

		foreach( $invalidValues as $value )
		{
			try
			{
				$queue->push($value);
				self::fail('Expect '.InvalidArgumentException::class.' error with push non '.MapData::class.' data');
			}
			catch( InvalidArgumentException $error )
			{
				self::assertTrue(true);
			}
		}

		try
		{
			$queue->push(new MapData);
			self::assertTrue(true);
		}
		catch( InvalidArgumentException $error )
		{
			self::fail('Unexpected error with push '.MapData::class.' data');
		}
	}
}