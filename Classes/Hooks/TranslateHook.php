<?php

namespace Jvelletti\JvDeepltranslatePiflexform\Hooks;

/**
 * J Vellettis TYPO3 Extension Template for Deepl translations of flexforms
 */


use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use WebVision\WvDeepltranslate\ClientInterface;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException as WvDeepltranslateLanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Exception\LanguageRecordNotFoundException as WvDeepltranslateLanguageRecordNotFoundException;
use WebVision\WvDeepltranslate\Hooks\TranslateHook as WvDeepltranslateTranslateHook;
use WebVision\WvDeepltranslate\Service\LanguageService;

/**
 * TYPO3
 */

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Php
 */

use Exception;

class TranslateHook
{

    /**
     * Class variables
     * =================================================================================================================
     */

    /**
     * @var int
     */
    protected int $currentRecordId;

    /**
     * @var int
     */
    protected int $sourceLanguageUid;

    /**
     * @var int
     */
    protected int $targetLanguageUid;

    private $client;

    public function __construct(
    ) {

        if ( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('wv_deepltranslate') ) {
            $config = GeneralUtility::makeInstance(\WebVision\WvDeepltranslate\Configuration::class);

            /** @var \WebVision\WvDeepltranslate\ClientInterface $client */
            $this->client = GeneralUtility::makeInstance(\WebVision\WvDeepltranslate\Client::class, $config);

        } else if ( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('deepltranslate_core') ) {
            $config = GeneralUtility::makeInstance(\WebVision\Deepltranslate\Core\Configuration::class);

            /** @var \WebVision\Deepltranslate\Core\ClientInterface $client */
            $this->client = GeneralUtility::makeInstance(\WebVision\Deepltranslate\Core\Client::class, $config);
        }



    }
    /**
     * Getters and setters
     * =================================================================================================================
     */



    protected function getCurrentRecordId(): int
    {
        return $this->currentRecordId;
    }

    protected function setCurrentRecordId(int $currentRecordId): void
    {
        $this->currentRecordId = $currentRecordId;
    }

    protected function getSourceLanguageUid(): int
    {
        return $this->sourceLanguageUid;
    }

    protected function setSourceLanguageUid(int $sourceLanguageUid): void
    {
        $this->sourceLanguageUid = $sourceLanguageUid;
    }

    protected function getTargetLanguageUid(): int
    {
        return $this->targetLanguageUid;
    }

    protected function setTargetLanguageUid(int $targetLanguageUid): void
    {
        $this->targetLanguageUid = $targetLanguageUid;
    }

    /**
     * Set the parameters for translations coming from TYPO3 request, e.g.:
     * [getQueryParams] => Array
     *   (
     *     [token] => b06db5dcc579e45445da57ff77bab419467e0c73
     *     [pageId] => 20228
     *     [srcLanguageId] => auto-0
     *     [destLanguageId] => 1
     *     [action] => localizedeeplauto
     *     [uidList] => Array
     *       (
     *         [0] => 283266
     *       )
     *     [route] => /ajax/records/localize
     *   )
     *
     * @return void
     * @throws Exception
     */
    protected function setParametersForTranslationFromRequest()
    {

        $queryParameters = $GLOBALS['TYPO3_REQUEST']->getQueryParams();

        // Current record id (tt_content)
        if (!isset($queryParameters['uidList'][0])) {
            throw new Exception('Could not get record id from query parameters', '1704787189');
        }
        $this->setCurrentRecordId((int)$queryParameters['uidList'][0]);

        // Source language uid
        if (!isset($queryParameters['srcLanguageId'])) {
            throw new Exception('Could not get source language uid from query parameters', '1704787380');
        }
        $srcLanguageId = str_replace('auto-', '', $queryParameters['srcLanguageId']);
        $this->setSourceLanguageUid((int)$srcLanguageId);

        // Target language uid
        if (!isset($queryParameters['destLanguageId'])) {
            throw new Exception('Could not get target language uid from query parameters', '1704787570');
        }
        $this->setTargetLanguageUid((int)$queryParameters['destLanguageId']);

    }

    /**
     * Functions
     * =================================================================================================================
     */

    /**
     * Function to handle Deepl translations for flexforms
     * => Hook function defined in TYPO3 data handler => see annotation below
     * => This function is called only, if we have flexforms
     *
     * How to add functionality:
     * 1) Activate mail in the first lines of this function
     * 2) Check the given flexform structure
     * 3) Use the function translateNestedFlexform() or create a customized function for this, if needed and add the config to the array $flexFormFieldsToTranslate
     *
     * @param DataHandler $dataHandler
     * @param array $currentValue
     * @param array $newValue
     * @return void
     * @see https://github.com/web-vision/wv_deepltranslate/issues/285
     * @see http/typo3/sysext/core/Classes/DataHandling/DataHandler.php->checkValueForFlex()
     * @see http/typo3/sysext/extbase/Classes/Hook/DataHandler/CheckFlexFormValue.php->checkFlexFormValue_beforeMerge()
     */
    public function checkFlexFormValue_beforeMerge(DataHandler $dataHandler, array &$currentValue, array &$newValue)
    {

        // We only want to execute this function, if a deepl function of EXT:wv_deepltranslate is used
        if ( !$GLOBALS['TYPO3_REQUEST'] ) {
            return;
        }
        $action = $GLOBALS['TYPO3_REQUEST']->getQueryParams() ? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['action'] : '';
        if (!in_array($action, ['localizedeepl', 'localizedeeplauto'])) {
            return;
        }
        try {
            $this->setParametersForTranslationFromRequest();

        } catch (Exception $e) {

            $flashMessage = GeneralUtility::makeInstance(
               FlashMessage::class,
               'Translation failed: ' . $e->getMessage() ,
               '',
               $this->getServityERROR(),
               true
            );
            // @extensionScannerIgnoreLine
            GeneralUtility::makeInstance(FlashMessageService::class)
               ->getMessageQueueByIdentifier()
               ->addMessage($flashMessage);

            return;
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['jv_deepltranslate_piflexform']['fieldsToTranslate'])) {
            return;
        }

