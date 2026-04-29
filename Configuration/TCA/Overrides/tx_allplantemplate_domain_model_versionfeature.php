<?php
// tx_allplantemplate_domain_model_versionfeature
// see also vendor/straight/sul-sst/Configuration/TCA/Overrides

if ( isset( $GLOBALS['TCA']['tx_allplantemplate_domain_model_versionfeature'])) {
    $GLOBALS['TCA']['tx_allplantemplate_domain_model_versionfeature']['columns']['plain']['l10n_mode'] = 'prefixLangTitle';
}