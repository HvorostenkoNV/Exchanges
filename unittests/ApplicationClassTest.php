<?php
declare(strict_types=1);

use Main\Application;
/** ***********************************************************************************************
 * Test Main\Application class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ApplicationClassTest extends ExchangeTestCase
{
	/** **********************************************************************
	 * Application is singleton
	 ************************************************************************/
	public function testIsSingleton() : void
	{
		self::assertTrue
		(
			$this->singletonImplemented(Application::class),
			$this->getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Application::class])
		);
	}
}