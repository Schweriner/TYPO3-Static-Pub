<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE media (dev@aoemedia.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
require_once(t3lib_extMgm::extPath('staticpub') . 'class.tx_staticpub_export.php');
/**
 * sheduler task to export static pages
 * @package TYPO3
 * @subpackage tx_staticpub
 */
class tx_staticpub_tasks_export extends tx_scheduler_Task {
	/**
	 * @var string
	 */
	public $folders;
	/**
	 * @return boolean	Returns true on successful execution, false on error
	 */
	public function execute() {
		/* @var $export tx_staticpub_export */
		try{
			$export = t3lib_div::makeInstance('tx_staticpub_export');
			$export->exportContent($this->folders);
			return true;
		}catch (Exception $e){
			t3lib_div::devLog('staticpub export error: '.$e->getMessage(), 'staticpub',2);
			throw $e;
		}
	}
}
