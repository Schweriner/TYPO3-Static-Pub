<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		'web_info',
		'tx_staticpub_modfunc1',
		NULL,
		'LLL:EXT:staticpub/locallang_db.xml:moduleFunction.tx_staticpub_modfunc1'
	);

}

?>