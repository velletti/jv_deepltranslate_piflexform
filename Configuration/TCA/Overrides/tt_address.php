<?php
defined('TYPO3') || die();

// tt_address
if ( isset( $GLOBALS['TCA']['tt_address'])) {
    $GLOBALS['TCA']['tt_address']['columns']['description']['l10n_mode'] = 'prefixLangTitle';
}