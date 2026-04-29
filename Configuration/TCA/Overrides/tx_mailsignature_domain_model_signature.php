<?php
defined('TYPO3') || die();
if ( isset( $GLOBALS['TCA']['tx_mailsignature_domain_model_signature'])) {
    $GLOBALS['TCA']['tx_mailsignature_domain_model_signature']['columns']['html']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_mailsignature_domain_model_signature']['columns']['plain']['l10n_mode'] = 'prefixLangTitle';
}