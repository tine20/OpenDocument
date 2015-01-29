<?php
/**
 * OpenDocument - http://www.tine20.org/
 *
 * @package     Document
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2015 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Cornelius Weiß <c.weiss@metaways.de>
 */

/**
 * Test class for OpenDocument_SpreadSheet_TableTests
 */
class OpenDocument_SpreadSheet_TableTests extends PHPUnit_Framework_TestCase
{

    /**
     * @var OpenDocument_SpreadSheet_Table
     */
    protected $_rowTestTable;

    public function setup()
    {
        $document = new OpenDocument_Document(OpenDocument_Document::SPREADSHEET, __DIR__ . '/../documents/TableTest.ods');
        $spreadSheet = $document->getBody();
        $this->_rowTestTable = $spreadSheet->getTable('RowTests');
    }

    public function testGetRowCount()
    {
        $this->assertEquals(6, $this->_rowTestTable->getRowCount());
    }

    public function testGetRow()
    {
        $row = $this->_rowTestTable->getRow(3);
        $this->assertInstanceOf('OpenDocument_SpreadSheet_Row', $row);

        $textNodes = $row->getBody()->xpath("*/text:p");
        $this->assertEquals(1, count($textNodes), 'textNode missmatch');

        $text = (string) $textNodes[0];
        $this->assertEquals('Row3', $text);
    }

    public function testDeleteRow()
    {
        $row = $this->_rowTestTable->getRow(3);
        $this->_rowTestTable->deleteRow($row);

        $this->assertEquals(5, $this->_rowTestTable->getRowCount());


        $this->assertEquals(null, OpenDocument_Node::getNodeId($row->getBody()));
    }
}