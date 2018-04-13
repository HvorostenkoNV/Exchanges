<?php
declare(strict_types=1);

use
    PHPUnit\Framework\Error\Error as FatalError,
    Main\Helpers\Config;
/** ***********************************************************************************************
 * Test Main\Helpers\Config class
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class ConfigClassTest extends ExchangeTestCase
{
    private
        $paramsFolderPermissions    = '540',
        $paramsFilesPermissions     = '440',
        $paramTestFolder            = 'unit_test',
        $paramTestFile              = 'params_test',
        $paramRenamedFolder         = 'params_unit_test',
        $testParams                 =
        [
            ['param1', 'value1'],
            ['param2', 'value2']
        ];
    /** **********************************************************************
     * Config is singleton
     * @test
     ************************************************************************/
    public function isSingleton() : void
    {
        self::assertTrue
        (
            $this->singletonImplemented(Config::class),
            $this->getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Config::class])
        );
    }
    /** **********************************************************************
     * params folder full test
     * @test
     * @return  string                  params folder path
     * @throws
     ************************************************************************/
    public function paramsFolderFullCheck() : string
    {
        $dirPermissions = $this->getPermissions(PARAMS_FOLDER);

        self::assertEquals
        (
            $this->paramsFolderPermissions, $dirPermissions,
            $this->getMessage('WRONG_PERMISSIONS',
            [
                'PATH'      => PARAMS_FOLDER,
                'NEED'      => $this->paramsFolderPermissions,
                'CURRENT'   => $dirPermissions
            ])
        );

        foreach ($this->findAllFiles(PARAMS_FOLDER) as $file)
        {
            $filePath           = $file->getPathname();
            $filePermissions    = $this->getPermissions($filePath);
            $fileExtension      = $file->getExtension();
            $fileContent        = include $filePath;

            self::assertEquals
            (
                $this->paramsFilesPermissions, $filePermissions,
                $this->getMessage('WRONG_PERMISSIONS',
                [
                    'PATH'      => $filePath,
                    'NEED'      => $this->paramsFilesPermissions,
                    'CURRENT'   => $filePermissions
                ])
            );
            self::assertEquals
            (
                'php', $fileExtension,
                $this->getMessage('WRONG_EXTENSION',
                [
                    'PATH'      => $filePath,
                    'NEED'      => 'php',
                    'CURRENT'   => $fileExtension
                ])
            );
            self::assertTrue
            (
                is_array($fileContent),
                $this->getMessage('FILE_MUST_RETURN_ARRAY', ['PATH' => $filePath])
            );
        }

        return PARAMS_FOLDER;
    }
    /** **********************************************************************
     * check Config can read params
     * @test
     * @depends paramsFolderFullCheck
     * @depends isSingleton
     * @param   string  $paramsPath     params folder path
     * @throws
     ************************************************************************/
    public function canReadParams(string $paramsPath) : void
    {
        if ($this->createTestParams($paramsPath))
        {
            $this->resetSingletonInstance(Config::class);

            $checkingParams =
            [
                $this->paramTestFolder.'.'.$this->paramTestFile.'.'.$this->testParams[0][0] => $this->testParams[0][1],
                $this->paramTestFolder.'.'.$this->paramTestFile.'.'.$this->testParams[1][0] => $this->testParams[1][1]
            ];

            foreach ($checkingParams as $index => $value)
                self::assertEquals
                (
                    $value, Config::getInstance()->getParam($index),
                    'Created test param "'.$index.'" not found or not equal test value "'.$value.'"'
                );

            $this->deleteTestParams($paramsPath);
        }
        else
            self::markTestSkipped('Unable to create test param file for testing');
    }
    /** **********************************************************************
     * expecting app crush with unavailable params folder
     * @test
     * @depends paramsFolderFullCheck
     * @depends isSingleton
     * @param   string  $paramsPath     params folder path
     * @throws
     ************************************************************************/
    public function crushedWithoutParamsFolder(string $paramsPath) : void
    {
        if (rename($paramsPath, $this->paramRenamedFolder))
        {
            $this->resetSingletonInstance(Config::class);

            try
            {
                Config::getInstance();
                self::fail('No crush without params folder');
            }
            catch (FatalError $error)
            {
                self::assertTrue(true);
            }

            rename($this->paramRenamedFolder, $paramsPath);
        }
        else
            self::markTestSkipped('Unable to rename param folder for testing');
    }
    /** **********************************************************************
     * creating test params
     * @param   string  $paramsPath     params folder path
     * @return  bool
     ************************************************************************/
    private function createTestParams(string $paramsPath) : bool
    {
        $paramsTestFolderPath   = $paramsPath.DS.$this->paramTestFolder;
        $paramsTestParamFile    = $paramsTestFolderPath.DS.$this->paramTestFile.'.php';

        if (!is_dir($paramsTestFolderPath) && is_writable($paramsPath))
            if (mkdir($paramsTestFolderPath))
                if (!file_exists($paramsTestParamFile))
                {
                    $file       = fopen($paramsTestParamFile, 'w');
                    $content    = '
                    <?php return
                    [
                        \''.$this->testParams[0][0].'\' => \''.$this->testParams[0][1].'\',
                        \''.$this->testParams[1][0].'\' => \''.$this->testParams[1][1].'\'
                    ];';

                    fwrite($file, $content);
                    fclose($file);
                }

        return file_exists($paramsTestParamFile);
    }
    /** **********************************************************************
     * deleting test params
     * @param   string  $paramsPath     params folder path
     ************************************************************************/
    private function deleteTestParams(string $paramsPath) : void
    {
        $paramsTestFolderPath   = $paramsPath.DS.$this->paramTestFolder;
        $paramsTestParamFile    = $paramsTestFolderPath.DS.$this->paramTestFile.'.php';

        if (is_file($paramsTestParamFile))
            if (unlink($paramsTestParamFile))
                if (is_dir($paramsTestFolderPath))
                    rmdir($paramsTestFolderPath);
    }
}