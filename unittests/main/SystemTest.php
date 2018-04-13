<?php
declare(strict_types=1);
/** ***********************************************************************************************
 * Test system
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class SystemTest extends ExchangeTestCase
{
    private $needPhpVersion = 7.2;
    /** **********************************************************************
     * check PHP version
     * @test
     ************************************************************************/
    public function phpVersion() : void
    {
        $phpVersionExplode  = explode('.', phpversion());
        $phpVersion         = floatval($phpVersionExplode[0].'.'.$phpVersionExplode[1]);

        self::assertTrue
        (
            $phpVersion >= $this->needPhpVersion,
            'PHP version have to be '.$this->needPhpVersion.' or higher'
        );
    }
    /** **********************************************************************
     * check document root constant exist
     * @test
     * @return  string                      document root constant value
     * @throws
     ************************************************************************/
    public function documentRootConstantExist() : string
    {
        self::assertTrue
        (
            defined('DOCUMENT_ROOT'),
            $this->getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'DOCUMENT_ROOT'])
        );

        return DOCUMENT_ROOT;
    }
    /** **********************************************************************
     * check params folder constant exist
     * @test
     * @depends documentRootConstantExist
     ************************************************************************/
    public function paramsFolderConstantExist() : void
    {
        self::assertTrue
        (
            defined('PARAMS_FOLDER'),
            $this->getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'PARAMS_FOLDER'])
        );
        self::assertNotEquals
        (
            DOCUMENT_ROOT, PARAMS_FOLDER,
            'Params constant equals document root'
        );
    }
    /** **********************************************************************
     * check logs folder constant exist
     * @test
     * @depends documentRootConstantExist
     ************************************************************************/
    public function logsFolderConstantExist() : void
    {
        self::assertTrue
        (
            defined('LOGS_FOLDER'),
            $this->getMessage('CONSTANT_NOT_DEFINED', ['CONSTANT_NAME' => 'LOGS_FOLDER'])
        );
        self::assertNotEquals
        (
            DOCUMENT_ROOT, LOGS_FOLDER,
            'Logs constant equals document root'
        );
    }
}