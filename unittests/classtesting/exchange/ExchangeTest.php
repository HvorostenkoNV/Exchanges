<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Exchange;

use
    UnitTests\AbstractTestCase,
    Main\Exchange\Exchange;
/** ***********************************************************************************************
 * Test Main\Exchange\Exchange class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ExchangeTest extends AbstractTestCase
{
    /** **********************************************************************
     * check Exchange is singleton
     *
     * @test
     * @throws
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