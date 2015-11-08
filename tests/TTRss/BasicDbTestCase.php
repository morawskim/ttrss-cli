<?php

namespace ttrssCliTests\TTRss;

use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use ttrssCli\PHPUnit\MySQLXmlStringDataSet;

class BasicDbTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        $pdo = new \PDO($GLOBALS['DB_DNS'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
        return $this->createDefaultDBConnection($pdo, $GLOBALS['DB_SCHEMA']);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new MySQLXmlStringDataSet(__DIR__ . '/../_files/seed.xml');
    }

    /**
     * Returns the database operation executed in test cleanup.
     *
     * @return PHPUnit_Extensions_Database_Operation_DatabaseOperation
     */
    protected function getTearDownOperation()
    {
        return \PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
    }
}