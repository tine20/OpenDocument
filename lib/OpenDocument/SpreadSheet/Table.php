<?php
/**
 * Tine 2.0
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright   Copyright (c) 2009-2013 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 */

/**
 * create opendocument files
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 */
 
class OpenDocument_SpreadSheet_Table extends OpenDocument_Node implements Iterator, Countable
{
    protected $_rows = array();
    
    protected $_columns = array();
    
    protected $_position = 0;
    
    /**
     * holds the representing xml
     * 
     * @var SimpleXMLElement
     */
    protected $_table;
    
    public function __construct(SimpleXMLElement $_table, $_parent = null)
    {
        $this->_table = $_table;

        if ($_parent) {
            try {
                self::registerNode($this, self::getNode($_parent));
            } catch (Exception $e) {
                // parent might have been created outside our hierarchy
                // also the spreadsheet table pattern is a mess
            }
        }
    }
    
    public function getBody()
    {
        return $this->_table;
    }

    /**
     * get cont of existing rows
     *
     * @return integer
     */
    public function getRowCount()
    {
        return count($this->_table->xpath("//*/table:table-row"));
    }

    /**
     * get existing row by position
     *
     * @param integer $position
     * @return OpenDocument_SpreadSheet_Row|null
     */
    public function getRow($position)
    {
        if ($position instanceof OpenDocument_SpreadSheet_Row) {
            return $position;
        }

        $row = $this->_table->xpath("//*/table:table-row[position()=$position]");

        if (count($row) === 0) {
            return FALSE;
        }

        //@TODO try to find row in registry first!
        return new OpenDocument_SpreadSheet_Row($row[0], $this);
    }

    /**
     * add new row and return reference
     *
     * @param string|optional $_tableName
     * @return OpenDocument_SpreadSheet_Row
     */
    public function appendRow($_styleName = null)
    {
        $row = OpenDocument_SpreadSheet_Row::createRow($this->_table, $_styleName);

        return $row;
    }
    
    /**
     * inserts new row and return reference
     *
     * @param string|optional $_tableName
     * @return OpenDocument_SpreadSheet_Row
     */
    public function insertRow($referenceRow, $position = 'after', $styleName = null)
    {
        $row = OpenDocument_SpreadSheet_Row::createRow($this->_table, $styleName, $referenceRow, $position);

        return $row;
    }

    /**
     * delete given row
     *
     * @param integer|OpenDocument_SpreadSheet_Row
     */
    public function deleteRow($row)
    {
        $row = $this->getRow($row);
        if (! $row instanceof OpenDocument_SpreadSheet_Row) {
            throw new Exception('Row does not exists');
        }

        self::unregisterNode($row);
        unset($row->getBody()->{0});


    }

    /**
     * sets the title of the table
     * 
     * @param string $tile
     */
    public function setTitle($title)
    {
        $this->_table->attributes(OpenDocument_Document::NS_TABLE)->name = $title;
    }
    
    /**
     * add new column and return reference
     *
     * @param string|optional $_tableName
     * @return OpenDocument_SpreadSheet_Row
     */
    public function appendColumn($_styleName = null)
    {
        $row = OpenDocument_SpreadSheet_Column::createColunm($this->_table, $_styleName);
    
        return $row;
    }



    /**
     * creates a table
     * 
     * @param SimpleXMLElement $_parent
     * @param string $_tableName
     * @param string $_styleName
     * @return OpenDocument_SpreadSheet_Table
     */
    static public function createTable(SimpleXMLElement $_parent, $_tableName, $_styleName = null)
    {
        $tableElement = $_parent->addChild('table', null, OpenDocument_Document::NS_TABLE);
        $tableElement->addAttribute('table:name', $_tableName, OpenDocument_Document::NS_TABLE);
        
        if ($_styleName !== null) {
            $tableElement->addAttribute('table:style-name', $_styleName, OpenDocument_Document::NS_TABLE);
        }
        
        $table = new OpenDocument_SpreadSheet_Table($tableElement, $_parent);

        return $table;
    }
    
    function rewind() {
        $this->_position = 0;
    }

    function current() {
        return $this->_rows[$this->_position];
    }

    function key() {
        return $this->_position;
    }

    function next() {
        ++$this->_position;
    }

    function valid() {
        return isset($this->_rows[$this->_position]);
    }
    
    public function count()
    {
        return count($this->_rows);
    }
}