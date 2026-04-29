<?php
defined('TYPO3') || die();

// tt_address
if ( isset( $GLOBALS['TCA']['sys_category'])) {
    $GLOBALS['TCA']['sys_category']['columns']['title']['l10n_mode'] = 'prefixLangTitle';
}
