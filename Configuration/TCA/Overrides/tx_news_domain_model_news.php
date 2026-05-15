<?php
defined('TYPO3') || die();
if ( isset( $GLOBALS['TCA']['tx_news_domain_model_news'])) {
    $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['title']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['teaser']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['bodytext']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['keywords']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['description']['l10n_mode'] = 'prefixLangTitle';
    $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['alternative_title']['l10n_mode'] = 'prefixLangTitle';
    
    if ( isset( $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['tx_allplan_news_extended_offering'])) {
        $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['tx_allplan_news_extended_offering']['l10n_mode'] = 'prefixLangTitle';
    }
    if ( isset( $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['tx_allplan_news_extended_you_and_us'])) {
        $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['tx_allplan_news_extended_you_and_us']['l10n_mode'] = 'prefixLangTitle';
    }

    if ( isset( $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['tx_allplan_news_extended_your_future'])) {
        $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['tx_allplan_news_extended_your_future']['l10n_mode'] = 'prefixLangTitle';
    }
}
    
    