<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Kasper Skaarhoj (kasper@typo3.com)
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
 * Static publishing extension, base class
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
 *   72: class tx_staticpub
 *   82:     function getPublishDir()
 *
 *              SECTION: Create static files
 *  120:     function createStaticFile($path,$file,$content,$pubDir,$page_id)
 *  176:     function createFile($path,$file,$content,$pubDir,$page_id)
 *  227:     function createResFile($path,$file,$content,$pubDir,$filepath_hash)
 *  275:     function createPath($path,$pubDir)
 *  300:     function createRecordForFile($filepath, $page_id, $update=FALSE)
 *
 *              SECTION: Remove static file
 *  340:     function remove_fileId($filepath_hash)
 *  373:     function remove_filesFromPageId($page_id)
 *  387:     function removeFile($filePath)
 *  416:     function removeDirRecursively($file, $pubDir)
 *  432:     function finalIntegrityCheck($fileOrDirToDelete, $pubDir)
 *
 *              SECTION: Various SQL stuff
 *  459:     function getRecordForFile($filepath)
 *  474:     function getRecordForPageID($page_id)
 *
 * TOTAL FUNCTIONS: 13
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */




/**
 * Static publishing extension, base class
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_staticpub
 */
class tx_staticpub {

	var $errorMsg = '';		// Error message.


