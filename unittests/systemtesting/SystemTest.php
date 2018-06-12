<?php
declare(strict_types=1);

namespace UnitTests\SystemTesting;

use UnitTests\AbstractTestCase;
/** ***********************************************************************************************
 * Test system
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class SystemTest extends AbstractTestCase
{
    private $needPhpVersion = 7.2;
    /** **********************************************************************
     * check PHP version
     *
     * @test
     * @throws
     ************************************************************************/
    public function phpVersion() : void
    {
        $phpVersionExplode  = explode('.', phpversion());
        $phpVersion         = floatval($phpVersionExplode[0].'.'.$phpVersionExplode[1]);
        $needPhpVersion     = $this->needPhpVersion;

        self::assertTrue
        (
            $phpVersion >= $this->needPhpVersion,
            "PHP version have to be $needPhpVersion or higher"
        );
    }
    /** **********************************************************************
     * check document root constant exist
     *
     * @test
     * @return  string                          document root constant value
     * @throws
     ************************************************************************/
    public function documentRootConstantExist() : string
    {
        self::assertTrue
        (
            defined('DOCUMENT_ROOT'),
            self::getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'DOCUMENT_ROOT'])
        );

        return DOCUMENT_ROOT;
    }
    /** **********************************************************************
     * check params folder constant exist
     *
     * @test
     * @depends documentRootConstantExist
     * @param   string  $documentRoot           document root path
     * @throws
     ************************************************************************/
    public function paramsFolderConstantExist(string $documentRoot) : void
    {
        self::assertTrue
        (
            defined('PARAMS_FOLDER'),
            self::getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'PARAMS_FOLDER'])
        );
        self::assertNotEquals
        (
            $documentRoot, PARAMS_FOLDER,
            'Params constant equals document root'
        );
    }
    /** **********************************************************************
     * check logs folder constant exist
     *
     * @test
     * @depends documentRootConstantExist
     * @param   string  $documentRoot           document root path
     * @throws
     ************************************************************************/
    public function logsFolderConstantExist(string $documentRoot) : void
    {
        self::assertTrue
        (
            defined('LOGS_FOLDER'),
            self::getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'LOGS_FOLDER'])
        );
        self::assertNotEquals
        (
            $documentRoot, LOGS_FOLDER,
            'Logs constant equals document root'
        );
    }
}