<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	// Setting up TSFE hooks for static publishing:
if (TYPO3_MODE=='FE')	{
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache']['tx_staticpub'] = 'EXT:staticpub/class.tx_staticpub_fe.php:tx_staticpub_fe';
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_staticpub'] = 'EXT:staticpub/class.tx_staticpub_fe.php:&tx_staticpub_fe->fe_headerNoCache';
}

	// Register "Processing Instruction" key and label with "crawler" extension:
$TYPO3_CONF_VARS['EXTCONF']['crawler']['procInstructions']['tx_staticpub_publish'] = 'Publish static';

?>