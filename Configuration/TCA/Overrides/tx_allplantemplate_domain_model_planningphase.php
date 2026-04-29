<?php

// tx_allplantemplate_domain_model_planningphase
// see also vendor/straight/sul-sst/Configuration/TCA/Overrides
if ( isset( $GLOBALS['TCA']['tx_allplantemplate_domain_model_planningphase'])) {
    $GLOBALS['TCA']['tx_allplantemplate_domain_model_planningphase']['columns']['planning_phase']['l10n_mode'] = 'prefixLangTitle';
}