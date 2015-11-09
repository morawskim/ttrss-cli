<?php

namespace TTRss;

use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use ttrssCli\PHPUnit\MySQLXmlStringDataSet;
use ttrssCli\Services\TTRss;
use ttrssCliTests\TTRss\BasicDbTestCase;

class UserTest extends BasicDbTestCase
{
    protected $ds = null;

    /**
     * @runInSeparateProcess
     * @throws \ttrssCli\Exceptions\UserNotExist
     */
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
     * @runInSeparateProcess
     * @throws \ttrssCli\Exceptions\UserNotExist
     */
    public function testChangeEmail()
    {
        $login = 'admin';
        $email = 'test@test.example';

        $ttrss = new TTRss($GLOBALS['TTRSS_DIR']);
        $ttrss->init();
        $ttrss->changeUserEmail($login, $email);

        $ds = new MySQLXmlStringDataSet(__DIR__ . '/../_files/changeUserEmail.xml');
        $this->assertTablesEqual(
            $ds->getTable('ttrss_users'),
            $this->getConnection()->createDataSet(['ttrss_users'])->getTable('ttrss_users')
        );
    }
}