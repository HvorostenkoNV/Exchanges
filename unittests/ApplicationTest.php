<?php
use Main\Application;

final class ApplicationTest extends ExchangeTestCase
{
	/* -------------------------------------------------------------------- */
	/* -------------------------- is singletone --------------------------- */
	/* -------------------------------------------------------------------- */
	public function testIsSingletone() : void
	{
		self::assertTrue
		(
			$this->singletoneImplemented(Application::class),
			$this->getMessage('SINGLETONE_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Application::class])
		);
	}
}