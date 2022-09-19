<?php
/**
 * OpenDocument - http://www.tine20.org/
 *
 * @package     Document
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2013-2022 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Alexander Stintzing <a.stintzing@metaways.de>
 */

/**
 * Test class for OpenDocument
 */
class OpenDocument_DocumentTests extends \PHPUnit\Framework\TestCase
{
    /**
     * tests the correct replacement of markers with different contents
     */
    public function testMarkerReplacement()
    {
        $doc = new OpenDocument_Document(OpenDocument_Document::SPREADSHEET);
        $table = $doc->getBody()->appendTable('UNITTEST');
        
        $titleText = 'Hello unittest!';
        
        $row  = $table->appendRow();
        $cell = $row->appendCell($titleText);
        
        $row  = $table->appendRow();
        
        $row  = $table->appendRow();
        $cell = $row->appendCell('###MATRIX###');
        
        $row  = $table->appendRow();
        
        $row  = $table->appendRow();
        $cell = $row->appendCell('###MARKER###');
        
        $tmpDir = sys_get_temp_dir();
        
        $filename = $tmpDir . DIRECTORY_SEPARATOR . sha1(mt_rand()) . '-ods-unittest.ods';
        
        $colInfo = array('id1' => 'ID 1', 'id2' => 'ID 2');
        
        $matrixArray = array(
            'id1' => array('id2' => '100'),
            'id2' => array('id1' => '200')
        );
        
        $matrix = new OpenDocument_Matrix($matrixArray, $colInfo, $colInfo, OpenDocument_Matrix::TYPE_FLOAT);
        
        $matrix->setColumnLegendDescription('Cat');
        $matrix->setRowLegendDescription('Dog');
        
        $markerText = 'unittest-marker';
        $doc->replaceMarker('marker', $markerText)->replaceMatrix('matrix', $matrix);
        $doc->getDocument($filename);
        
        $contentXml = file_get_contents('zip://' . $filename . '#content.xml');
        $xml = simplexml_load_string($contentXml);
        
        unlink($filename);
        
        $spreadSheets = $xml->xpath('//office:body/office:spreadsheet');
        $spreadSheet  = $spreadSheets[0];
        
        $results = $spreadSheet->xpath("//text()[contains(., '$markerText')]");
        $this->assertEquals(1, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., '$titleText')]");
        $this->assertEquals(1, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., 'ID 1')]");
        $this->assertEquals(2, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., 'ID 2')]");
        $this->assertEquals(2, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., 'Sum')]");
        $this->assertEquals(2, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., 'Cat')]");
        $this->assertEquals(1, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., 'Dog')]");
        $this->assertEquals(1, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., '100')]");
        $this->assertEquals(3, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., '200')]");
        $this->assertEquals(3, count($results));
        
        $results = $spreadSheet->xpath("//text()[contains(., '300')]");
        $this->assertEquals(1, count($results));
    }
}