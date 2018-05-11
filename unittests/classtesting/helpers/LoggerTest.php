<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers;

use
    SplFileInfo,
    UnitTests\Core\ExchangeTestCase,
    PHPUnit\Framework\Error\Error as FatalError,
    Main\Helpers\Logger;
/** ***********************************************************************************************
 * Test Main\Helpers\Logger class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class LoggerTest extends ExchangeTestCase
{
    /** **********************************************************************
     * check Logger is singleton
     *
     * @test
     * @throws
     ************************************************************************/
    public function isSingleton() : void
    {
        self::assertTrue
        (
            self::singletonImplemented(Logger::class),
            self::getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => Logger::class])
        );
    }
    /** **********************************************************************
     * logs folder full check
     *
     * @test
     * @return  SplFileInfo                 logs folder
     * @throws
     ************************************************************************/
    public function logsFolderFullCheck() : SplFileInfo
    {
        $logsFolder = new SplFileInfo(LOGS_FOLDER);

        self::assertDirectoryIsReadable
        (
            $logsFolder->getPathname(),
            self::getMessage('NOT_READABLE', ['PATH' => $logsFolder->getPathname()])
        );
        self::assertDirectoryIsWritable
        (
            $logsFolder->getPathname(),
            self::getMessage('NOT_WRITABLE', ['PATH' => $logsFolder->getPathname()])
        );

        foreach (self::getAllFiles($logsFolder) as $file)
        {
            $filePath       = $file->getPathname();
            $fileExtension  = $file->getExtension();

            self::assertEquals
            (
                'txt',
                $fileExtension,
                self::getMessage('WRONG_EXTENSION',
                [
                    'PATH'      => $filePath,
                    'NEED'      => 'txt',
                    'CURRENT'   => $fileExtension
                ])
            );
        }

        return $logsFolder;
    }
    /** **********************************************************************
     * check creating new log file
     *
     * @test
     * @depends logsFolderFullCheck
     * @depends isSingleton
     * @param   SplFileInfo $logsFolder     logs folder
     * @throws
     ************************************************************************/
    public function creatingLogFile(SplFileInfo $logsFolder) : void
    {
        $logger         = Logger::getInstance();
        $logsCountStart = count(self::getAllFiles($logsFolder));

        $logger->addNotice('Unit testing');
        $logger->write();

        self::assertEquals
        (
            $logsCountStart + 1,
            count(self::getAllFiles($logsFolder)),
            'Expected new created log file not found'
        );
    }
    /** **********************************************************************
     * check if new log file contains messages, that was seted
     *
     * @test
     * @depends logsFolderFullCheck
     * @depends isSingleton
     * @param   SplFileInfo $logsFolder         logs folder
     * @throws
     ************************************************************************/
    public function setedMessagesExistsInLogFile(SplFileInfo $logsFolder) : void
    {
        $logger             = Logger::getInstance();
        $testMessageNotice  = 'This is test notice';
        $testMessageWarning = 'This is test warning';
        $logContent         = '';

        $logger->addNotice($testMessageNotice);
        $logger->addWarning($testMessageWarning);
        $logger->write();

        foreach (scandir($logsFolder->getPathname(), SCANDIR_SORT_DESCENDING) as $fileName)
        {
            $file = new SplFileInfo($logsFolder->getPathname().DS.$fileName);
            $logContent  = $file->openFile('r')->fread($file->getSize());
            break;
        }

        foreach ([$testMessageNotice, $testMessageWarning] as $message)
        {
            self::assertContains
            (
                $message, $logContent,
                'Failed to find log file with seted test message'
            );
        }
    }
    /** **********************************************************************
     * check system shut down on seting ERROR
     *
     * @test
     * @depends isSingleton
     * @throws
     ************************************************************************/
    public function setingErrorStopsExecuting() : void
    {
        try
        {
            Logger::getInstance()->addError('UNIT TEST');
            self::fail('Expect shut down on call "addError" method');
        }
        catch (FatalError $error)
        {
            self::assertTrue(true);
        }
    }
}