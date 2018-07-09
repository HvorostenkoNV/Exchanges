<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers;

use
    PHPUnit\Framework\Error\Error as FatalError,
    SplFileInfo,
    UnitTests\AbstractTestCase,
    Main\Helpers\Config;
/** ***********************************************************************************************
 * Test Main\Helpers\Config class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ConfigTest extends AbstractTestCase
{
    private
        $paramsFolder       = PARAMS_FOLDER,
        $paramTestFolder    = 'unit_test',
        $paramTestFile      = 'params_test',
        $testParams         =
            [
                ['param1', 'value1'],
                ['param2', 'value2']
            ];
    /** **********************************************************************
     * check Config is singleton
     *
     * @test
     * @throws
     ************************************************************************/
    public function isSingleton() : void
    {
        self::assertTrue
        (
            self::singletonImplemented(Config::class),
            'Config class not implements singletone'
        );
    }
    /** **********************************************************************
     * check Config can read params
     *
     * @test
     * @depends isSingleton
     * @throws
     ************************************************************************/
    public function canReadParams() : void
    {
        if ($this->createTempParams())
        {
            self::resetSingletonInstance(Config::class);

            $checkingParams =
                [
                    $this->paramTestFolder.'.'.$this->paramTestFile.'.'.$this->testParams[0][0] => $this->testParams[0][1],
                    $this->paramTestFolder.'.'.$this->paramTestFile.'.'.$this->testParams[1][0] => $this->testParams[1][1]
                ];

            foreach ($checkingParams as $index => $value)
            {
                self::assertEquals
                (
                    $value,
                    Config::getInstance()->getParam($index),
                    'Created test param not found or not equal seted'
                );
            }

            $this->dropTempParams();
        }
        else
        {
            self::markTestSkipped('Unable to create test param file for testing');
        }
    }
    /** **********************************************************************
     * expecting application crush with unavailable params folder
     *
     * @test
     * @depends isSingleton
     * @throws
     ************************************************************************/
    public function crushedWithoutParamsFolder() : void
    {
        $paramsRenamedFolder = 'params_unit_test';

        if (rename($this->paramsFolder, $paramsRenamedFolder))
        {
            self::resetSingletonInstance(Config::class);

            try
            {
                Config::getInstance();
                self::fail('No crush without params folder');
            }
            catch (FatalError $error)
            {
                self::assertTrue(true);
            }

            rename($paramsRenamedFolder, $this->paramsFolder);
        }
        else
        {
            self::markTestSkipped('Unable to rename param folder for testing');
        }
    }
    /** **********************************************************************
     * creating temp application params
     *
     * @return  bool                        creating temp params success
     ************************************************************************/
    private function createTempParams() : bool
    {
        $paramsFolder       = new SplFileInfo($this->paramsFolder);
        $paramsTestFolder   = new SplFileInfo($paramsFolder->getPathname().DIRECTORY_SEPARATOR.$this->paramTestFolder);
        $paramsTestFile     = new SplFileInfo($paramsTestFolder->getPathname().DIRECTORY_SEPARATOR.$this->paramTestFile.'.php');

        if (!$paramsTestFolder->isDir() && $paramsFolder->isWritable())
        {
            if (mkdir($paramsTestFolder->getPathname()))
            {
                if (!$paramsTestFile->isFile())
                {
                    $firstParam         = $this->testParams[0][0];
                    $firstParamValue    = $this->testParams[0][1];
                    $secondParam        = $this->testParams[1][0];
                    $secondParamValue   = $this->testParams[1][1];
                    $content            = "
                    <?php return
                        [
                            '$firstParam'   => '$firstParamValue',
                            '$secondParam'  => '$secondParamValue'
                        ];";

                    $paramsTestFile
                        ->openFile('w')
                        ->fwrite($content);
                }
            }
        }

        return $paramsTestFile->isFile();
    }
    /** **********************************************************************
     * delete temp application params
     ************************************************************************/
    private function dropTempParams() : void
    {
        $paramsFolder       = new SplFileInfo($this->paramsFolder);
        $paramsTestFolder   = new SplFileInfo($paramsFolder->getPathname().DIRECTORY_SEPARATOR.$this->paramTestFolder);
        $paramsTestFile     = new SplFileInfo($paramsTestFolder->getPathname().DIRECTORY_SEPARATOR.$this->paramTestFile.'.php');

        if ($paramsTestFile->isFile())
        {
            if (unlink($paramsTestFile->getPathname()))
            {
                if ($paramsTestFolder->isDir())
                {
                    rmdir($paramsTestFolder->getPathname());
                }
            }
        }
    }
}