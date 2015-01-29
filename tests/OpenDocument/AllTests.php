<?php
/**
 * OpenDocument
 *
 * @package     OpenDocument
 * @subpackage  Tests
 * @license     http://www.tine20.org/licenses/lgpl.html LGPL Version 3
 * @copyright   Copyright (c) 2013 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Alexander Stintzing <a.stintzing.net>
 */

/**
 * class to test the OpenDocument Library - All Tests
 *
 * @package     OpenDocument
 * @subpackage  Tests
 */
class OpenDocument_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('All Tests');
        
        $suite->addTestSuite('OpenDocument_DocumentTests');
        $suite->addTestSuite('OpenDocument_SpreadSheet_TableTests');

        return $suite;
    }
}
