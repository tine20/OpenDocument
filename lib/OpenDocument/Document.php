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
 
class OpenDocument_Document extends OpenDocument_Node
{
    const NS_TABLE  = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    const NS_STYLE  = 'urn:oasis:names:tc:opendocument:xmlns:style:1.0';
    const NS_OFFICE = 'urn:oasis:names:tc:opendocument:xmlns:office:1.0';
    const NS_FO     = 'urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0';
    const NS_TEXT   = 'urn:oasis:names:tc:opendocument:xmlns:text:1.0';
    
    const SPREADSHEET = 'SpreadSheet';
    
    protected $_rowStyles = array();
    
    protected $_columnStyles = array();
    
    protected $_cellStyles = array();
    
    /**
     * the given template file
     * 
     * @var string
     */
    protected $_templateFile;
    
    /**
     * the content.xml in an simplexml element
     * 
     * @var SimpleXMLElement 
     */
    protected $_document;
        
    /**
     * document body
     *
     * @var OpenDocument_SpreadSheet
     */
    protected $_body;
    
    /**
     * 
     * @var string
     */
    protected $_type;

    protected $_tmpdir;
    
    /**
     * the content.xml as text
     *
     * @var string
     */
    protected $_content = '<?xml version="1.0" encoding="UTF-8"?>
        <office:document-content 
            xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
            xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
            xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
            xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
            xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" 
            xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" 
            xmlns:xlink="http://www.w3.org/1999/xlink" 
            xmlns:dc="http://purl.org/dc/elements/1.1/" 
            xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
            xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" 
            xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" 
            xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" 
            xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" 
            xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" 
            xmlns:math="http://www.w3.org/1998/Math/MathML" 
            xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" 
            xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" 
            xmlns:ooo="http://openoffice.org/2004/office" 
            xmlns:ooow="http://openoffice.org/2004/writer" 
            xmlns:oooc="http://openoffice.org/2004/calc" 
            xmlns:dom="http://www.w3.org/2001/xml-events" 
            xmlns:xforms="http://www.w3.org/2002/xforms" 
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xmlns:field="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:field:1.0" office:version="1.1">
            <office:scripts/>
            <office:font-face-decls>
                <style:font-face style:name="Arial" svg:font-family="Arial" style:font-family-generic="swiss" style:font-pitch="variable"/>
                <style:font-face style:name="DejaVu Sans" svg:font-family="&apos;DejaVu Sans&apos;" style:font-family-generic="system" style:font-pitch="variable"/>
            </office:font-face-decls>
            <office:automatic-styles/>
            <office:body>
            </office:body>
        </office:document-content>';
    
    protected $_manifest = '<?xml version="1.0" encoding="UTF-8"?>
        <manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.2">
            <manifest:file-entry manifest:media-type="application/vnd.oasis.opendocument.spreadsheet" manifest:full-path="/"/>
            <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="content.xml"/>
            <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="styles.xml"/>
            <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="meta.xml"/>
            <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="settings.xml"/>
        </manifest:manifest>';
    
    protected $_meta = '<?xml version="1.0" encoding="UTF-8"?>
        <office:document-meta 
            xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
            xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" 
            xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
            xmlns:ooo="http://openoffice.org/2004/office" office:version="1.1">
            <office:meta>
                <meta:generator>Tine 2.0</meta:generator>
                <meta:creation-date>2009-01-17T22:04:51</meta:creation-date>
                <meta:editing-cycles>1</meta:editing-cycles>
                <meta:editing-duration>PT0S</meta:editing-duration>
                <meta:user-defined meta:name="Info 1"/>
                <meta:user-defined meta:name="Info 2"/>
                <meta:user-defined meta:name="Info 3"/>
                <meta:user-defined meta:name="Info 4"/>
                <meta:document-statistic meta:table-count="1"/>
            </office:meta></office:document-meta>';
    
    protected $_settings = '<?xml version="1.0" encoding="UTF-8"?>
        <office:document-settings 
            xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
            xmlns:xlink="http://www.w3.org/1999/xlink" 
            xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" 
            xmlns:ooo="http://openoffice.org/2004/office" office:version="1.1">
            <office:settings>
            </office:settings>
        </office:document-settings>';
 
