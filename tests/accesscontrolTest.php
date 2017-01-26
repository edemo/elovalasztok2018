<?php
require_once "tests/testJoomlaFramework.php";

class accesscontrolTest extends PHPUnit_Framework_TestCase {
    	function __construct() {
		global $testData,$componentName,$viewName;
		$componentName = 'accesscontrol';
		$viewName = '';
		define('JPATH_COMPONENT', 'elovalasztok');
		$testData->addDbResult(JSON_encode('{}'));
		require_once dirname(__FILE__).'\..\accesscontrol.php';
		require_once dirname(__FILE__).'\..\funkciok.php';
		require_once dirname(__FILE__).'\..\config.php';
		parent::__construct();
	}
	protected function setupConfig() {
		global $testData,$componentName, $testUser;
		$testData->clear();
		$testUser = new JUser();
	}

    	public function test_szavazottMar_nem()  {
		global $testData,$componentName, $testUser;
		$this->setupConfig();
		$testUser->id = 1;
		$testData->addDbResult(false);
		$res = szavazottMar(10, $user, 0);
        	$this->assertEquals(false, $res);
    	}
    	public function test_szavazottMar_igen()  {
		global $testData,$componentName, $testUser;
		$this->setupConfig();
		$testUser->id = 1;
		$testData->addDbResult(JSON_decode('[{"id":1}]'));
		$res = szavazottMar(10, $testUser, 0);
        	$this->assertEquals(true, $res);
    	}
    	public function test_szavazottMar_nincsbelepve()  {
		global $testData,$componentName, $testUser;
		$this->setupConfig();
		$testUser->id = 0;
		$testData->addDbResult(false);
		$res = szavazottMar(10, $testUser, 0);
        	$this->assertEquals(false, $res);
    	}
	public function test_teheti_jeloltAdd_superuser() { 	
		global $testData,$componentName, $testUser,$evConfig;
		$this->setupConfig();
		$testUser->id = 1;
		$testUser->groups = array();
		$testUser->groups[8] = 8;
		$evConfig->jeloltAdd = true;
		$msg = '';
		$res = 	teheti(10, $testUser, 'jeloltAdd', $msg);
        	$this->assertEquals(true, $res);
		$this->assertEquals('',$msg);
	}
	public function test_teheti_jeloltAdd_elovalasztokadmin() { 	
		global $testData,$componentName, $testUser,$evConfig;
		$this->setupConfig();
		$testUser->id = 1;
		$testUser->groups = array();
		$testUser->groups[10] = 10;
		$evConfig->jeloltAdd = true;
		$msg = '';
		$res = 	teheti(10, $testUser, 'jeloltAdd', $msg);
        	$this->assertEquals(true, $res);
		$this->assertEquals('',$msg);
	}
	public function test_teheti_jeloltAdd_normaluser() { 	
		global $testData,$componentName, $testUser,$evConfig;
		$this->setupConfig();
		$testUser->id = 1;
		$testUser->groups = array();
		$evConfig->jeloltAdd = true;
		$msg = '';
		$res = 	teheti(10, $testUser, 'jeloltAdd', $msg);
        	$this->assertEquals(false, $res);
		$this->assertEquals('Nincs ehhez joga!',$msg);
	}
	public function test_teheti_jeloltAdd_configTilt() { 	
		global $testData,$componentName, $testUser,$evConfig;
		$this->setupConfig();
		$testUser->id = 1;
		$testUser->groups = array();
		$testUser->groups[10] = 10;
		$evConfig->jeloltAdd = false;
		$msg = '';
		$res = 	teheti(10, $testUser, 'jeloltAdd', $msg);
        	$this->assertEquals(false, $res);
		$this->assertEquals('config',$msg);
	}


	// itt most jöhetne még kb 20-30 "teheti" if ág tesztelése....
}
?>
