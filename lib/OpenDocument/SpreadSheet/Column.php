<?php
/**
 * Tine 2.0
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright   Copyright (c) 2014 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Alexander Stintzing <a.stintzing@metaways.de>
 */

/**
 * create opendocument files
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 */
 
class OpenDocument_SpreadSheet_Column extends OpenDocument_Node
{
    protected static $_styleFamily = 'table-column';

    protected static $_supportedStyles = array(
        'border-top'    => array('table-cell-properties', 'fo'),
        'border-right'  => array('table-cell-properties', 'fo'),
        'border-bottom' => array('table-cell-properties', 'fo'),
        'border-left'   => array('table-cell-properties', 'fo'),
        'column-width'  => array('table-column-properties', 'style'),
        'text-align'    => array('paragraph-properties', 'fo'),
        'margin-top'    => array('paragraph-properties', 'fo'),
        'margin-right'  => array('paragraph-properties', 'fo'),
        'margin-bottom' => array('paragraph-properties', 'fo'),
        'margin-left'   => array('paragraph-properties', 'fo'),
        'font-weight'   => array('text-properties', 'fo'),
    );

    /**
     * 
     * @var SimpleXMLElement
     */
    protected $_column;
    
    public function __construct(SimpleXMLElement $_column)
    {
        $this->_column = $_column;
    }
    
    public function getBody()
    {
        return $this->_column;
    }
    
    /**
     * 
     * @param SimpleXMLElement $_parent
     * @param string $_styleName
     * @param SimpleXMLElement $_referenceRow
     * @param string $_position
     * 
     * @return OpenDocument_SpreadSheet_Row
     */
    static public function createColunm($_parent, $_styleName = NULL, $_reference = NULL, $_refIndex = 0, $_position = 'after')
    {
        if ($_reference == NULL) {
            $columnElement = $_parent->addChild('table-column', NULL, OpenDocument_Document::NS_TABLE);
            
            if ($_styleName !== NULL) {
                $columnElement->addAttribute('table:style-name', $_styleName, OpenDocument_Document::NS_TABLE);
            }
        } else {
            
            $columnElement = $_parent->addChild('table-column', NULL, OpenDocument_Document::NS_TABLE);
            
            if ($_position == 'after') {
                $columnElement = OpenDocument_Shared_SimpleXML::simplexml_insert_after($columnElement, $_reference, $_refIndex);
            } else {
                $columnElement = OpenDocument_Shared_SimpleXML::simplexml_insert_before($columnElement, $_reference, $_refIndex);
            }
            
        }
        
        $column = new self($columnElement);

        try {
            self::registerNode($column, self::getNode($_parent));
        } catch (Exception $e) {
            // parent might have been created outside our hierarchy
            // also the spreadsheet table pattern is a mess
        }

        return $column;
    }
}