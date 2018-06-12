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
        $paramsFolderPermissions    = '540',
        $paramsFilesPermissions     = '440',
        $paramTestFolder            = 'unit_test',
        $paramTestFile              = 'params_test',
        $paramsRenamedFolder        = 'params_unit_test',
        $testParams                 =
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
            self::getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Config::class])
        );
    }
    /** **********************************************************************
     * params folder full check
     *
     * @test
     * @return  SplFileInfo                     params folder
     * @throws
     ************************************************************************/
    public function paramsFolderFullCheck() : SplFileInfo
    {
        $paramsFolder   = new SplFileInfo(PARAMS_FOLDER);
        $dirPermissions = self::getPermissions($paramsFolder->getPathname());

        self::assertEquals
        (
            $this->paramsFolderPermissions,
            $dirPermissions,
            self::getMessage('WRONG_PERMISSIONS',
            [
                'PATH'      => $paramsFolder->getPathname(),
                'NEED'      => $this->paramsFolderPermissions,
                'CURRENT'   => $dirPermissions
            ])
        );

        foreach (self::getAllFiles($paramsFolder) as $file)
        {
            $filePath           = $file->getPathname();
            $filePermissions    = self::getPermissions($filePath);
            $fileExtension      = $file->getExtension();
            $fileContent        = include $filePath;

            self::assertEquals
            (
                $this->paramsFilesPermissions,
                $filePermissions,
                self::getMessage('WRONG_PERMISSIONS',
                [
                    'PATH'      => $filePath,
                    'NEED'      => $this->paramsFilesPermissions,
                    'CURRENT'   => $filePermissions
                ])
            );
            self::assertEquals
            (
                'php',
                $fileExtension,
                self::getMessage('WRONG_EXTENSION',
                [
                    'PATH'      => $filePath,
                    'NEED'      => 'php',
                    'CURRENT'   => $fileExtension
                ])
            );
            self::assertTrue
            (
                is_array($fileContent),
                self::getMessage('FILE_MUST_RETURN_ARRAY', ['PATH' => $filePath])
            );
        }

        return $paramsFolder;
    }
    /** **********************************************************************
     * check Config can read params
     *
     * @test
     * @depends paramsFolderFullCheck
     * @depends isSingleton
     * @param   SplFileInfo $paramsFolder       params folder
     * @throws
     ************************************************************************/
    public function canReadParams(SplFileInfo $paramsFolder) : void
    {
        if ($this->createTempParams($paramsFolder))
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
                    "Created test param not found or not equal seted"
                );
            }

            $this->dropTempParams($paramsFolder);
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
     * @depends paramsFolderFullCheck
     * @depends isSingleton
     * @param   SplFileInfo $paramsFolder       params folder
     * @throws
     ************************************************************************/
    public function crushedWithoutParamsFolder(SplFileInfo $paramsFolder) : void
    {
        if (rename($paramsFolder->getPathname(), $this->paramsRenamedFolder))
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

            rename($this->paramsRenamedFolder, $paramsFolder->getPathname());
        }
        else
        {
            self::markTestSkipped('Unable to rename param folder for testing');
        }
    }
    /** **********************************************************************
     * creating temp application params
     *
     * @param   SplFileInfo $paramsFolder       params folder
     * @return  bool
     ************************************************************************/
    private function createTempParams(SplFileInfo $paramsFolder) : bool
    {
        $paramsTestFolder   = new SplFileInfo($paramsFolder->getPathname().DS.$this->paramTestFolder);
        $paramsTestFile     = new SplFileInfo($paramsTestFolder->getPathname().DS.$this->paramTestFile.'.php');

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
     *
     * @param   SplFileInfo $paramsFolder       params folder
     ************************************************************************/
    private function dropTempParams(SplFileInfo $paramsFolder) : void
    {
        $paramsTestFolder   = new SplFileInfo($paramsFolder->getPathname().DS.$this->paramTestFolder);
        $paramsTestFile     = new SplFileInfo($paramsTestFolder->getPathname().DS.$this->paramTestFile.'.php');

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