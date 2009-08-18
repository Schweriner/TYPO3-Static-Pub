<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Kasper Skaarhoj (kasper@typo3.com)
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
/**
 * Static publishing, frontend hook
 *
 * $Id $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   59: class tx_staticpub_fe extends tx_staticpub
 *   69:     function fe_headerNoCache(&$params, $ref)
 *   89:     function insertPageIncache(&$pObj,$timeOutTime)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */




require_once(t3lib_extMgm::extPath('staticpub').'class.tx_staticpub.php');

/**
 * Static publishing, frontend hook
 * Publishing of TYPO3 pages as static HTML must happen via the frontend since that is where the page is rendered into its final form
 * The publishing actions is activated ONLY during a request where the extension "crawler" is requesting the page with the "tx_staticpub_publish" processing instruction.
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_staticpub
 */
class tx_staticpub_fe extends tx_staticpub {

	/**
	 * Bypasses cache-acquisition of page if the page should be staticly published (otherwise the publishing hook is not activated...)
	 * (Hook-function called from TSFE, see ext_localconf.php for configuration)
	 *
	 * @param	array		Parameters from frontend
	 * @param	object		TSFE object (reference under PHP5)
	 * @return	void
	 */
	function fe_headerNoCache(&$params, $ref)	{

			// Requirements are that the crawler is loaded, a crawler session is running and re-caching requested as processing instruction:
		if (t3lib_extMgm::isLoaded('crawler')
				&& $params['pObj']->applicationData['tx_crawler']['running']
				&& in_array('tx_staticpub_publish', $params['pObj']->applicationData['tx_crawler']['parameters']['procInstructions']))	{

				// Disables a look-up for cached page data - thus resulting in re-generation of the page even if cached.
			$params['disableAcquireCacheData'] = TRUE;
		}
	}

	/**
	 * Publishes the current page as static HTML file if possible (depends on configuration and other circumstances)
	 * (Hook-function called from TSFE, see ext_localconf.php for configuration)
	 *
	 * @param	tslib_fe	Reference to parent object (TSFE)
	 * @param	integer		[Not used here]
	 * @return	void
	 */
	function insertPageIncache(tslib_fe $pObj, $timeOutTime)	{

		$GLOBALS['TT']->push('tx_staticpub','');


			// Look for "crawler" extension activity:
			// Requirements are that the crawler is loaded, a crawler session is running and re-indexing requested as processing instruction:
		if (t3lib_extMgm::isLoaded('crawler')
				&& $pObj->applicationData['tx_crawler']['running']
				&& in_array('tx_staticpub_publish', $pObj->applicationData['tx_crawler']['parameters']['procInstructions']))	{

			$fileCreated = false;

			$pubDir = $this->getPublishDir();
			$origId = intval(t3lib_div::_GET('id'));
			$siteScript = $this->createUrl($pObj);
			$uParts = parse_url($siteScript);
			$fI = t3lib_div::split_fileref($uParts['path']);

				// If the page can be staticly published (same evaluation as if cache-control headers would be sent to a reverse-proxy)
			if ($pObj->isStaticCacheble())	{

					// Check for Publishing Directory:
				if ($pubDir) {

						// Get positive confirmation that either "simulateStaticDocument" or "realurl" was processed right!
					if ($origId === $pObj->id)	{

						if (!$this->hasInvalidQueryparts($uParts['query']))	{

								// check if the file extension is empty, "html" or "htm"
							if (!strcmp($fI['fileext'],'') || t3lib_div::inList('html,htm',$fI['fileext']))	{

									// create file
								$res = $this->createStaticFile($fI['path'], $fI['file'], $pObj->content, $pubDir, $origId, $pObj->applicationData['tx_crawler']['parameters']['procInstrParams']['tx_staticpub_publish.']);

									// check if the file has been created successfully
								if ($res) {
									$pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; OK: "'.$uParts['path'].'" published in "'.substr($pubDir,strlen(PATH_site)).'". Msg: '.$res;
									$pObj->applicationData['tx_crawler']['success']['tx_staticpub'] = true;
									$fileCreated = true;
								} else $pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: '.$this->errorMsg;
							} else $pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: Filepath was not an HTML file or directory ("'.$siteScript.'").';
						} else $pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: A query string was found in the constructed filepath ("'.$siteScript.'"). This automatically disables publishing!';
					} else $pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: GET var ID ("'.$origId.'") did not match TSFE->id ("'.$pObj->id.'")!';
				} else $pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: No publishing directory was configured.';
			} else $pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: isStaticCacheble = NO';
			
			if($this->errorMsg) {
				$pObj->applicationData['tx_crawler']['log'][] = $this->errorMsg; 
			}
			
				// if no file was created check if an existing file from a previous run should be deleted
			if (!$fileCreated) {
				$pageTSconfig = t3lib_BEfunc::getPagesTSconfig($origId);
				if (!empty($pageTSconfig['tx_staticpub.']['deleteOldFiles'])) {
					$fileName = $fI['path'] . $fI['file'];
					$res = $this->removeFile($fileName);
				}
			}

		} elseif(!t3lib_extMgm::isLoaded('crawler')) {
			$pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: crawler is not loaded';
		} elseif(!$pObj->applicationData['tx_crawler']['running']) {
			$pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: crawler is not running';
		} elseif(!in_array('tx_staticpub_publish', $pObj->applicationData['tx_crawler']['parameters']['procInstructions'])) {
			$pObj->applicationData['tx_crawler']['log']['tx_staticpub'] = 'EXT:static_pub; ERROR: no procInstructions given';
		}

		$GLOBALS['TT']->pull();
	}

	/**
	 * Create url for this page
	 *
	 * @param void
	 * @return string url
	 */
	function createUrl(tslib_fe $pObj) {
		$getVars = t3lib_div::_GET();
		$origId = intval($getVars['id']);
		$origType = intval($getVars['type']);
		unset($getVars['id']);
		unset($getVars['type']);

			// Create URL with link-function (should be the same as a script in the frontend would make to link to this script):
		$urlData = $pObj->tmpl->linkData($pObj->sys_page->getPage($origId),'',FALSE,'','',t3lib_div::implodeArrayForUrl('',$getVars),$origType);
		$siteScript = $urlData['totalURL'];

		return $siteScript;
	}

	/**
	 * Check whether there're parts within the query
	 * which aren't related to a possible workspace-publish
	 * 
	 * @param $str	the query-string
	 * @return boolean
	 */
	function hasInvalidQueryparts($str) {
		if(strcmp($str,'')===0) return false;
		$query=array();
		parse_str($str,$query);
		foreach($query as $key=>$value) {
			if(substr($key,0,6)!='ADMCMD') {
				return true;
			}
		}
		return false;
	}
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticpub/class.tx_staticpub_fe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticpub/class.tx_staticpub_fe.php']);
}
?>