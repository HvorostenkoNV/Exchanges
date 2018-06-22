<?php
declare(strict_types=1);

namespace UnitTests\ClassTesting\Helpers;

use
    UnitTests\AbstractTestCase,
    Main\Helpers\DB;
/** ***********************************************************************************************
 * Test Main\Helpers\DB class
 *
 * @package exchange_unit_tests
 * @author  Hvorostenko
 *************************************************************************************************/
final class DBTest extends AbstractTestCase
{
    private static
        $tempDBTableName    = 'phpunit_test_table',
        $tempDBTableColumns =
        [
            'item_id'   => 'ID',
            'item_name' => 'NAME',
            'item_code' => 'CODE'
        ];
    /** **********************************************************************
     * check DB class is singleton
     *
     * @test
     * @return  DB                          DB object
     * @throws
     ************************************************************************/
    public function isSingleton() : DB
    {
        self::assertTrue
        (
            self::singletonImplemented(DB::class),
            self::getMessage('SINGLETON_IMPLEMENTATION_FAILED', ['CLASS_NAME' => DB::class])
        );

        return DB::getInstance();
    }
    /** **********************************************************************
     * check creating table process
     *
     * @test
     * @depends isSingleton
     * @param   DB $db                      DB object
     * @throws
     ************************************************************************/
    public function creatingTable(DB $db) : void
    {
        $table                  = self::$tempDBTableName;
        $idColumn               = self::$tempDBTableColumns['item_id'];
        $nameColumn             = self::$tempDBTableColumns['item_name'];
        $codeColumn             = self::$tempDBTableColumns['item_code'];
        $createTableSqlQuery    = "
            CREATE TABLE $table
            (
                $idColumn INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                $nameColumn VARCHAR(255),
                $codeColumn VARCHAR(255)
            )";

        $db->query($createTableSqlQuery);
        $tableQuery = $db->query('SHOW TABLES');
        while ($tableQuery->count() > 0)
        {
            $row    = $tableQuery->pop();
            $value  = $row->get($row->getKeys()[0]);

            if ($value == $table)
            {
                self::assertTrue(true);
                return;
            }
        }

        self::fail('Temp DB table was not created');
    }
    /** **********************************************************************
     * check read/write records process
     *
     * @test
     * @depends isSingleton
     * @depends creatingTable
     * @param   DB $db                      DB object
     * @throws
     ************************************************************************/
    public function readWriteRecords(DB $db) : void
    {
        $table          = self::$tempDBTableName;
        $nameColumn     = self::$tempDBTableColumns['item_name'];
        $codeColumn     = self::$tempDBTableColumns['item_code'];
        $tempItems      = [];
        $createdItems   = [];

        for ($index = 1; $index <= 5; $index++)
        {
            $itemName   = "Item-$index";
            $itemCode   = "item_$index";

            $tempItems[] =
            [
                $nameColumn => $itemName,
                $codeColumn => $itemCode
            ];

            $db->query("INSERT INTO $table ($nameColumn, $codeColumn) VALUES (?, ?)", [$itemName, $itemCode]);
        }

        $queryResult = $db->query("SELECT $nameColumn, $codeColumn FROM $table");
        while ($queryResult->count() > 0)
        {
            $item = $queryResult->pop();
            $createdItems[] =
            [
                $nameColumn => $item->get($nameColumn),
                $codeColumn => $item->get($codeColumn)
            ];
        }

        self::assertEquals
        (
            $tempItems,
            $createdItems,
            'Expect get same DB records as temp created'
        );
    }
    /** **********************************************************************
     * check providing last inserted item ID
     *
     * @test
     * @depends isSingleton
     * @depends readWriteRecords
     * @param   DB $db                      DB object
     * @throws
     ************************************************************************/
    public function gettingLastInsertedId(DB $db) : void
    {
        $table      = self::$tempDBTableName;
        $nameColumn = self::$tempDBTableColumns['item_name'];
        $codeColumn = self::$tempDBTableColumns['item_code'];

        $db->query("INSERT INTO $table ($nameColumn, $codeColumn) VALUES (?, ?)", ['someName', 'someCode']);
        $firstInsertedId = $db->getLastInsertId();
        $db->query("INSERT INTO $table ($nameColumn, $codeColumn) VALUES (?, ?)", ['someName', 'someCode']);
        $secondInsertedId = $db->getLastInsertId();

        self::assertEquals
        (
            $secondInsertedId,
            $firstInsertedId + 1,
            'Last inserted ID is not as expected'
        );

        $db->query("SELECT * FORM $table");
        self::assertEquals
        (
            $db->getLastInsertId(),
            0,
            'Expect get zero on calling "getting last inserted ID" after running not inserting operation'
        );
    }
    /** **********************************************************************
     * check providing database query last error
     *
     * @test
     * @depends isSingleton
     * @depends readWriteRecords
     * @param   DB $db                      DB object
     * @throws
     ************************************************************************/
    public function gettingQueryLastError(DB $db) : void
    {
        $db->query('Some incorrect sql query string');

        self::assertTrue
        (
            $db->hasLastError() && strlen($db->getLastError()) > 0,
            'No error provided with incorrect query'
        );
    }
    /** **********************************************************************
     * check refreshing last error process after any operation
     *
     * @test
     * @depends isSingleton
     * @depends readWriteRecords
     * @param   DB $db                      DB object
     * @throws
     ************************************************************************/
    public function lastErrorRefreshing(DB $db) : void
    {
        $table = self::$tempDBTableName;

        $db->query('Some incorrect query');
        $firstError = $db->getLastError();

        $db->query("SELECT * FROM $table");
        $secondError = $db->getLastError();

        self::assertNotEquals
        (
            $firstError,
            $secondError,
            'Last error not refreshed after previous operation'
        );
    }
    /** **********************************************************************
     * check delete records process
     *
     * @test
     * @depends isSingleton
     * @depends readWriteRecords
     * @param   DB $db                      DB object
     * @throws
     ************************************************************************/
    public function deleteRecords(DB $db) : void
    {
        $table      = self::$tempDBTableName;
        $idColumn   = self::$tempDBTableColumns['item_id'];
        $tableItems = [];

        $queryResult = $db->query("SELECT $idColumn FROM $table");
        while ($queryResult->count() > 0)
        {
            $tableItems[] = $queryResult->pop()->get($idColumn);
        }

        while (count($tableItems) > 0)
        {
            $itemId = array_pop($tableItems);
            $db->query("DELETE FROM $table WHERE $idColumn = ?", [$itemId]);
            $dbActualItemsCount = $db->query("SELECT $idColumn FROM $table")->count();

            self::assertEquals
            (
                count($tableItems),
                $dbActualItemsCount,
                'Expect get items count less by one after drop operation'
            );
        }
    }
    /** **********************************************************************
     * check delete table process
     *
     * @test
     * @depends isSingleton
     * @depends deleteRecords
     * @param   DB $db                      DB object
     * @throws
     ************************************************************************/
    public function deleteTable(DB $db) : void
    {
        $table = self::$tempDBTableName;

        $db->query("DROP TABLE $table");
        $tableQuery = $db->query('SHOW TABLES');
        while ($tableQuery->count() > 0)
        {
            $row    = $tableQuery->pop();
            $value  = $row->get($row->getKeys()[0]);

            if ($value == $table)
            {
                self::fail('Temp DB table was not deleted');
                return;
            }
        }

        self::assertTrue(true);
    }
}