<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{

		// Add Web>Info module:
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_staticpub_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_staticpub_modfunc1.php',
		'LLL:EXT:staticpub/locallang_db.php:moduleFunction.tx_staticpub_modfunc1'
	);
}
?>