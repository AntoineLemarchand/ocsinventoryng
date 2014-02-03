<?php

/**
* Test class for ocsclient.
* Generated by PHPUnit.
*/
class ocsclientTest extends PHPUnit_Framework_TestCase {

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main(){
    	require_once 'PHPUnit/TextUI/TestRunner.php';

    	$suite  = new PHPUnit_Framework_TestSuite('ocsclientTest');
    	$result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(){
    	global $DB, $CFG_GLPI;
        $_SERVER['REQUEST_URI'] = '/plugins';
        require_once(__DIR__.'/../../../inc/includes.php');
        $soapuser = $GLOBALS['soapuser']; 
        $soappass = $GLOBALS['soappass']; 
        $this->dbclient = new PluginOcsinventoryngOcsDbClient(1,'ocstest','ocsuser','ocspass','ocsweb');
        $this->soapclient = new PluginOcsinventoryngOcsSoapClient(2,'ocstest',$soapuser,$soappass);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown(){
        unset($this->client);
        unset($this->soapclient);
    }


    public function testGetComputer($options){
        $options = array(
            'OFFSET' => 0,
            'MAX_RECORDS' => 1,
            'FILTER' => array(                     
               'IDS' => array(67),                   
               ),
            'DISPLAY' => array(    
                'CHECKSUM' => 0x1FFFF, 
               'WANTED' => 0x00003,   
               )
            );
        $soap = $this->soapclient->getComputers($options);
        $db = $this->dbclient->getComputers($options);
        $this->assertEquals($soap,$db);
    }



    public function testGetComputers($options){
        $options = array(
            'OFFSET' => 0,
            'MAX_RECORDS' => 25,
            'FILTER' => array(                     
               'EXCLUDE_IDS' => array(189,181),  
               'EXCLUDE_TAGS' => array(LCF-Clermont-Ferrand-Evelyne),
               ),
            'DISPLAY' => array(    
              'CHECKSUM' => 0x1FFFF, 
               'WANTED' => 0x00003,   
               )
            );
        $soap = $this->soapclient->getComputers($options);
        $db = $this->dbclient->getComputers($options);
        $this->assertEquals($soap,$db);
    }



    //TODO Test if options[FILTER] is set but only contains empty arrays
    
}











