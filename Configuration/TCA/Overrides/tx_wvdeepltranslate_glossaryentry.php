<?php
defined('TYPO3') || die();

// Bug in EXT:wv_deepltranslate, exclude not set to provide user right settings
if ( isset( $GLOBALS['TCA']['tx_wvdeepltranslate_glossaryentry'])) {
    $GLOBALS['TCA']['tx_wvdeepltranslate_glossaryentry']['columns']['term']['exclude'] = true;
}