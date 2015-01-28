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
 
class OpenDocument_SpreadSheet_Cell extends OpenDocument_Node
{
    const TYPE_CURRENCY   = 'currency';
    const TYPE_DATE       = 'date';
    const TYPE_TIME       = 'time';
    const TYPE_FLOAT      = 'float';
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_STRING     = 'string';
    const TYPE_FUNCTION   = 'function';

    protected static $_styleFamily = 'table-cell';

    protected static $_supportedStyles = array(
        'border-top'        => array('table-cell-properties', 'fo'),
        'border-right'      => array('table-cell-properties', 'fo'),
        'border-bottom'     => array('table-cell-properties', 'fo'),
        'border-left'       => array('table-cell-properties', 'fo'),
        'vertical-align'    => array('table-cell-properties', 'style'),
        'text-align'        => array('paragraph-properties', 'fo'),
        'margin-top'        => array('paragraph-properties', 'fo'),
        'margin-right'      => array('paragraph-properties', 'fo'),
        'margin-bottom'     => array('paragraph-properties', 'fo'),
        'margin-left'       => array('paragraph-properties', 'fo'),
        'font-weight'       => array('text-properties', 'fo'),
        'font-size'         => array('text-properties', 'fo'),
    );

    /**
     * 
     * @var SimpleXMLElement
     */
    protected $_cell;

    /**
     * @param SimpleXMLElement              $cell
     */
    public function __construct(SimpleXMLElement $cell)
    {
        $this->_cell = $cell;
    }
    
    /**
     * creates a covered table cell
     * 
     * @param OpenDocument_SpreadSheet_Row|SimpleXMLElement $parent
     */
    static public function createCoveredCell($parent)
    {
        if ($parent instanceof OpenDocument_SpreadSheet_Row) {
            $parent = $parent->getBody();
        }

        $cellElement = $parent->addChild('covered-table-cell', null, OpenDocument_Document::NS_TABLE);

        $cell = new OpenDocument_SpreadSheet_Cell($cellElement);

        try {
            self::registerNode($cell, self::getNode($parent));
        } catch (Exception $e) {
            // parent might have been created outside our hierarchy
            // also the spreadsheet table pattern is a mess
        }

        return $cell;
    }
    
    
    /**
     * 
     * @param SimpleXMLElement|OpenDocument_SpreadSheet_Row $_parent
     * @param scalar $_value
     * @param string $_type
     * @param array $additionalAttributes
     * 
     * @return OpenDocument_SpreadSheet_Cell
     */
    static public function createCell($_parent, $_value, $_type = null, $additionalAttributes = array())
    {
        if ($_parent instanceof OpenDocument_SpreadSheet_Row) {
            $_parent = $_parent->getBody();
        }
        
        $cellElement = $_parent->addChild('table-cell', null, OpenDocument_Document::NS_TABLE);
        
        if($_value !== null) {
            if($_type !== null) {
                if ($_type == self::TYPE_FUNCTION) {
                    $cellElement->addAttribute('office:value-type', self::TYPE_FLOAT, OpenDocument_Document::NS_OFFICE);
                    $cellElement->addAttribute('table:formula', self::encodeValue($_value), OpenDocument_Document::NS_TABLE);
                } else {
                    $cellElement->addAttribute('office:value-type', $_type, OpenDocument_Document::NS_OFFICE);
                }
            }
            
            foreach ($additionalAttributes as $attName => $attValue) {
                $ns = NULL;
                if (strstr($attName, ':')) {
                    $ex = explode(':', $attName);
                    $cellElement->addAttribute($attName, self::encodeValue($attValue), $ex[0]);
                } else {
                    $cellElement->addAttribute($attName, self::encodeValue($attValue), $ns);
                }
            }
            
            switch($_type) {
                case self::TYPE_STRING:
                case self::TYPE_FLOAT:
                case self::TYPE_PERCENTAGE:
                    $cellElement->addAttribute('office:value', self::encodeValue($_value), OpenDocument_Document::NS_OFFICE);
                    break;
                    
                case self::TYPE_DATE:
                    $cellElement->addAttribute('office:date-value', self::encodeValue($_value), OpenDocument_Document::NS_OFFICE);
                    break;
                    
                case self::TYPE_TIME:
                    $odfTime = OpenDocument_Shared_Time::ISO2ODF($_value);
                    if ($odfTime) {
                        $cellElement->addAttribute('office:time-value', self::encodeValue($odfTime), OpenDocument_Document::NS_OFFICE);
                    } else {
                        $cellElement->addAttribute('office:value', self::encodeValue($_value), OpenDocument_Document::NS_OFFICE);
                    }
                    break;
                    
                case self::TYPE_CURRENCY:
                    if (strpos($_value, ' ') === FALSE) {
                        $value = $_value;
                    } else {
                        list($value, $currency) = explode(' ', $_value);
                    }
                    if(isset($currency) && ! empty($$currency)) {
                        $cellElement->addAttribute('office:currency', self::encodeValue($currency), OpenDocument_Document::NS_OFFICE);
                    }
                    $cellElement->addAttribute('office:value', self::encodeValue($value), OpenDocument_Document::NS_OFFICE);
                    break;
            }
            if ($_type == self::TYPE_FUNCTION) {
                
            } elseif ($_type != self::TYPE_CURRENCY && $_type != self::TYPE_PERCENTAGE) {
                $cellElement->addChild('p', self::encodeValue($_value), OpenDocument_Document::NS_TEXT);
            }
        }
        
        $cell = new OpenDocument_SpreadSheet_Cell($cellElement);

        try {
            self::registerNode($cell, self::getNode($_parent));
        } catch (Exception $e) {
            // parent might have been created outside our hierarchy
            // also the spreadsheet table pattern is a mess
        }

        return $cell;
    }

    public function setFormula($_formula)
    {
        $this->_cell->addAttribute('table:formula', $this->encodeValue('oooc:' . $_formula), OpenDocument_Document::NS_TABLE);
    }

    public function setAtttibute($_key, $_value, $_nameSpace)
    {
        $this->_cell->addAttribute($_key, $_value, $_nameSpace);
    }

    public function getBody()
    {
        return $this->_cell;
    }
}