    protected $_styles = '<?xml version="1.0" encoding="UTF-8"?>
        <office:document-styles 
            xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" 
            xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" 
            xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" 
            xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" 
            xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" 
            xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" 
            xmlns:xlink="http://www.w3.org/1999/xlink" 
            xmlns:dc="http://purl.org/dc/elements/1.1/" 
            xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
            xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" 
            xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" 
            xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" 
            xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" 
            xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" 
            xmlns:math="http://www.w3.org/1998/Math/MathML" 
            xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" 
            xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" 
            xmlns:ooo="http://openoffice.org/2004/office" 
            xmlns:ooow="http://openoffice.org/2004/writer" 
            xmlns:oooc="http://openoffice.org/2004/calc" 
            xmlns:dom="http://www.w3.org/2001/xml-events" office:version="1.1">
        </office:document-styles>';
    
    protected $_userStyles = array();
    
    /**
     * constructor
     * 
     * @param string $_type
     * @param string $_fileName
     * @param string $_tmpdir
     * @return void
     */
    public function __construct($_type, $_fileName = null, $_tmpdir = NULL, $_userStyles = array())
    {
        if ($_fileName !== null) {
            if (! file_exists($_fileName)) {
                error_log('Template file not found: ' . $_fileName);
            } else {
                $this->_templateFile = $_fileName;
                
                $this->_content     = file_get_contents('zip://' . $_fileName . '#content.xml');
                #$this->_manifest    = file_get_contents('zip://' . $_fileName . '#META-INF/manifest.xml');
                #$this->_meta        = file_get_contents('zip://' . $_fileName . '#meta.xml');
                #$this->_settings    = file_get_contents('zip://' . $_fileName . '#settings.xml');
                $this->_styles      = file_get_contents('zip://' . $_fileName . '#styles.xml');
            }
        }

        $this->_document = new SimpleXMLElement($this->_content);
        self::registerNode($this);
        
        // register namespaces
        $namespaces = $this->_document->getNamespaces(true);
        foreach ($namespaces as $prefix => $ns) {
          $this->_document->registerXPathNamespace($prefix, $ns);
        }
        
        $this->_tmpdir = ($_tmpdir !== NULL) ? $_tmpdir : sys_get_temp_dir();
        $this->_userStyles = $_userStyles;
        
        switch ($_type) {
            case self::SPREADSHEET:
                // don't create new spreadsheet node if it already exists
                $spreadsheets = $this->_document->xpath('//office:body/office:spreadsheet');
                if (count($spreadsheets) == 0) {
                    $body = $this->_document->xpath('//office:body');
                    $spreadsheet = $body[0]->addChild('office:spreadsheet', NULL, OpenDocument_Document::NS_OFFICE);
                } else {
                    $spreadsheet = $spreadsheets[0];
                }
                $this->_body = new OpenDocument_SpreadSheet($spreadsheet);
                self::registerNode($this->_body, $this);

                break;
            default:
                throw new Exception('unsupported documenttype: ' . $_type);
                break;
        }
        
        $this->_type = $_type;
    }
    
    /**
     * get the body
     *
     * @return OpenDocument_SpreadSheet
     */
    public function getBody()
    {
        return $this->_body;
    }
    
