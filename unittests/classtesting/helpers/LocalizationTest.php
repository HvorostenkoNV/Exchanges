<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers;

use
    InvalidArgumentException,
    DomainException,
    SplFileInfo,
    UnitTests\AbstractTestCase,
    Main\Helpers\Config,
    Main\Helpers\Localization;
/** ***********************************************************************************************
 * Test Main\Helpers\Localization class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class LocalizationTest extends AbstractTestCase
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
        $locFolderParam = Config::getInstance()->getParam('structure.localizationFolder');

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
     * @return  SplFileInfo                     loc folder
     * @throws
     ************************************************************************/
    public function locFolderFullCheck(string $locFolderParam) : SplFileInfo
    {
        $locFolder = new SplFileInfo(DOCUMENT_ROOT.DIRECTORY_SEPARATOR.$locFolderParam);

        self::assertDirectoryIsReadable
        (
            $locFolder->getPathname(),
            self::getMessage('NOT_READABLE', ['PATH' => $locFolder->getPathname()])
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
     * @return  SplFileInfo                     default loc folder
     * @throws
     ************************************************************************/
    public function defaultLocFolderFullCheck(string $locFolder, string $defaultLangParam) : SplFileInfo
    {
        $defaultLangFolder = new SplFileInfo($locFolder.DIRECTORY_SEPARATOR.$defaultLangParam);

        self::assertDirectoryIsReadable
        (
            $defaultLangFolder->getPathname(),
            self::getMessage('NOT_READABLE', ['PATH' => $defaultLangFolder->getPathname()])
        );

        return $defaultLangFolder;
    }
    /** **********************************************************************
     * check throwing exception while construct with incorrect params
     *
     * @test
     * @return void
     * @throws
     ************************************************************************/
    public function exceptionWithIncorrectParams() : void
    {
        try
        {
            new Localization('');
            $exceptionName = InvalidArgumentException::class;
            self::fail("Expect $exceptionName exception on creating loc object with empty path argument");
        }
        catch (InvalidArgumentException $exception)
        {
            self::assertTrue(true);
        }
    }
    /** **********************************************************************
     * check throwing exception while construct when folder not exist
     *
     * @test
     * @depends locFolderFullCheck
     * @param   SplFileInfo $locFolder          loc folder
     * @return  void
     * @throws
     ************************************************************************/
    public function exceptionWithFolderNotExist(SplFileInfo $locFolder) : void
    {
        $wrongLocFolder = new SplFileInfo($locFolder->getPathname().DIRECTORY_SEPARATOR.'test');
        while ($wrongLocFolder->isDir())
        {
            $wrongLocFolder = new SplFileInfo($wrongLocFolder->getPathname().'1');
        }

        try
        {
            new Localization($wrongLocFolder->getBasename());
            $exceptionName = DomainException::class;
            self::fail("Expect $exceptionName exception on creating loc object with incorrect path argument");
        }
        catch (DomainException $exception)
        {
            self::assertTrue(true);
        }
    }
    /** **********************************************************************
     * check localization class can read params, added to loc folder
     *
     * @test
     * @depends locFolderFullCheck
     * @param   SplFileInfo $locFolder          loc folder
     * @return  void
     * @throws
     ************************************************************************/
    public function canReadParams(SplFileInfo $locFolder) : void
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
                    "Created test loc message not found or not equal seted"
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
     * creating temp application loc params
     *
     * @param   SplFileInfo $locFolder          loc folder
     * @return  bool
     ************************************************************************/
    private function createTempLocParams(SplFileInfo $locFolder) : bool
    {
        $locTestFolder  = new SplFileInfo($locFolder->getPathname().DIRECTORY_SEPARATOR.$this->locTestFolder);
        $locTestFile    = new SplFileInfo($locTestFolder->getPathname().DIRECTORY_SEPARATOR.$this->locTestFile.'.php');

        if (!$locTestFolder->isDir() && $locFolder->isWritable())
        {
            if (@mkdir($locTestFolder->getPathname()))
            {
                if (!$locTestFile->isFile())
                {
                    $firstParam         = $this->locTestParams[0][0];
                    $firstParamValue    = $this->locTestParams[0][1];
                    $secondParam        = $this->locTestParams[1][0];
                    $secondParamValue   = $this->locTestParams[1][1];
                    $content            = "
                    <?php return
                    [
                        '$firstParam'   => '$firstParamValue',
                        '$secondParam'  => '$secondParamValue'
                    ];";

                    $locTestFile
                        ->openFile('w')
                        ->fwrite($content);
                }
            }
        }

        return $locTestFile->isFile();
    }
    /** **********************************************************************
     * delete temp application loc params
     *
     * @param   SplFileInfo $locFolder          loc folder
     * @return  void
     ************************************************************************/
    private function dropTempLocParams(SplFileInfo $locFolder) : void
    {
        $locTestFolder  = new SplFileInfo($locFolder->getPathname().DIRECTORY_SEPARATOR.$this->locTestFolder);
        $locTestFile    = new SplFileInfo($locTestFolder->getPathname().DIRECTORY_SEPARATOR.$this->locTestFile.'.php');

        if ($locTestFile->isFile())
        {
            if (unlink($locTestFile->getPathname()))
            {
                if ($locTestFolder->isDir())
                {
                    rmdir($locTestFolder->getPathname());
                }
            }
        }
    }
}