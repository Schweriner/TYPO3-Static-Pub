<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	// Setting up TSFE hooks for static publishing:
if (TYPO3_MODE=='FE')	{
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache']['tx_staticpub'] = 'EXT:staticpub/class.tx_staticpub_fe.php:tx_staticpub_fe';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_staticpub'] = 'EXT:staticpub/class.tx_staticpub_fe.php:&tx_staticpub_fe->fe_headerNoCache';
}

	// Register "Processing Instruction" key and label with "crawler" extension:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_staticpub_publish'] = 'Publish static';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['pollSuccess'][] = 'tx_staticpub';

if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticpub']['publishDir'])) {
 $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticpub']['publishDir'] = '_staticpub_';
}

if (TYPO3_MODE=='BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_staticpub_tasks_export'] = array(
		'extension'        => 'staticpub',
		'title'            => 'LLL:EXT:staticpub/locallang_db.php:staticpub_tasks_export.name',
		'description'      => 'LLL:EXT:staticpub/locallang_db.php:staticpub_tasks_export.description',
		'additionalFields' => 'tx_staticpub_tasks_export_AdditionalFieldProvider'
	);
}
?>