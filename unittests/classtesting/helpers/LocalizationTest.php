<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers;

use
    InvalidArgumentException,
    DomainException,
    UnitTests\Core\ExchangeTestCase,
    Main\Helpers\Config,
    Main\Helpers\Localization;
/** ***********************************************************************************************
 * Test Main\Helpers\Localization class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class LocalizationTest extends ExchangeTestCase
{
    private
        $locTestFolder  = 'unit_test',
        $locTestFile    = 'loc_test',
        $locTestParams  =
        [
            ['param1', 'value1'],
            ['param2', 'value2']
        ];
    /** **********************************************************************
     * check loc folder param exist
     *
     * @test
     * @return  string                          loc folder param value
     * @throws
     ************************************************************************/
    public function locFolderParamExist() : string
    {
        $locFolderParam = Config::getInstance()->getParam('main.localizationFolder');

        self::assertNotEmpty
        (
            $locFolderParam,
            'Loc folder param is not defined'
        );

        return $locFolderParam;
    }
    /** **********************************************************************
     * check default loc folder param exist
     *
     * @test
     * @return  string                          default loc folder param value
     * @throws
     ************************************************************************/
    public function defaultLocFolderParamExist() : string
    {
        $defaultLangParam = Config::getInstance()->getParam('main.defaultLang');

        self::assertNotEmpty
        (
            $defaultLangParam,
            'Default loc folder param is not defined'
        );

        return $defaultLangParam;
    }
    /** **********************************************************************
     * loc folder full check
     *
     * @test
     * @depends locFolderParamExist
     * @param   string  $locFolderParam         loc folder param value
     * @return  string                          loc folder path
     * @throws
     ************************************************************************/
    public function locFolderFullCheck(string $locFolderParam) : string
    {
        $locFolder = DOCUMENT_ROOT.DS.$locFolderParam;

        self::assertDirectoryIsReadable
        (
            $locFolder,
            self::getMessage('NOT_READABLE', ['PATH' => $locFolder])
        );

        foreach (self::getAllFiles($locFolder) as $file)
        {
            $filePath       = $file->getPathname();
            $fileExtension  = $file->getExtension();
            $fileContent    = include $filePath;

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
            self::assertFileIsReadable
            (
                $filePath,
                self::getMessage('NOT_READABLE', ['PATH' => $filePath])
            );
            self::assertTrue
            (
                is_array($fileContent),
                self::getMessage('FILE_MUST_RETURN_ARRAY', ['PATH' => $filePath])
            );
        }

        return $locFolder;
    }
    /** **********************************************************************
     * default loc folder full check
     *
     * @test
     * @depends locFolderFullCheck
     * @depends defaultLocFolderParamExist
     * @param   string  $locFolder              loc folder path
     * @param   string  $defaultLangParam       default loc folder param value
     * @return  string                          default loc folder path
     * @throws
     ************************************************************************/
    public function defaultLocFolderFullCheck(string $locFolder, string $defaultLangParam) : string
    {
        $defaultLangFolder = $locFolder.DS.$defaultLangParam;

        self::assertDirectoryIsReadable
        (
            $defaultLangFolder,
            self::getMessage('NOT_READABLE', ['PATH' => $defaultLangFolder])
        );

        return $defaultLangFolder;
    }
    /** **********************************************************************
     * check throwing exception while construct with incorrect params
     *
     * @test
     ************************************************************************/
    public function exceptionWithIncorrectParams() : void
    {
        try
        {
            new Localization('');
            self::fail('Expect '.InvalidArgumentException::class.' exception on creating loc object with empty path argument');
        }
        catch (InvalidArgumentException $error)
        {
            self::assertTrue(true);
        }
    }
    /** **********************************************************************
     * check throwing exception while construct when folder not exist
     *
     * @test
     * @depends locFolderFullCheck
     * @param   string  $locFolder              loc folder path
     * @throws
     ************************************************************************/
    public function exceptionWithFolderNotExist(string $locFolder) : void
    {
        $wrongLocFolder = 'test';
        while (is_dir($locFolder.DS.$wrongLocFolder))
        {
            $wrongLocFolder .= '1';
        }

        try
        {
            new Localization($wrongLocFolder);
            self::fail('Expect '.DomainException::class.' exception on creating loc object with incorrect path argument');
        }
        catch (DomainException $error)
        {
            self::assertTrue(true);
        }
    }
    /** **********************************************************************
     * check localization class can read params, added to loc folder
     *
     * @test
     * @depends locFolderFullCheck
     * @param   string  $locFolder              loc folder path
     * @throws
     ************************************************************************/
    public function canReadParams(string $locFolder) : void
    {
        if ($this->createTempLocParams($locFolder))
        {
            $localization   = new Localization($this->locTestFolder);
            $checkingParams =
            [
                $this->locTestFile.'.'.$this->locTestParams[0][0] => $this->locTestParams[0][1],
                $this->locTestFile.'.'.$this->locTestParams[1][0] => $this->locTestParams[1][1]
            ];

            foreach ($checkingParams as $index => $value)
            {
                self::assertEquals
                (
                    $value,
                    $localization->getMessage($index),
                    'Created test loc message "'.$index.'" not found or not equal test value "'.$value.'"'
                );
            }

            $this->dropTempLocParams($locFolder);
        }
        else
        {
            self::markTestSkipped('Unable to create test loc file for testing');
        }
    }
    /** **********************************************************************
     * creating temp loc params
     *
     * @param   string  $locFolder              loc folder path
     * @return  bool
     ************************************************************************/
    private function createTempLocParams(string $locFolder) : bool
    {
        $locTestFolderPath  = $locFolder.DS.$this->locTestFolder;
        $locTestParamFile   = $locTestFolderPath.DS.$this->locTestFile.'.php';

        if (!is_dir($locTestFolderPath) && is_writable($locFolder))
        {
            if (mkdir($locTestFolderPath))
            {
                if (!file_exists($locTestParamFile))
                {
                    $file       = fopen($locTestParamFile, 'w');
                    $content    = '
                    <?php return
                    [
                        \''.$this->locTestParams[0][0].'\' => \''.$this->locTestParams[0][1].'\',
                        \''.$this->locTestParams[1][0].'\' => \''.$this->locTestParams[1][1].'\'
                    ];';

                    fwrite($file, $content);
                    fclose($file);
                }
            }
        }

        return file_exists($locTestParamFile);
    }
    /** **********************************************************************
     * deleting temp loc params
     *
     * @param   string  $locFolder              loc folder path
     ************************************************************************/
    private function dropTempLocParams(string $locFolder) : void
    {
        $locTestFolderPath  = $locFolder.DS.$this->locTestFolder;
        $locTestParamFile   = $locTestFolderPath.DS.$this->locTestFile.'.php';

        if (is_file($locTestParamFile))
        {
            if (unlink($locTestParamFile))
            {
                if (is_dir($locTestFolderPath))
                {
                    rmdir($locTestFolderPath);
                }
            }
        }
    }
}