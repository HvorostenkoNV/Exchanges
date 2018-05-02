<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange;

use
    UnitTests\Core\ExchangeTestCase,
    Main\Exchange\Exchange;
/** ***********************************************************************************************
 * Test Main\Exchange\Exchange class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ExchangeTest extends ExchangeTestCase
{
    /** **********************************************************************
     * check Exchange is singleton
     *
     * @test
     ************************************************************************/
    public function isSingleton() : void
    {
        self::assertTrue
        (
            self::singletonImplemented(Exchange::class),
            self::getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Exchange::class])
        );
    }
}