<?php

namespace ttrssCli\PHPUnit;

class MySQLXmlStringDataSet extends \PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet
{
    public function __construct($xmlFile)
    {
        if (!is_file($xmlFile)) {
            throw new \InvalidArgumentException(
                "Could not find xml file: {$xmlFile}"
            );
        }

        $xmlString = file_get_contents($xmlFile);
        $libxmlErrorReporting  = libxml_use_internal_errors(TRUE);
        $this->xmlFileContents = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

        if (!$this->xmlFileContents) {
            $message = '';

            foreach (libxml_get_errors() as $error) {
                $message .= print_r($error, true);
            }

            throw new \RuntimeException($message);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($libxmlErrorReporting);

        $tableColumns = array();
        $tableValues  = array();

        $this->getTableInfo($tableColumns, $tableValues);
        $this->createTables($tableColumns, $tableValues);
    }
}