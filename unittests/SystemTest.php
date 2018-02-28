<?php
declare(strict_types=1);
/** ***********************************************************************************************
 * Test system
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
class SystemTest extends ExchangeTestCase
{
	private
		$needPhpVersion = 7.2;
	/** **********************************************************************
	 * check PHP version
	 ************************************************************************/
	public function testPhpVersion() : void
	{
		$phpVersionExplode  = explode('.', phpversion());
		$phpVersion         = floatval($phpVersionExplode[0].'.'.$phpVersionExplode[1]);

		self::assertTrue
		(
			$phpVersion >= $this->needPhpVersion,
			'PHP version have to be '.$this->needPhpVersion.' or higher'
		);
	}
}