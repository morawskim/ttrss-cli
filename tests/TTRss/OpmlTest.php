<?php

namespace ttrssCliTests\TTRss;

use ttrssCli\PHPUnit\MySQLXmlStringDataSet;
use ttrssCli\Services\TTRss;

class OpmlTest extends BasicDbTestCase
{
    /**
     * @runInSeparateProcess
     * @throws \ttrssCli\Exceptions\UserNotExist
     */
    public function testExportWithoutSettings()
    {
        $login = 'admin';

        $ttrss = new TTRss($GLOBALS['TTRSS_DIR']);
        $ttrss->init();
        $opmlString = $ttrss->exportOpml($login, false);

        $opml = new \DOMDocument();
        $result = $opml->loadXML($opmlString);
        $this->assertTrue($result);

        $document = new \DOMDocument();
        $result = $document->loadXML(file_get_contents(__DIR__ . '/../_files/TinyTinyRSSWithoutSettings.opml'));
        $this->assertTrue($result);

        $this->assertEquals($document->firstChild->nodeName, $opml->firstChild->nodeName);
        $this->assertEqualXMLStructure($document->firstChild, $opml->firstChild);
    }

    /**
     * @runInSeparateProcess
     * @throws \ttrssCli\Exceptions\UserNotExist
     */
    public function testExportWithSettings()
    {
        $login = 'admin';

        $ttrss = new TTRss($GLOBALS['TTRSS_DIR']);
        $ttrss->init();
        $opmlString = $ttrss->exportOpml($login, true);

        $opml = new \DOMDocument();
        $result = $opml->loadXML($opmlString);
        $this->assertTrue($result);

        $document = new \DOMDocument();
        $result = $document->loadXML(file_get_contents(__DIR__ . '/../_files/TinyTinyRSSWithSettings.opml'));
        $this->assertTrue($result);

        $this->assertEquals($document->firstChild->nodeName, $opml->firstChild->nodeName);
        $this->assertEqualXMLStructure($document->firstChild, $opml->firstChild);
    }

    /**
     * @runInSeparateProcess
     */
    public function testImportOpml()
    {
        $login = 'admin';

        $ttrss = new TTRss($GLOBALS['TTRSS_DIR']);
        $ttrss->init();
        $ttrss->importOpml($login, __DIR__ . '/../_files/import.opml');

        $ds = new MySQLXmlStringDataSet(__DIR__ . '/../_files/afterImportFeeds.xml');
        $actual = $this->getConnection()->createDataSet(['ttrss_feed_categories', 'ttrss_feeds']);

        $this->assertDataSetsEqual(
            $ds,
            $actual
        );
    }
}
