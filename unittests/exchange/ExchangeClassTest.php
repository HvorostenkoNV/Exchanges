<?php
declare(strict_types=1);

use Main\Exchange\Exchange;
/** ***********************************************************************************************
 * Test Main\Exchange\Exchange class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ExchangeClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * Exchange is singleton
	 * @test
	 ************************************************************************/
	public function isSingleton() : void
	{
		self::assertTrue
		(
			$this->singletonImplemented(Exchange::class),
			$this->getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Exchange::class])
		);
	}
}