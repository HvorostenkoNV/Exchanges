<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers;

use
    RuntimeException,
    InvalidArgumentException,
    SplFileInfo,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempFilesGenerator,
    Main\Helpers\MarkupData\XML,
    Main\Exchange\Participants\FieldsTypes\Manager as FieldsTypesManager;
/** ***********************************************************************************************
 * Test Main\Helpers\MarkupData\XML class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class XMLTest extends ExchangeTestCase
{
    /** **********************************************************************
     * check XML reading
     *
     * @test
     * @throws
     ************************************************************************/
    public function readingXml() : void
    {
        $tempFilesGenerator = new TempFilesGenerator;
        $randomData         = $this->getRandomData();
        $tempXml            = $tempFilesGenerator->createTempXml($randomData);
        $xml                = new XML($tempXml);

        self::assertEquals
        (
            $randomData,
            $xml->read(),
            'Expect get same xml data as was saved into file'
        );

        $tempFilesGenerator->dropCreatedTempData();
    }
    /** **********************************************************************
     * check empty XML reading
     *
     * @test
     * @depends readingXml
     * @throws
     ************************************************************************/
    public function readingEmptyXml() : void
    {
        $tempFilesGenerator = new TempFilesGenerator;
        $tempXml            = $tempFilesGenerator->createTempXml([]);
        $xml                = new XML($tempXml);

        self::assertEquals
        (
            [],
            $xml->read(),
            'Expect get empty array on reading empty XML'
        );

        $tempFilesGenerator->dropCreatedTempData();
    }
    /** **********************************************************************
     * check incorrect XML reading
     *
     * @test
     * @depends readingXml
     * @throws
     ************************************************************************/
    public function readingIncorrectXml() : void
    {
        $xml = new XML(new SplFileInfo('someIncorrectPath'));

        try
        {
            $exceptionName = RuntimeException::class;
            $xml->read();
            self::fail("Expect get \"$exceptionName\" on trying to read incorrect XML");
        }
        catch (RuntimeException $exception)
        {
            self::assertTrue(true);
        }
    }
    /** **********************************************************************
     * check XML writing
     *
     * @test
     * @throws
     ************************************************************************/
    public function writing() : void
    {
        $tempFilesGenerator = new TempFilesGenerator;
        $randomData         = $this->getRandomData();
        $tempXml            = $tempFilesGenerator->createTempXml($randomData);
        $tempXmlForFilling  = $tempFilesGenerator->createTempXml([]);
        $xmlMarkupData      = new XML($tempXmlForFilling);
        $writingResult      = $xmlMarkupData->write($randomData);

        $tempXmlContent             = $tempXml          ->openFile('r')->fread($tempXml          ->getSize());
        $tempXmlForFillingContent   = $tempXmlForFilling->openFile('r')->fread($tempXmlForFilling->getSize());

        self::assertTrue
        (
            $writingResult,
            'Expect get success XML writing result'
        );
        self::assertEquals
        (
            $tempXmlContent,
            $tempXmlForFillingContent,
            'Expect get xml content as in example xml'
        );

        $tempFilesGenerator->dropCreatedTempData();
    }
    /** **********************************************************************
     * get random data for writing into XML
     *
     * @return  array                       data
     ************************************************************************/
    private function getRandomData() : array
    {
        $result     = [];
        $dataSize   = rand(30, 50);

        $currentArray = &$result;
        while ($dataSize > 0)
        {
            $addSubArray = rand(0, 3) === 0 && $dataSize !== 1;

            if ($addSubArray)
            {
                $currentArray["value-$dataSize"] = [];
                $currentArray = &$currentArray["value-$dataSize"];
            }
            else
            {
                try
                {
                    $value = FieldsTypesManager::getField('string')->getRandomValue();
                    $currentArray["value-$dataSize"] = $value;
                }
                catch (InvalidArgumentException $exception)
                {

                }
            }

            $dataSize--;
        }

        return $result;
    }
}