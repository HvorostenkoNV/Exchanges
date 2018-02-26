<?php
declare(strict_types=1);

use Main\Application;
/**************************************************************************************************
 * Test Main\Application class
 * @author Hvorostenko
 *************************************************************************************************/
final class ApplicationTest extends ExchangeTestCase
{
	/*************************************************************************
	 * Application is singletone
	 ************************************************************************/
	public function testIsSingletone() : void
	{
		self::assertTrue
		(
			$this->singletoneImplemented(Application::class),
			$this->getMessage('SINGLETONE_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Application::class])
		);
	}
}