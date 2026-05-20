<?php

declare(strict_types=1);

namespace Jvelletti\JvDeepltranslatePiflexform\Event\Listener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Core\Localization\LanguageService;
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

        $title = "Deepl Config";
        try {
            $info =  $this->usageService->getCurrentUsage();

            $message = var_export($info, true);
        } catch (\Exception $e) {
            $message = $e->getMessage();
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
