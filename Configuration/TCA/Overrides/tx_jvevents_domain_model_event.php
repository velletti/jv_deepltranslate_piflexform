<?php
// tx_jvevents_domain_model_event
if ( isset( $GLOBALS['TCA']['tx_jvevents_domain_model_event'])) {
    $GLOBALS['TCA']['tx_jvevents_domain_model_event']['columns']['name']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_jvevents_domain_model_event']['columns']['teaser']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_jvevents_domain_model_event']['columns']['description']['l10n_mode'] = 'prefixLangTitle';
}