	/**
	 * Return directory for static publishing
	 *
	 * @return	string|null		Returns publishing directory relative to the path site (if any)
	 */
	function getPublishDir()	{

		$currentPageId = $GLOBALS['TSFE']->id;

		// hook
		if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['staticpub/class.tx_staticpub.php']['getPublishDir'])) {
			$params = array(
				'currentPageId' => $currentPageId,
			);
			$pubDir = t3lib_div::callUserFunction(
				$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['staticpub/class.tx_staticpub.php']['getPublishDir'],
				$params,
				$this
			);
		}

		// retrieve from page tsconfig
		if (empty($pubDir)) {
			$pageTSconfig = t3lib_BEfunc::getPagesTSconfig($currentPageId);
			if (isset($pageTSconfig['tx_staticpub.']['publishDir'])) {
				$pubDir = $pageTSconfig['tx_staticpub.']['publishDir'];
			}
		}

		// retrieve from TYPO3_CONF_VARS
		if (empty($pubDir)) {
			$pubDir = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticpub']['publishDir'];
		}

		if (!empty($pubDir)) {
			$pubDirAbs = t3lib_div::getFileAbsFileName($pubDir);
			if (substr($pubDirAbs,-1)!='/')	{
				$pubDirAbs.='/';
			}
			if (@is_dir($pubDirAbs)) {
				return $pubDirAbs;
			}
		}
	}












	/**********************************
	 *
	 * Create static files
	 *
	 **********************************/

	/**
	 * Writes content string to static filename in publishing directory
	 *
	 * @param	string		Path in which to publish file (relative to $pubDir)
	 * @param	string		Filename (if blank, writes to "index.html")
	 * @param	string		Content to write
	 * @param	string		Absolute dir in which to publish the content
	 * @param	integer		Page id
	 * @param	array		Options. Key: includeResources=boolean; decides if resources are rendered or not.
	 * @return	string		Success-message
	 */
	function createStaticFile($path,$file,$content,$pubDir,$page_id,$options=array())	{

		$htmlparser = t3lib_div::makeInstance('t3lib_parsehtml'); /* @var $htmlparser t3lib_parsehtml */

			// Fix base URL:
		if (isset($options['overruleBaseUrl']))	{
			$parts = $htmlparser->splitTags('base',$content,1);
			if (isset($parts[1]))	{
				$parts[1] = !$options['overruleBaseUrl'] ? '' : '<base href="'.htmlspecialchars($options['overruleBaseUrl']).'" />';
				$content = implode('',$parts);
			}
		}

			// Find relative path prefix for file:
		if ($options['includeResources']==='relPath')	{
			$prefixN = count(explode('/',$path))-1;
			$prefixStr = '';
			for($a=0;$a<$prefixN;$a++)	{
				$prefixStr.='../';
			}
		}

			// Split by resource:
		$log = array();
		$token = md5(microtime());
		$parts = explode($token,$htmlparser->prefixResourcePath($token,$content,array(),$token));
		foreach($parts as $k => $v)	{
			if ($k%2)	{
				$uParts = parse_url($v);
				if (!strcmp($uParts['query'],''))	{

						// Include resources:
					if ($options['includeResources'])	{
						$fI = t3lib_div::split_fileref($uParts['path']);
						if (t3lib_div::inList('gif,jpeg,jpg,png,css,js,swf',$fI['fileext']))	{
							$fileName = t3lib_div::getFileAbsFileName($v);
							if (@is_file($fileName))	{
								if (!isset($log[$v]))	{
									$fI = t3lib_div::split_fileref($v);
									$this->createFile($fI['path'],$fI['file'],t3lib_div::getUrl($fileName),$pubDir,$page_id,TRUE);

									$GLOBALS['TSFE']->applicationData['tx_crawler']['log'][] = 'YES:'.$v.' - ';
									$log[$v] = $v;
								}
							}
						}
					}

						// Check links;
						// A: Resource is a directory, add default script name "index.html":
						// B: If
					$noPrefix=FALSE;
					if ($options['checkLinks'])	{
						if (ereg('<a[^<>]+$',$parts[$k-1]))	{

								// Set full name for links to directories:
							if (substr($parts[$k],-1)=="/")	{
								$parts[$k].='index.html';
							}

								// Set JavaScript notice when no file found:
							if (!@is_file($pubDir.$parts[$k]))	{
								$parts[$k] = "javascript:alert('Sorry, this link is disabled in the offline version');return false;";
								$noPrefix=TRUE;
							}
						}
					}

						// Fix relative path of resources (including links):
					if ($options['includeResources']==='relPath' && !$noPrefix)	{
						$parts[$k] = $prefixStr.$parts[$k];
					}
				}
			}
		}

			// Update if source has changed:
		if ($options['includeResources']==='relPath' || $options['checkLinks'])	{
			$content = implode('',$parts);

		}
	    if ($options['addComment']) {
				$content.='<!-- sp - '.date(r).'--->';;
		}

			// Write file:
		$msg = $this->createFile($path,$file,$content,$pubDir,$page_id);

		return $msg;
	}

	/**
	 * Writes content string to static filename in publishing directory
	 *
	 * @param	string		Path in which to publish file (relative to $pubDir)
	 * @param	string		Filename (if blank, writes to "index.html")
	 * @param	string		Content to write
	 * @param	string		Absolute dir in which to publish the content
	 * @param	integer		Page id
	 * @param	boolean		Is resource
	 * @return	string		Success-message
	 */
	function createFile($path,$file,$content,$pubDir,$page_id,$isResource=FALSE)	{
		$this->errorMsg = '';

			// If there is a path prefix then create the path if not created already...:
		if (!@is_dir($pubDir.$path) && strcmp($path,'') && $path!='/')	{
			$this->errorMsg = $this->createPath($path,$pubDir);
			if ($this->errorMsg)	{
				return;
			}
		}

			// Write file to dir:
		if (@is_dir($pubDir.$path) && substr($pubDir.$path,-1)=='/' && (!$isResource || $file))	{

				// Set filename to "index.html" if not given (assumed default filename of webserver)
			$fN = $file ? $file : 'index.html';

			if (is_array ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['staticpub/class.tx_staticpub.php']['createFile_processContent'])) {
				reset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['staticpub/class.tx_staticpub.php']['createFile_processContent']);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['staticpub/class.tx_staticpub.php']['createFile_processContent'] as $classRef) {
					$userObj = &t3lib_div::getUserObj($classRef);
					$content = $userObj->createFile_processContent($path,$file,$content,$pubDir,$page_id,$isResource);
				}
			}
				// Ask for existing record:
			$existRec = $this->getRecordForFile($path.$fN);
			if (is_array($existRec))	{

				if (md5_file($pubDir.$path.$fN) != md5($content)) {
					// Overwrite file if it has changedcd .:
					t3lib_div::writeFile($pubDir.$path.$fN, $content);
				} else {
					if (TYPO3_DLOG) t3lib_div::devLog(sprintf('File "%s" has not changed', $path.$fN), 'staticpuc', 0);
				}

					// Create record for published file:
				$this->createRecordForFile($path.$fN, $isResource?0:$page_id, TRUE);

				return 'Existing file updated.';
			} else {
					// Write new file:
				t3lib_div::writeFile($pubDir.$path.$fN, $content);

					// Create record for published file:
				$this->createRecordForFile($path.$fN, $isResource?0:$page_id);

				return 'New file created';
			}
		} else {
			$this->errorMsg = 'Path "'.$pubDir.$path.'" was not valid.';
		}
	}

	/**
	 * Create path in publishing directory
	 *
	 * @param	string		Path to create
	 * @param	string		Absolute publishing directory
	 * @return	boolean		Returns false on success, otherwise error message
	 */
	function createPath($path,$pubDir)	{
		//remove leading slash from path
		if ($path{0}=='/')
			$path=substr($path,1);

		if (substr($path,-1)=='/')	{
			$pathParts = explode('/',substr($path,0,-1));
			foreach($pathParts as $c => $partOfPath)	{
				if (strcmp($partOfPath,''))	{
					if (!@is_dir($pubDir.$partOfPath))	{
						t3lib_div::mkdir($pubDir.$partOfPath);
					}
					if (@is_dir($pubDir.$partOfPath))	{
						$pubDir.=$partOfPath.'/';
					} else return 'ERROR: Directory "'.$partOfPath.'" was still not created!';
				} else return 'ERROR: part of path ('.$partOfPath.') "'.$path.'" was empty! pubdir:'.$pubDir;
			}
			return FALSE;
		} else return 'ERROR: Directory "'.$path.'" did not end with "/" for pubdir:'.$pubDir;
	}

	/**
	 * Creates or updates the record for a written file.
	 * All published files have a record 1-1
	 *
	 * @param	string		Filepath (primary key for record in hashed form)
	 * @param	integer		Page ID where the file belongs
	 * @param	boolean		If set, an EXISTING record is updated instead of creating a new. This flag MUST be set based on an actual test if a record already exists!
	 * @return	void
	 */
	function createRecordForFile($filepath, $page_id, $update=FALSE)	{

			// Set
		$field_values = array(
			'filepath' => $filepath,
			'filepath_hash' => t3lib_div::md5int($filepath),
			'page_id' => $page_id,
			'tstamp' => time(),
		);

		if ($update)	{
				// Update in database:
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_staticpub_pages', 'filepath_hash='.t3lib_div::md5int($filepath), $field_values);
		} else {
				// Insert in database:
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_staticpub_pages', $field_values);
		}
	}










	/**********************************
	 *
	 * Remove static file
	 *
	 **********************************/

	/**
	 * Remove file ID from filesystem and database registration
	 *
	 * @param	integer		File registration ID (hash of filepath)
	 * @return	string		False if OK, otherwise error string
	 */
	function remove_fileId($filepath_hash)	{

			// Get record for file:
		list($rec) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'*',
					'tx_staticpub_pages',
					'filepath_hash='.intval($filepath_hash)
				);

			// If found, delete:
		if (is_array($rec))	{

				// Remove the file from file system:
			$err = $this->removeFile($rec['filepath']);
			if ($err)	{
				return $err;
			} else {

					// Delete record now:
				$GLOBALS['TYPO3_DB']->exec_DELETEquery(
						'tx_staticpub_pages',
						'filepath_hash='.intval($filepath_hash)
					);
			}
		}
	}

	/**
	 * Removes all files registered for a certain page ID
	 *
	 * @param	integer		Page ID
	 * @return	string		False if OK, otherwise error string
	 */
	function remove_filesFromPageId($page_id)	{
		$filesOnPage =   $this->getRecordForPageID($page_id);

		foreach($filesOnPage as $frec)	{
			$this->remove_fileId($frec['filepath_hash']);
		}
	}

	/**
	 * Removes a single file from the file systems publishing directory
	 * Removes directories also if empty
	 *
	 * @param	string		File path, relative to publishing directory
	 * @return	string		False if OK, otherwise error string
	 */
	function removeFile($filePath)	{

			// Initialize:
		$pubDir = $this->getPublishDir();
		$file = t3lib_div::getFileAbsFileName($pubDir.$filePath);

		if ($pubDir)	{

				// DELETE file if present:
			if (@is_file($file) && $this->finalIntegrityCheck($file, $pubDir))	{
				unlink($file);
			}

				// Check if it is gone...:
			if (@is_file($file))	{
				return 'File "'.$filePath.'" still exists!';
			} else {
				$this->removeDirRecursively($file, $pubDir);
			}
		} else return 'No publishing dir!';
	}

	/**
	 * Remove directory recursively backwards until not possible anymore.
	 *
	 * @param	string		Filename / Directory name
	 * @param	string		Publishing directory (makes sure deletion doesn't happend all the way down...)
	 * @return	void
	 */
	function removeDirRecursively($file, $pubDir)	{
		$fileDir = ereg_replace('\/[^\/]*$','',$file);
		if (@is_dir($fileDir) && t3lib_div::isFirstPartOfStr($fileDir, $pubDir) && $this->finalIntegrityCheck($fileDir, $pubDir))	{
			if (@rmdir($fileDir))	{
				$this->removeDirRecursively($fileDir, $pubDir);
			}
		}
	}

	/**
	 * Integrity check of a file/directory before being unlinked/removed. This is making sure the path is valid, absolute path and within the publishing directory!
	 *
	 * @param	string		File/Directory to delete!
	 * @param	string		Publishing directory
	 * @return	boolean		Returns TRUE if OK, otherwise it DIES! (because it really shouldn't fail! You should have checked it all on beforehand! This is only an emergency brake against your bad coding...)
	 */
	function finalIntegrityCheck($fileOrDirToDelete, $pubDir)	{
		if ($fileOrDirToDelete === t3lib_div::getFileAbsFileName($fileOrDirToDelete) && $pubDir && t3lib_div::isFirstPartOfStr($fileOrDirToDelete, $pubDir))	{
			return TRUE;
		} else die('INTEGRITY CHECK on "'.$fileOrDirToDelete.'" FAILED!');
	}










	/**********************************
	 *
	 * Various SQL stuff
	 *
	 **********************************/

	/**
	 * Returns record for a file, if any
	 *
	 * @param	string		Filepath relative to publishing directory
	 * @return	array		Returns array IF there was a record.
	 */
	function getRecordForFile($filepath)	{
		list($rec) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'*',
					'tx_staticpub_pages',
					'filepath_hash='.t3lib_div::md5int($filepath)
				);
		return $rec;
	}

	/**
	 * Returns records for a page id
	 *
	 * @param	integer		Page id
	 * @return	array		Array of records
	 */
	function getRecordForPageID($page_id)	{
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'*',
					'tx_staticpub_pages',
					'page_id='.intval($page_id)
				);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticpub/class.tx_staticpub.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticpub/class.tx_staticpub.php']);
}
?>