<?php
/**
 * Tine 2.0
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @version     $Id$
 */

/**
 * create opendocument files
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 */
 
class OpenDocument_SpreadSheet_Cell
{
    const TYPE_CURRENCY   = 'currency';
    const TYPE_DATE       = 'date';
    const TYPE_TIME       = 'time';
    const TYPE_FLOAT      = 'float';
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_STRING     = 'string';
    
    /**
     * 
     * @var SimpleXMLElement
     */
    protected $_cell;
    
    public function __construct(SimpleXMLElement $_cell)
    {
        $this->_cell = $_cell;
    }
    
    static public function createCell($_parent, $_value, $_type = null)
    {
        $cellElement = $_parent->addChild('table-cell', null, OpenDocument_Document::NS_TABLE);
        
        if($_value !== null) {
            if($_type !== null) {
                $cellElement->addAttribute('office:value-type', $_type, OpenDocument_Document::NS_OFFICE);
            }
            
            switch($_type) {
                case self::TYPE_STRING:
                case self::TYPE_FLOAT:
                case self::TYPE_PERCENTAGE:
                    $cellElement->addAttribute('office:value', self::_encodeValue($_value), OpenDocument_Document::NS_OFFICE);
                    break;
                    
                case self::TYPE_DATE:
                    $cellElement->addAttribute('office:date-value', self::_encodeValue($_value), OpenDocument_Document::NS_OFFICE);
                    break;
                    
                case self::TYPE_TIME:
                    $odfTime = OpenDocument_Shared_Time::ISO2ODF($_value);
                    if ($odfTime) {
                        $cellElement->addAttribute('office:time-value', self::_encodeValue($odfTime), OpenDocument_Document::NS_OFFICE);
                    } else {
                        $cellElement->addAttribute('office:value', self::_encodeValue($_value), OpenDocument_Document::NS_OFFICE);
                    }
                    break;
                    
                case self::TYPE_CURRENCY:
                    if (strpos($_value, ' ') === FALSE) {
                        $value = $_value;
                    } else {
                        list($value, $currency) = explode(' ', $_value);
                    }
                    if(isset($currency) && ! empty($$currency)) {                 
                        $cellElement->addAttribute('office:currency', self::_encodeValue($currency), OpenDocument_Document::NS_OFFICE);
                    }                 
                    $cellElement->addAttribute('office:value', self::_encodeValue($value), OpenDocument_Document::NS_OFFICE);
                    break;
            }
            
            if($_type != self::TYPE_CURRENCY && $_type != self::TYPE_PERCENTAGE) {
                $cellElement->addChild('p', self::_encodeValue($_value), OpenDocument_Document::NS_TEXT);
            }
        }
        
        $cell = new OpenDocument_SpreadSheet_Cell($cellElement);
        
        return $cell;
    }
    
    static public function _encodeValue($_value)
    {
        return htmlspecialchars($_value, ENT_NOQUOTES, 'UTF-8');
    }
    
    public function setFormula($_formula)
    {
        $this->_cell->addAttribute('table:formula', $this->_encodeValue('oooc:' . $_formula), OpenDocument_Document::NS_TABLE);
    }
    
    public function setStyle($_style)
    {
        $this->_cell->addAttribute('table:style-name', $this->_encodeValue($_style), OpenDocument_Document::NS_TABLE);
    }
    
    public function setAtttibute($_key, $_value, $_nameSpace)
    {
        $this->_cell->addAttribute($_key, $_value, $_nameSpace);
    }
}