        $flexFormFieldsToTranslate =  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['jv_deepltranslate_piflexform']['fieldsToTranslate'] ;

        // Loop through the defined flexform fields
        foreach($flexFormFieldsToTranslate as $flexformField => $config) {

            // If field does not exist in flexform => continue
            if (!isset($newValue['data']['main']['lDEF'][$flexformField])) {
                continue;
            }

            // Translate
            try {

                // If function translateNestedFlexform() is defined for translation
                if (isset($config['translateNestedFlexform']) && $config['translateNestedFlexform']) {

                    // Translate special nested flexform fields
                    $newValue['data']['main']['lDEF'][$flexformField] = $this->translateNestedFlexform(
                       $newValue['data']['main']['lDEF'][$flexformField],
                       $config['mainFlexformField'],
                       $config['flexFormFieldsToTranslate']
                    );

                } else {
                    // Translate default flexform fields
                    $content = $newValue['data']['main']['lDEF'][$flexformField]['vDEF'];
                    $translatedContent = $this->translate($content);
                    $newValue['data']['main']['lDEF'][$flexformField]['vDEF'] = $translatedContent;

                }

            } catch (Exception $e) {

                $flashMessage = GeneralUtility::makeInstance(
                   FlashMessage::class,
                   'Translation failed: ' . $e->getMessage() ,
                   '',
                   $this->getServityERROR(),
                   true
                );
                // @extensionScannerIgnoreLine
                GeneralUtility::makeInstance(FlashMessageService::class)
                   ->getMessageQueueByIdentifier()
                   ->addMessage($flashMessage);

                return;
            }
        }
    }
    private function getServityERROR()
    {
        return \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR;
        // return ContextualFeedbackSeverity::ERROR;
    }

    /**
     * Translate a given content by Deepl provided by EXT:wv_deepltranslate
     * @param string|null $content
     * @return string|null
     * @throws Exception
     */
    private function translate(?string $content): ?string
    {
        if (empty($content)) {
            return $content;
        }

        // Get the parameters
        $tableName = 'tt_content';
        $customMode = 'deepl';
        $currentRecordId = $this->getCurrentRecordId();
        $targetLanguageUid = $this->getTargetLanguageUid();

        // Get site information (from EXT:wv_deepltranslate)
        $languageService = GeneralUtility::makeInstance(LanguageService::class);

        try {

            $siteInformation = $this->getCurrentSite($tableName, $currentRecordId);
            if ( !$siteInformation ) {
                throw new Exception('Could not get site information', '1704787571');
            }

            // Get source language record (from EXT:wv_deepltranslate)
            $sourceLanguageRecord = $languageService->getSourceLanguage(
               $siteInformation
            );
            $sourceLanguage = ($sourceLanguageRecord['language_isocode'] ?? false) ;

            // Get target language record (from EXT:wv_deepltranslate)
            $targetLanguageRecord = $languageService->getTargetLanguage(
               $siteInformation,
               $targetLanguageUid
            );
            $targetLanguage = ($targetLanguageRecord['language_isocode'] ?? false );

            if ( !$sourceLanguage || !$targetLanguage ) {
                return $content ;
            }

        } catch (WvDeepltranslateLanguageIsoCodeNotFoundException|WvDeepltranslateLanguageRecordNotFoundException $e) {

            throw new Exception($e->getMessage(), '1704792782');

        }
        try {

            $translatedContent = $this->client->translate(
               $content,
               $sourceLanguage ,
               $targetLanguage
            ) ;
        } catch (Exception $e) {
            return $e->getMessage() ;

        }
        return $translatedContent;

    }
    protected function getPageIdFromRequest(): int
    {
        if ($GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
            $queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams();
            if (isset($queryParams['pageId'])) {
                return (int)$queryParams['pageId'];
            }
        }
        throw new Exception('Page ID not found in request', 1704787571);
    }
    protected function getCurrentSite(): ?Site
    {
        $pageId = $this->getPageIdFromRequest();
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        try {
            return $siteFinder->getSiteByPageId($pageId);
        } catch (\TYPO3\CMS\Core\Exception\SiteNotFoundException $e) {
            return null;
        }
    }

    /**
     * Special case: nested flexforms like Function matrix or Contacts
     * @param array $flexFormData
     * @param string $flexformField
     * @param array $flexFormFieldsToTranslate
     * @return array
     * @throws Exception
     */
    private function translateNestedFlexform(array $flexFormData, string $flexformField, array $flexFormFieldsToTranslate): array
    {
        // Copy the flexform data
        $newFlexFormData = $flexFormData;

        foreach($flexFormData['el'] as $key => $element) {

            foreach($element[$flexformField]['el'] as $fieldKey => $fieldArray) {

                // If field should be translated
                if (in_array($fieldKey, $flexFormFieldsToTranslate)) {

                    // Current content
                    $content = $flexFormData['el'][$key][$flexformField]['el'][$fieldKey]['vDEF'];

                    // Translate it
                    $translatedContent = $this->translate($content);

                    // Write it back
                    $newFlexFormData['el'][$key][$flexformField]['el'][$fieldKey]['vDEF'] = $translatedContent;
                }
            }
        }
        return $newFlexFormData;
    }
}