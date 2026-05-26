<?php

declare(strict_types=1);

namespace Jvelletti\JvDeepltranslatePiflexform\Event\Listener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\Deepltranslate\Core\Exception\ApiKeyNotSetException;
use WebVision\Deepltranslate\Core\Service\UsageService;

class ConfigToolBarEventListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private UsageService $usageService;

    public function __construct(
        UsageService $usageService
    ) {
        $this->usageService = $usageService;
    }

    public function __invoke(SystemInformationToolbarCollectorEvent $systemInformation): void
    {

         //   $severity = InformationStatus::STATUS_ERROR;
        //   $severity =  InformationStatus::STATUS_WARNING;
        //    $severity =  InformationStatus::STATUS_INFO;
        $severity =  InformationStatus::STATUS_NOTICE;
        $BEutility = new \WebVision\Deepltranslate\Core\Utility\DeeplBackendUtility() ;

        $title = "Deepl Status: ";
        $message = $BEutility->getApiKey() ? "API Key is set" : "API Key is missing";
        if ( isset($GLOBALS['TYPO3_CONF_VARS']['HTTP']['auth']) && count($GLOBALS['TYPO3_CONF_VARS']['HTTP']['auth']) > 1 ) {
            $message .= " | ERROR: HTTP Authentication is set !";
            $severity = InformationStatus::STATUS_ERROR;
        } else {
            $message .= " | OK: HTTP Auth not set";
        }
        try {
            $usage =  $this->usageService->getCurrentUsage();
            if ($usage === null || $usage->character === null) {
                $message .= " | No usage information retrieve - " . var_export($usage , true);
            } else {
                // everthing is working ..
                $title = "Deepl Config OK:";
                $message = $this->getLanguageService()->sL(
                    'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:usages.toolbar.message'
                );

                $severity = $this->usageService->determineSeverityForSystemInformation($usage->character->count, $usage->character->limit);

                $message =    sprintf(
                    $message,
                    $this->usageService->formatNumber($usage->character->count),
                    $this->usageService->formatNumber($usage->character->limit)
                ) ;

                if ( isset(  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['jv_deepltranslate_piflexform']['fieldsToTranslate'] )) {
                    $message .= " | " . count( $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['jv_deepltranslate_piflexform']['fieldsToTranslate'] ) . " Flexform fields configured";
                } else {
                    $message .= " | Translation of FlexForms not configured !";
                }
            }
        } catch (\Exception $e) {
            $message = $message . " " . $e->getMessage();
            $severity = InformationStatus::STATUS_ERROR;
        }

        $systemInformation->getToolbarItem()->addSystemInformation(
            $title,
            $message,
            'actions-localize-deepl',
            $severity
        );
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
