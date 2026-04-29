<?php
/***********************************************************************************************************************
 * EXT: wv_deepltranslate: Define fields for Deepl translation
 * See also: http/typo3conf/ext/wv_deepltranslate/Configuration/TCA/Overrides/*.*
 * Note: if this is not working, check, that these settings are not overwritten on other places
 **********************************************************************************************************************/

$GLOBALS['TCA']['pages']['columns']['title']['l10n_mode'] = 'prefixLangTitle';
$GLOBALS['TCA']['pages']['columns']['nav_title']['l10n_mode'] = 'prefixLangTitle';
$GLOBALS['TCA']['pages']['columns']['subtitle']['l10n_mode'] = 'prefixLangTitle';