    /**
     * replaces a marker with a matrix
     * 
     * @param string $marker
     * @param OpenDocument_Matrix $matrix
     * @param bool $showLegend
     * @return OpenDocument_Document
     */
    public function replaceMatrix($marker, $matrix, $showLegend = TRUE, $showLegendDescription = TRUE, $showSums = TRUE)
    {
        if ($xml = $this->_findMarkers($marker)) {
            switch ($this->_type) {
                case self::SPREADSHEET:
                    foreach($xml as $xmlElement) {
                        $this->_body->replaceMatrix($xmlElement, $matrix, $showLegend, $showLegendDescription, $showSums);
                    }
                    break;
                default:
                    throw new Exception('unsupported documenttype for matrix replace: ' . $this->_type);
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param unknown $marker
     * @param unknown $list
     * @throws Exception
     * @return OpenDocument_Document
     */
    public function replaceList($marker, $list, $type = OpenDocument_SpreadSheet_Cell::TYPE_STRING, $direction = 'horizontal')
    {
        if ($xml = $this->_findMarkers($marker)) {
            switch ($this->_type) {
                case self::SPREADSHEET:
                    foreach($xml as $xmlElement) {
                        $this->_body->replaceList($xmlElement, $list, $type, $direction);
                    }
                    break;
                default:
                    throw new Exception('unsupported documenttype for matrix replace: ' . $this->_type);
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param string $marker
     * @return array
     */
    protected function _findMarkers($marker)
    {
        return $this->_document->xpath("//text()[contains(., '###".strtoupper($marker)."###')]");
    }
    
    /**
     * replaces a marker
     * 
     * @param SimpleXMLElement $xml
     * @param string $value
     */
    public function replaceMarker($marker, $value)
    {
        if ($xml = $this->_findMarkers($marker)) {
            foreach($xml as $xmlElement) {
                $xmlElement[0] = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * returns this _document (content.xml) as string
     * 
     * @return mixed
     */
    public function asXML()
    {
        return $this->_document->asXML();
    }
    
    /**
     * adds additional user styles
     * 
     * @param string|array $_style
     * @return OpenDocument_Document
     */
    public function addStyle($_style)
    {
        if (! is_array($_style)) {
            $_style = array($_style);
        }
        foreach($_style as $s) {
            $this->_userStyles[] = $s;
        }
        return $this;
    }

    /**
     * creates the document
     * 
     * @param string $_filename
     * @throws Exception
     * @return string
     */
    public function getDocument($_filename = null)
    {
        $this->_addStyles();

        $filename =  $_filename !== null ? $_filename : tempnam($this->_tmpdir, 'OpenDocument');
        
        if($this->_templateFile === null) {
            
            $tempDir = $this->_tmpdir . DIRECTORY_SEPARATOR . 'od_' . md5(uniqid(rand(), true));
        
            if (file_exists($tempDir)) {
                throw new Exception('Directory already exists.');
            }
            
            mkdir($tempDir);
            
            mkdir($tempDir . DIRECTORY_SEPARATOR . 'META-INF');
            file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'mimetype', $this->_body->getContentType());
            file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'meta.xml', $this->_meta);
            file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'settings.xml', $this->_settings);
            file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'META-INF/manifest.xml', $this->_manifest);
            
            file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'content.xml', $this->_document->saveXML());
            file_put_contents($tempDir . DIRECTORY_SEPARATOR . 'styles.xml', $this->_styles);
            
            $zip = new ZipArchive();
            $opened = $zip->open($filename, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
            
            if( $opened !== true ) {
                throw new Exception('could not open zip file');
            }
            
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDir));
            
            foreach ($iterator as $fullFilename => $cur) {
                // the second parameter of the addFile function needs always the unix directory separator
                $localname = str_replace('\\', '/', substr($fullFilename, strlen($tempDir)+1));
                
                // exclude . and ..
                $localname_parts = pathinfo($localname);
                $localbasename = $localname_parts['basename'];
                if ($localbasename !== 'mimetype' && $localbasename !== '.' && $localbasename !== '..') {
                    $zip->addFile($fullFilename, $localname);
                }
            }
    
            $zip->close();
            
            // delete files / remove dir
            $this->_removeTempFiles($tempDir);
            
            
        } else {
            $templateZip = new ZipArchive();
            if ($templateZip->open($this->_templateFile) === TRUE) {
                $templateZip->close();
                
                copy($this->_templateFile, $filename);
                
                $templateZip->open($filename);
                $templateZip->addFromString('content.xml', $this->_document->saveXML());
                $templateZip->close();
            }
        }
        
        return $filename;
    }
    

    /**
     * recursive deleting of temp directory and all containing files
     *
     * @param string $_dir
     * @return void
     */
    protected function _removeTempFiles($_dir)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($_dir), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $fullFilename => $cur) {
            if (is_dir($fullFilename) && substr($fullFilename, -1) !== '.') {
                rmdir($fullFilename);
            } elseif (is_file($fullFilename)) {
                unlink($fullFilename);
            }
        }
        if (is_dir($_dir)) rmdir($_dir);
    }
    
    /**
     * add styles to this document
     */
    protected function _addStyles()
    {
        $styles = $this->_document->xpath('//office:automatic-styles');
        $domStyles = dom_import_simplexml($styles[0]);

        foreach($this->_userStyles as $userStyle) {
            if ($userStyle instanceof SimpleXMLElement) {
                $newChild = $userStyle;
            } else {
                $newChild = new SimpleXMLElement($userStyle);
            }
            $dom_sxe = dom_import_simplexml($newChild);
            $newStyle = $domStyles->ownerDocument->importNode($dom_sxe, true);
            $domStyles->appendChild($newStyle);
        }
    }
}
