<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009 AOE media (dev@aoemedia.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Export static pub content
 *
 * @package TYPO3
 * @subpackage tx_staticpub
 */
class tx_staticpub_export {
	const FOLDER_SEPERATOR = ';';
	const TARGET_SEPERATOR = ':';
	/**
	 * @param string $str_folders
	 * @throws Exception
	 */
	public function exportContent($str_folders) {
		$folders = $this->parseFolders ( $str_folders );
		if (empty ( $folders )) {
			throw new Exception ( 'tx_staticpub_export: invalid folders: ' . $str_folders );
		}
		$this->synchroniseFolders ( $folders );
	}
	/**
	 * @param string $str_folders
	 * @return array
	 */
	private function parseFolders($str_folders) {
		$folders = array ();
		foreach ( explode ( self::FOLDER_SEPERATOR, $str_folders ) as $folder ) {
			if (FALSE !== strpos ( $folder, self::TARGET_SEPERATOR )) {
				list ( $source, $target ) = explode ( self::TARGET_SEPERATOR, $folder );
				$folders [trim ( $source )] = trim ( $target );
			}
		}
		return $folders;
	}
	/**
	 * @param array $folders
	 * @throws Exception
	 */
	private function synchroniseFolders(array $folders) {
		foreach ( $folders as $source => $target ) {
			if (FALSE === is_dir ( $source )) {
				$source = PATH_site . $source;
				if (FALSE === is_dir ( $source )) {
					throw new Exception ( 'invalid source folder: ' . $source );
				}
			}
			if (FALSE === is_dir ( $target )) {
				$target = PATH_site . $target;
				if (FALSE === is_dir ( $target )) {
					throw new Exception ( 'invalid target folder: ' . $target );
				}
			}
			if (FALSE === is_readable ( $source )) {
				throw new Exception ( 'source not is_readable: ' . $source );
			}
			if (FALSE === is_writeable ( $target )) {
				throw new Exception ( 'source not writable: ' . $target );
			}
			$this->sync ( realpath ( $source ), realpath ( $target ) );
		}
	}
	/**
	 * Sync two directorys with each other.
	 *
	 * @param string $sourceLocation
	 * @param string $targetLocation
	 * @return string rsync command
	 * @throws RuntimeException
	 */
	private function sync($sourceLocation, $targetLocation) {
		$options = '--force --omit-dir-times --ignore-errors --archive --partial --perms  --ignore-existing';
		$command = 'rsync ' . $options;
		if (substr ( $sourceLocation, - 1 ) != DIRECTORY_SEPARATOR) {
			$sourceLocation .= DIRECTORY_SEPARATOR;
		}
		if (substr ( $targetLocation, - 1 ) != DIRECTORY_SEPARATOR) {
			$targetLocation .= DIRECTORY_SEPARATOR;
		}
		$command .= ' ' . $sourceLocation;
		$command .= ' ' . $targetLocation;
		$command = escapeshellcmd ( $command );
		if (FALSE === system($command)) {
			throw new RuntimeException ( 'Error on system command execution! Command:' . $command  );
		}
	}
}
if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/staticpub/class.tx_staticpub_export.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/staticpub/class.tx_staticpub_export.php']);
}