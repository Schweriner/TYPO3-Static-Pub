<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009 AOE media GmbH <dev@aoemedia.de>
 * All rights reserved
 *
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
require_once (dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class.tx_staticpub_export.php');
/**
 * Test for class tx_staticpub_export
 * @package static_pub
 */
class tx_staticpub_export_testcase extends tx_phpunit_testcase {
	/**
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;
	/**
	 * @var tx_staticpub_export
	 */
	private $tx_staticpub_export;
	/**
	 * @var string
	 */
	private $pubDir;
	/**
	 * prepare the test
	 */
	protected function setUp() {
		$this->tx_staticpub_export = new tx_staticpub_export ();
		$tempPath = realpath ( dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'typo3temp' );
		$this->pubDir = $tempPath . DIRECTORY_SEPARATOR . uniqid ( 'testPubDir' );
		t3lib_div::mkdir ( $this->pubDir );
	}
	/**
	 * test the method exportContent
	 * @test
	 * @expectedException Exception
	 */
	public function exportContentWithEmptyFolders() {
		$this->tx_staticpub_export->exportContent ( '' );
	}
	/**
	 * test the method exportContent
	 * @test
	 * @expectedException Exception
	 */
	public function exportContentWithInvalidSourceFolder() {
		$this->tx_staticpub_export->exportContent ( 'aassadad' . tx_staticpub_export::TARGET_SEPERATOR . 'sdsdds' . tx_staticpub_export::FOLDER_SEPERATOR );
	}
	/**
	 * test the method exportContent
	 * @test
	 */
	public function exportContent() {
		$source = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
		$this->tx_staticpub_export->exportContent ( $source . tx_staticpub_export::TARGET_SEPERATOR . $this->pubDir . tx_staticpub_export::FOLDER_SEPERATOR );
		$this->assertFileExists ( $this->pubDir . DIRECTORY_SEPARATOR . 'welt.html', 'file not created' );
	}
	/**
	 * clean up after test
	 */
	protected function tearDown() {
		unset ( $this->tx_staticpub_export );
		t3lib_div::rmdir ( $this->pubDir, TRUE );
	}
}
