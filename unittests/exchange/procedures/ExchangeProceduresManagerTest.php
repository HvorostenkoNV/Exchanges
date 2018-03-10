<?php
declare(strict_types=1);

use Main\Helpers\DB;
/** ***********************************************************************************************
 * Test Main\Exchange\Procedures\Manager class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ExchangeProceduresManagerTest extends DBExchangeTestCase
{
	private static
		$proceduresTable                = 'procedures',
		$tempProcedure                  = 'unit_test_procedure',
		$tempProcedureInactive          = 'unit_test_procedure_inactive',
		$tempProcedureWithoutFile       = 'unit_test_procedure_without_file',
		$tempProcedureWithoutDBResord   = 'unit_test_procedure_without_db_record';
	/** **********************************************************************
	 * construct
	 ************************************************************************/
	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
	}
	/** **********************************************************************
	 * destruct
	 ************************************************************************/
	public static function tearDownAfterClass() : void
	{
		parent::tearDownAfterClass();
	}
	/** **********************************************************************
	 * test providing correct procedures list
	 * @test
	 ************************************************************************/
	public function provideCorrectProceduresList() : void
	{
		/*
		$result = DB::getInstance()->query('SELECT * FROM procedures');

		while( !$result->isEmpty() )
		{
			print_r($result->dequeue());
		}
*/
		self::markTestSkipped('skiped');
	}
	/** **********************************************************************
	 * test providing correct procedure by name
	 * @test
	 ************************************************************************/
	public function provideCorrectProcedureByName() : void
	{
		self::markTestSkipped('skiped');
	}
	/** **********************************************************************
	 * get temp data set
	 * @return  array   temp data
	 ************************************************************************/
	protected static function prepareDBTempData() : array
	{
		/*
		$sqlQuery   = 'SELECT * FROM #TABLE_NAME#';
		$data       = [];

		$sqlQuery       = str_replace('#TABLE_NAME#', self::$proceduresTable, $sqlQuery);
		$queryResult    = DB::getInstance()->query($sqlQuery);

		while( !$queryResult->isEmpty() )
		{
			$itemInfo   = $queryResult->dequeue();
			$data[]     =
			[
				'ID'        => $itemInfo->ID,
				'NAME'      => $itemInfo->NAME,
				'ACTIVITY'  => $itemInfo->ACTIVITY
			];
		}

		$data[] =
		[
			'ID'        => 3,
			'NAME'      => self::$tempProcedure,
			'ACTIVITY'  => 1
		];
*/
		return
		[
			self::$proceduresTable =>
			[
				[
					'NAME'      => self::$tempProcedure,
					'ACTIVITY'  => 'Y'
				],
				[
					'NAME'      => self::$tempProcedureInactive,
					'ACTIVITY'  => 'N'
				],
				[
					'NAME'      => self::$tempProcedureWithoutFile,
					'ACTIVITY'  => 'Y'
				]
			]
		];
	}
}