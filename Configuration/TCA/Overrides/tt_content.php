<?php
defined('TYPO3') || die();

/***********************************************************************************************************************
 * EXT: wv_deepltranslate: Define fields for Deepl translation
 * See also: http/typo3conf/ext/wv_deepltranslate/Configuration/TCA/Overrides/*.*
 * Note: if this is not working, check, that these settings are not overwritten on other places
 **********************************************************************************************************************/

// sys_category
$GLOBALS['TCA']['sys_category']['columns']['title']['l10n_mode'] = 'prefixLangTitle';

// tt_address
if ( isset( $GLOBALS['TCA']['tt_address'])) {
    $GLOBALS['TCA']['tt_address']['columns']['description']['l10n_mode'] = 'prefixLangTitle';
}

// tt_content
$GLOBALS['TCA']['tt_content']['columns']['header']['l10n_mode'] = 'prefixLangTitle';
$GLOBALS['TCA']['tt_content']['columns']['subheader']['l10n_mode'] = 'prefixLangTitle';
$GLOBALS['TCA']['tt_content']['columns']['bodytext']['l10n_mode'] = 'prefixLangTitle';


// tx_mailsignature_domain_model_signature
if ( isset( $GLOBALS['TCA']['tx_mailsignature_domain_model_signature'])) {
    $GLOBALS['TCA']['tx_mailsignature_domain_model_signature']['columns']['html']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_mailsignature_domain_model_signature']['columns']['plain']['l10n_mode'] = 'prefixLangTitle';
}
