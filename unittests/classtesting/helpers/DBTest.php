<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers;

use
    Throwable,
    RuntimeException,
    PDO,
    SplFileInfo,
    UnitTests\Core\ExchangeTestCase,
    UnitTests\Core\TempDBRecordsGenerator,
    Main\Helpers\Config,
    Main\Helpers\DB;
/** ***********************************************************************************************
 * Test Main\Helpers\DB class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBTest extends ExchangeTestCase
{
    private static $tempDBTable =
    [
        'name'      => 'phpunit_test_table',
        'columns'   =>
        [
            'item_id'   => 'ID',
            'item_name' => 'NAME',
            'item_code' => 'CODE'
        ],
        'items'     => []
    ];
    /** @var TempDBRecordsGenerator */
    private static $tempDBRecordsGenerator  = null;
    /** **********************************************************************
     * construct
     ************************************************************************/
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $table      = self::$tempDBTable['name'];
        $idColumn   = self::$tempDBTable['columns']['item_id'];
        $nameColumn = self::$tempDBTable['columns']['item_name'];
        $codeColumn = self::$tempDBTable['columns']['item_code'];

        self::$tempDBRecordsGenerator = new TempDBRecordsGenerator;

        self::$tempDBRecordsGenerator->createTempTable($table,
        [
            'ID INT AUTO_INCREMENT',
            'NAME VARCHAR(255)',
            'CODE VARCHAR(255)',
            'PRIMARY KEY (ID)'
        ]);
        for ($index = 1; $index <= 10; $index++)
        {
            $item =
            [
                $idColumn   => $index,
                $nameColumn => "Item name $index",
                $codeColumn => "item_code_$index"
            ];
            self::$tempDBRecordsGenerator->createTempRecord($table, $item);
            self::$tempDBTable['items'][] = $item;
        }
    }
    /** **********************************************************************
     * destruct
     ************************************************************************/
    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();
        self::$tempDBRecordsGenerator->dropTempChanges();
    }
    /** **********************************************************************
     * check DB class is singleton
     *
     * @test
     ************************************************************************/
    public function isSingleton() : void
    {
        self::assertTrue
        (
            self::singletonImplemented(DB::class),
            self::getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => DB::class])
        );
    }
    /** **********************************************************************
     * check database params file exist
     *
     * @test
     * @return  SplFileInfo                     params file
     * @throws
     ************************************************************************/
    public function paramsFileExist() : SplFileInfo
    {
        $paramsFilePath = PARAMS_FOLDER.DS.'db.php';
        $paramsFile     = new SplFileInfo($paramsFilePath);

        self::assertFileIsReadable
        (
            $paramsFile->getPathname(),
            self::getMessage('NOT_READABLE', ['PATH' => $paramsFilePath])
        );

        return $paramsFile;
    }
    /** **********************************************************************
     * check database connection params exist
     *
     * @test
     * @depends paramsFileExist
     * @return  array                           connection params array
     * @throws
     ************************************************************************/
    public function dbConnectionParamsExist() : array
    {
        $config     = Config::getInstance();
        $dbName     = $config->getParam('db.name');
        $dbLogin    = $config->getParam('db.login');
        $dbPassword = $config->getParam('db.password');
        $dbHost     = $config->getParam('db.host');

        self::assertNotEmpty($dbName,       'DB name param no exist');
        self::assertNotEmpty($dbLogin,      'DB login param no exist');
        self::assertNotEmpty($dbPassword,   'DB password param no exist');
        self::assertNotEmpty($dbHost,       'DB host param no exist');

        return
        [
            'name'      => $dbName,
            'login'     => $dbLogin,
            'password'  => $dbPassword,
            'host'      => $dbHost
        ];
    }
    /** **********************************************************************
     * check PDO extension available
     *
     * @test
     * @throws
     ************************************************************************/
    public function pdoAvailable() : void
    {
        self::assertTrue
        (
            extension_loaded('PDO'),
            'PDO extension is unavailable'
        );
    }
    /** **********************************************************************
     * check database connection with PDO available
     *
     * @test
     * @depends dbConnectionParamsExist
     * @depends pdoAvailable
     * @param   array   $connectionParams       connection params
     * @return  PDO|null                        PDO
     * @throws
     ************************************************************************/
    public function connectionWithPDOAvailable(array $connectionParams) : ?PDO
    {
        $dbHost     = $connectionParams['host'];
        $dbName     = $connectionParams['name'];
        $dbLogin    = $connectionParams['login'];
        $dbPassword = $connectionParams['password'];

        try
        {
            $pdo = new PDO
            (
                "mysql:dbname=$dbName;host=$dbHost",
                $dbLogin,
                $dbPassword,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
                ]
            );

            self::assertTrue(true);
            return $pdo;
        }
        catch (Throwable $exception)
        {
            $error = $exception->getMessage();
            self::fail("Failed to create database connection with PDO: $error");
            return null;
        }
    }
    /** **********************************************************************
     * check exception with incomplete connection params
     *
     * @test
     * @depends paramsFileExist
     * @depends dbConnectionParamsExist
     * @depends isSingleton
     * @param   SplFileInfo $paramsFile         params file path
     * @param   array       $connectionParams   connection params
     * @throws
     ************************************************************************/
    public function exceptionOnIncompleteConnParams(SplFileInfo $paramsFile, array $connectionParams) : void
    {
        if ($paramsFile->isWritable())
        {
            $fileContentOrigin  = $paramsFile->openFile('r')->fread($paramsFile->getSize());
            $randParamValue     = $connectionParams[array_rand($connectionParams)];
            $fileContentChanged = str_replace($randParamValue, 'phpunit_testing', $fileContentOrigin);

            $paramsFile
                ->openFile('w')
                ->fwrite($fileContentChanged);

            try
            {
                self::resetSingletonInstance(DB::class);
                DB::getInstance();
                self::fail('Expect exception with incomplete DB connection params');
            }
            catch (RuntimeException $error)
            {
                self::assertTrue(true);
            }

            $paramsFile
                ->openFile('w')
                ->fwrite($fileContentOrigin);
        }
        else
        {
            self::markTestSkipped('Unable to rewrite db params file for testing');
        }
    }
    /** **********************************************************************
     * check providing correct database query result
     *
     * @test
     * @depends connectionWithPDOAvailable
     * @depends isSingleton
     * @param   PDO $unitTestPdo                unit test PDO connection object
     * @throws
     ************************************************************************/
    public function providingCorrectQuery(PDO $unitTestPdo) : void
    {
        $db                 = DB::getInstance();
        $table              = self::$tempDBTable['name'];
        $idColumn           = self::$tempDBTable['columns']['item_id'];
        $nameColumn         = self::$tempDBTable['columns']['item_name'];
        $codeColumn         = self::$tempDBTable['columns']['item_code'];
        $tempData           = self::$tempDBTable['items'];
        $tempDataRandomItem = $tempData[array_rand($tempData)];
        $sqlQueries         =
        [
            "SELECT *                      FROM $table",
            "SELECT $idColumn, $nameColumn FROM $table",
            "SELECT *                      FROM $table  WHERE     $idColumn = ? AND $codeColumn = ?",
            "SELECT $idColumn, $nameColumn FROM $table  WHERE     $idColumn IN (?, ?)",
            "SELECT *                      FROM $table  ORDER BY  $nameColumn   ASC",
            "SELECT *                      FROM $table  ORDER BY  $codeColumn   DESC"
        ];
        $sqlQueriesValues   =
        [
            2   => [$tempDataRandomItem[$idColumn], $tempDataRandomItem[$codeColumn]],
            3   => [2, rand(3, count($tempData))]
        ];

        foreach ($sqlQueries as $index => $sqlString)
        {
            $queryValues        = array_key_exists($index, $sqlQueriesValues) ? $sqlQueriesValues[$index] : [];
            $queryByUnitTestPdo = [];
            $queryByDBClass     = [];

            $statement = $unitTestPdo->prepare($sqlString);
            $statement->execute($queryValues);
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $item)
            {
                $queryByUnitTestPdo[] = $item;
            }

            $dbClassQuery = $db->query($sqlString, $queryValues);
            while (!$dbClassQuery->isEmpty())
            {
                $data       = $dbClassQuery->pop();
                $dataArray  = [];

                foreach ($data->getKeys() as $key)
                {
                    $dataArray[$key] = $data->get($key);
                }

                $queryByDBClass[] = $dataArray;
            }

            self::assertEquals
            (
                $queryByUnitTestPdo,
                $queryByDBClass,
                "Different query results on \"$sqlString\""
            );
        }
    }
    /** **********************************************************************
     * check providing correct database saving operation
     *
     * @test
     * @depends connectionWithPDOAvailable
     * @depends providingCorrectQuery
     * @depends isSingleton
     * @param   PDO $unitTestPdo                unit test PDO connection object
     * @return  int                             created test item id
     * @throws
     ************************************************************************/
    public function providingCorrectSaving(PDO $unitTestPdo) : int
    {
        $db                 = DB::getInstance();
        $table              = self::$tempDBTable['name'];
        $idColumn           = self::$tempDBTable['columns']['item_id'];
        $nameColumn         = self::$tempDBTable['columns']['item_name'];
        $codeColumn         = self::$tempDBTable['columns']['item_code'];
        $sqlGetLastItem     = "SELECT $idColumn FROM $table ORDER BY $idColumn DESC LIMIT 1";
        $sqlCreateNewItem   = "INSERT INTO $table ($nameColumn, $codeColumn) VALUES (?, ?)";
        $lastTestItemId     = 0;
        $newTestItemId      = 0;

        $queryResult = $unitTestPdo->query($sqlGetLastItem)->fetchAll(PDO::FETCH_ASSOC);
        if (count($queryResult) > 0)
        {
            $lastTestItemId = (int) $queryResult[0][$idColumn];
            $db->query($sqlCreateNewItem, ['Test name', 'Test code']);
            $newTestItemId = $db->getLastInsertId();
        }

        self::assertEquals
        (
            $lastTestItemId + 1,
            $newTestItemId,
            'DB saving process incorrect: expect get projected new record ID'
        );

        return $newTestItemId;
    }
    /** **********************************************************************
     * check providing correct database deleting operation
     *
     * @test
     * @depends connectionWithPDOAvailable
     * @depends providingCorrectSaving
     * @depends isSingleton
     * @param   PDO $unitTestPdo                unit test PDO connection object
     * @param   int $itemId                     created test item
     * @throws
     ************************************************************************/
    public function providingCorrectDeleting(PDO $unitTestPdo, int $itemId) : void
    {
        $db                 = DB::getInstance();
        $table              = self::$tempDBTable['name'];
        $idColumn           = self::$tempDBTable['columns']['item_id'];
        $sqlDropNewItem     = "DELETE FROM $table WHERE $idColumn = ?";
        $sqlGetDeletedItem  = "SELECT $idColumn FROM $table WHERE $idColumn = ?";

        $db->query($sqlDropNewItem, [$itemId]);
        $error = $db->getLastError();
        self::assertFalse
        (
            $db->hasLastError(),
            "Delete process failed: $error"
        );

        $statement = $unitTestPdo->prepare($sqlGetDeletedItem);
        $statement->execute([$itemId]);
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $item)
        {
            self::assertNotEquals
            (
                $itemId, $item[$idColumn],
                'Item was not deleted'
            );
        }
    }
    /** **********************************************************************
     * check providing database query last error
     *
     * @test
     * @depends providingCorrectQuery
     * @depends isSingleton
     ************************************************************************/
    public function providingQueryLastError() : void
    {
        $db = DB::getInstance();
        $db->query('Some incorrect sql query string');

        self::assertTrue
        (
            $db->hasLastError() && strlen($db->getLastError()) > 0,
            'No error provided with incorrect query'
        );
    }
    /** **********************************************************************
     * check PDO refresh last error after any queries
     *
     * @test
     * @depends providingCorrectQuery
     * @depends isSingleton
     * @throws
     ************************************************************************/
    public function checkLastErrorRefreshing() : void
    {
        $db             = DB::getInstance();
        $table          = self::$tempDBTable['name'];
        $sqlRead        = "SELECT * FROM $table";
        $firstError     = '';
        $secondError    = '';

        $db->query('Some incorrect query');
        if ($db->hasLastError())
        {
            $firstError = $db->getLastError();
        }
        $db->query($sqlRead);
        if ($db->hasLastError())
        {
            $secondError = $db->getLastError();
        }

        self::assertNotEquals
        (
            $firstError,
            $secondError,
            'Last error not refreshed after previous operation'
        );
    }
}