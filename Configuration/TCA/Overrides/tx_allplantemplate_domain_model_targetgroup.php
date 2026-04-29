<?php
// tx_allplantemplate_domain_model_targetgroup
// see also vendor/straight/sul-sst/Configuration/TCA/Overrides
if ( isset( $GLOBALS['TCA']['tx_allplantemplate_domain_model_targetgroup'])) {
    $GLOBALS['TCA']['tx_allplantemplate_domain_model_targetgroup']['columns']['target_group']['l10n_mode'] = 'prefixLangTitle';
}