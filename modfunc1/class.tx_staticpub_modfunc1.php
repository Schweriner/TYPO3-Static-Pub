<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Static publishing extension
 *
 * $Id: class.tx_cms_webinfo_lang.php,v 1.3 2004/08/26 12:18:49 typo3 Exp $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_staticpub_modfunc1 extends t3lib_extobjbase
 *   65:     function modMenu()
 *   83:     function main()
 *  134:     function renderModule($tree)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_t3lib.'class.t3lib_pagetree.php');
require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once(t3lib_extMgm::extPath('staticpub').'class.tx_staticpub.php');


/**
 * Static publishing extension
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_staticpub
 */
class tx_staticpub_modfunc1 extends t3lib_extobjbase {

	/**
	 * Returns the menu array
	 *
	 * @return	array
	 */
	function modMenu()	{
		global $LANG;

		return array (
			'depth' => array(
				0 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_0'),
				1 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_1'),
				2 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_2'),
				3 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_3'),
			)
		);
	}

	/**
	 * MAIN function for static publishing information
	 *
	 * @return	string		Output HTML for the module.
	 */
	function main()	{
		global $BACK_PATH,$LANG,$SOBE;

		$theOutput = '';

			// Depth selector:
		$h_func = t3lib_BEfunc::getFuncMenu($this->pObj->id,'SET[depth]',$this->pObj->MOD_SETTINGS['depth'],$this->pObj->MOD_MENU['depth'],'index.php');
		$theOutput.= $h_func;

			// Showing the tree:
			// Initialize starting point of page tree:
		$treeStartingPoint = intval($this->pObj->id);
		$treeStartingRecord = t3lib_BEfunc::getRecord('pages', $treeStartingPoint);
		$depth = $this->pObj->MOD_SETTINGS['depth'];

			// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));

			// Creating top icon; the current page
		$HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
		$tree->tree[] = array(
			'row' => $treeStartingRecord,
			'HTML' => $HTML
		);

			// Create the tree from starting point:
		if ($depth>0)	{
			$tree->getTree($treeStartingPoint, $depth, '');
		}

			// Add CSS needed:
		$css_content = '
		';
		$marker = '/*###POSTCSSMARKER###*/';
		$this->pObj->content = str_replace($marker,$css_content.chr(10).$marker,$this->pObj->content);

			// Render information table:
		$theOutput.= $this->renderModule($tree);

		return $theOutput;
	}

	/**
	 * Rendering the information
	 *
	 * @param	array		The Page tree data
	 * @return	string		HTML for the information table.
	 */
	function renderModule($tree)	{

			// Init static publishing object:
		$this->pubObj = t3lib_div::makeInstance('tx_staticpub');
		$pubDir = substr($this->pubObj->getPublishDir(),strlen(PATH_site));

			// Commands executed?
		if (t3lib_div::_GP('delete_file'))	{
			$this->pubObj->remove_fileId(t3lib_div::_GP('delete_file'));
		}
		if (t3lib_div::_GP('delete_page'))	{
			$this->pubObj->remove_filesFromPageId(t3lib_div::_GP('delete_page'));
		}
		$flushAll = t3lib_div::_POST('_flush_all');


			// Traverse tree:
		$output = '';
		foreach($tree->tree as $row)	{

				// Flush all files... :-)
			if ($flushAll)	{
				$this->pubObj->remove_filesFromPageId($row['row']['uid']);
			}

				// Fetch files:
			$filerecords = $this->pubObj->getRecordForPageID($row['row']['uid']);
			$cellAttrib = ($row['row']['_CSSCLASS'] ? ' class="'.$row['row']['_CSSCLASS'].'"' : '');

			if (count($filerecords))	{
				foreach($filerecords as $k => $frec)	{
					$tCells = array();

					if (!$k)	{
						$tCells[] = '<td nowrap="nowrap" valign="top" rowspan="'.count($filerecords).'"'.$cellAttrib.'>'.$row['HTML'].t3lib_BEfunc::getRecordTitle('pages',$row['row'],TRUE).'</td>';
						$tCells[] = '<td nowrap="nowrap" valign="top" rowspan="'.count($filerecords).'"><a href="'.htmlspecialchars('index.php?id='.$this->pObj->id.'&delete_page='.$row['row']['uid']).'">'.
									'<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/garbage.gif','width="11" height="12"').' alt="" />'.
									'</a></td>';
					}

					$tCells[] = '<td nowrap="nowrap"><span class="typo3-dimmed">'.$pubDir.'</span> '.$frec['filepath'].'</td>';
					$tCells[] = '<td>'.(@is_file(PATH_site.$pubDir.$frec['filepath'])?'OK':'Not found!').'</td>';
					$tCells[] = '<td nowrap="nowrap">'.t3lib_BEfunc::dateTimeAge($frec['tstamp']).'</td>';
					$tCells[] = '<td><a href="'.htmlspecialchars('index.php?id='.$this->pObj->id.'&delete_file='.$frec['filepath_hash']).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/garbage.gif','width="11" height="12"').' alt="" />'.
								'</a></td>';

						// Compile Row:
					$output.= '
						<tr class="bgColor4">
							'.implode('
							',$tCells).'
						</tr>';
				}
			} else {
					// Compile Row:
				$output.= '
					<tr class="bgColor4">
						<td nowrap="nowrap" colspan="2"'.$cellAttrib.'>'.$row['HTML'].t3lib_BEfunc::getRecordTitle('pages',$row['row'],TRUE).'</td>
						<td colspan="4"><em>No entries</em></td>
					</tr>';
			}
		}

			// Create header:
		$tCells = array();
		$tCells[]='<td>Title:</td>';
		$tCells[]='<td>&nbsp;</td>';
		$tCells[]='<td>Filepath:</td>';
		$tCells[]='<td>Status:</td>';
		$tCells[]='<td>Timestamp:</td>';
		$tCells[]='<td>&nbsp;</td>';
		$output = '
			<tr class="bgColor5 tableheader">
				'.implode('
				',$tCells).'
			</tr>'.$output;

			// Compile final table and return:
		$output = '

		<input type="hidden" name="id" value="'.$this->pObj->id.'" />
		<input type="submit" name="_flush_all" value="Flush ALL!">


		<table border="0" cellspacing="1" cellpadding="0" class="lrPadding">'.$output.'
		</table>';

		return $output;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticpub/modfunc1/class.tx_staticpub_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/staticpub/modfunc1/class.tx_staticpub_modfunc1.php']);
}
?>