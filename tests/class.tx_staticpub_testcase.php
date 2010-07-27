<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE media GmbH <dev@aoemedia.de>
 *  All rights reserved
 *
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
require_once (dirname(__FILE__) .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR. 'class.tx_staticpub.php');
/**
 * Test for class tx_staticpub
 * @package static_pub
 */
class tx_staticpub_testcase extends tx_phpunit_testcase {
	/**
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;
	/**
	 * @var tx_staticpub
	 */
	private $tx_staticpub;
	/**
	 * @var string
	 */
	private $pubDir;
	/**
	 * prepare the test
	 */
	protected function setUp(){
		$this->tx_staticpub = new tx_staticpub();
		$tempPath = realpath(dirname(__FILE__) .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'typo3temp');
		$this->pubDir = $tempPath.DIRECTORY_SEPARATOR.uniqid('testPubDir');
		t3lib_div::mkdir($this->pubDir);
	}
	/**
	 * test the method createStaticFile
	 * @test
	 */
	public function createStaticFile(){
		$path = 'hallo/';
		$file = 'welt.html';
		$content = '<html></html>';
		$page_id = 0;
		$pubDir = $this->pubDir.DIRECTORY_SEPARATOR;
		$options = array();
		
		$this->assertNotNull($this->tx_staticpub->createStaticFile($path,$file,$content,$pubDir,$page_id,$options),'suscces message expected');
		$this->assertFileExists($this->pubDir.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$file,'file not created');
	}
	/**
	 * test the method includeResources
	 * @test
	 */
	public function includeResources(){
		$path = 'hallo/';
		$file = 'welt.html';
		$content = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'welt.html');
		$page_id = 0;
		$pubDir = $this->pubDir.DIRECTORY_SEPARATOR;
		$options = array('includeResources'=>TRUE);
		$this->tx_staticpub->createStaticFile($path,$file,$content,$pubDir,$page_id,$options);
		$this->assertFileExists($this->pubDir.DIRECTORY_SEPARATOR.'typo3conf'.DIRECTORY_SEPARATOR.'ext'.DIRECTORY_SEPARATOR.'staticpub'.DIRECTORY_SEPARATOR.'ext_icon.gif','file not created');
	}
	/**
	 * test the method getPublishDirForResources
	 * @test
	 */
	public function getPublishDirForResources(){
		$path = 'hallo/';
		$file = 'welt.html';
		$content = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'welt.html');
		$page_id = 0;
		$pubDir = $this->pubDir.DIRECTORY_SEPARATOR;
		$publishDirForResources = $pubDir.'res';
		t3lib_div::mkdir($publishDirForResources);
		$options = array('includeResources'=>TRUE,'publishDirForResources'=>$publishDirForResources.DIRECTORY_SEPARATOR);
		$this->tx_staticpub->createStaticFile($path,$file,$content,$pubDir,$page_id,$options);
		$this->assertFileNotExists($this->pubDir.DIRECTORY_SEPARATOR.'typo3conf'.DIRECTORY_SEPARATOR.'ext'.DIRECTORY_SEPARATOR.'staticpub'.DIRECTORY_SEPARATOR.'ext_icon.gif','file not created');
		$this->assertFileExists($this->pubDir.DIRECTORY_SEPARATOR.'res'.DIRECTORY_SEPARATOR.'typo3conf'.DIRECTORY_SEPARATOR.'ext'.DIRECTORY_SEPARATOR.'staticpub'.DIRECTORY_SEPARATOR.'ext_icon.gif','file not created');
	}
	/**
	 * clean up after test
	 */
	protected function tearDown(){
		unset($this->tx_staticpub);
		t3lib_div::rmdir($this->pubDir,TRUE);
	}
}
