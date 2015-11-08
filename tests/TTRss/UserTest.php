<?php

namespace TTRss;

use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use ttrssCli\PHPUnit\MySQLXmlStringDataSet;
use ttrssCli\Services\TTRss;

class UserTest extends \PHPUnit_Extensions_Database_TestCase
{
    protected $ds = null;

    public function testChangePassword()
    {
        $login = 'admin';
        $query = "SELECT * FROM ttrss_users WHERE login = '$login';";
        $oldDS = $this->getConnection()->createQueryTable('ttrss_users', $query);
        $oldRow = $oldDS->getRow(0);
        $oldHash = $oldRow['pwd_hash'];
        $oldSalt = $oldRow['salt'];

        $ttrss = new TTRss($GLOBALS['TTRSS_DIR']);
        $ttrss->init();
        $ttrss->changeUserPassword($login, 'newpassword');

        $newDS = $this->getConnection()->createQueryTable('ttrss_users', $query);
        $changeRow = $newDS->getRow(0);
        $newHash = $changeRow['pwd_hash'];
        $newSalt = $changeRow['salt'];

        $this->assertNotEquals($oldHash, $newHash);
        $this->assertNotEquals($oldSalt, $newSalt);
    }

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
     * @return \PHPUnit_Extensions_Database_Operation_DatabaseOperation
     */
    protected function getTearDownOperation()
    {
        return \PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
    }


}