<?php
// tx_jvevents_domain_model_category
if ( isset( $GLOBALS['TCA']['tx_jvevents_domain_model_category'])) {
    $GLOBALS['TCA']['tx_jvevents_domain_model_category']['columns']['title']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_jvevents_domain_model_category']['columns']['description']['l10n_mode'] = 'prefixLangTitle';
}
