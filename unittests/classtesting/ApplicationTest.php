<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting;

use
    UnitTests\Core\ExchangeTestCase,
    Main\Application;
/** ***********************************************************************************************
 * Test Main\Application class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ApplicationTest extends ExchangeTestCase
{
    /** **********************************************************************
     * check application is singleton
     *
     * @test
     ************************************************************************/
    public function isSingleton() : void
    {
        self::assertTrue
        (
            self::singletonImplemented(Application::class),
            self::getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Application::class])
        );
    }
}