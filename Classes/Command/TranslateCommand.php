<?php
namespace Jvelletti\JvDeepltranslatePiflexform\Command;

use DeepL\Translator;
use DeepL\TranslatorOptions;
use PDO;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class NotifyCommand
 * @author Jörg Velletti <typo3@velletti.de>
 * @package JVE\Jvchat\Command
 */
class TranslateCommand extends Command {


    /**
     * @var array
     */
    private $extConf = [] ;

    private int $wait = 5 ;

    private bool $setApproved = false ;

    /** @var \DeepL\Translator  */
    private $translator  ;

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Translate XLF files with DeepL')
            ->setHelp('Get list of Options: .' . LF . 'use the --help option.')
            ->addArgument(
                'source',
                null ,
                "relative path to the source locallang.xlf file"
            )
            ->addArgument(
                'target',
                null ,
                'target language, like "de" or "it" or "all" for all languages'
            )->addOption(
                'wait',
                'w',
                InputOption::VALUE_OPTIONAL,
                'Wait time when executing deepl translate in seconds. needed for the DeepL API to process the request. Default is 5 seconds.',
                5
            )->addOption(
                'ext',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Optional: Extension key. If set, the source file will be searched in the extension path. EXT:this value/Resources/Private/Language/ ',
                5
            )->addOption(
            'approved',
            'a',
            InputOption::VALUE_OPTIONAL,
            'set approved to yes for all translations, default is no',
            "no"
            ) ;

    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $options[TranslatorOptions::HTTP_CLIENT] = GeneralUtility::makeInstance(GuzzleClientFactory::class)->getClient();
        if ( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('wv_deepltranslate') ) {
            $config = GeneralUtility::makeInstance(\WebVision\WvDeepltranslate\Configuration::class);


        } else if ( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('deepltranslate_core') ) {
            $config = GeneralUtility::makeInstance(\WebVision\Deepltranslate\Core\Configuration::class);
        }
        if ( !$config ) {
            $io->error('DeepL Client Config not found - please check your configuration in the Extension Manager');
            return 1;
        }
        $this->translator = new Translator($config->getApiKey(), $options);
        
        if ( !$this->translator  ) {
            $io->error('DeepL Client not found ');
            return 1;
        }



        $io->title($this->getDescription());
        $error = 0 ;
        if ($input->getArgument('source') ) {
            $source = (string)trim($input->getArgument('source')) ;
            $io->writeln('File to translate from: '. $source );
        } else {
            $io->error('No source file given, use --source option');
            return 1;
        }
        if ($input->getOption('ext') ) {
            $io->writeln('Got extension key: '. (string)$input->getOption('ext') );
                $source = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath( (string)$input->getOption('ext')) . 'Resources/Private/Language/' . $source;
                $io->writeln('Search Source file in extension path: '. $source );
        }

        // check if source file exists
        if (!file_exists($source)) {
            $io->error('Source file does not exist: ' . $source);
            return 1;
        }
        if ($input->getArgument('target') ) {
            $targetLanguage= (string)trim($input->getArgument('target')) ;
            $io->writeln('Target language is: '. $targetLanguage);
        } else {
            $io->error('No target language given, use --target option');
            return 1;
        }
        $this->wait = 5 ;
        if ($input->getOption('wait') ) {
            $this->wait = (int)trim($input->getOption('wait')) ;
            $io->writeln('Will wait  : '. $this->wait . " seconds for the DeepL API to process the next request." );
        }
        if ($input->getOption('approved') && strtolower(trim($input->getOption('approved'))) === 'yes') {
            $this->setApproved = true;
            $io->writeln('Translatens will be set to approved: yes' );
        }

        $io->newLine(1);

        $sourceContent = file_get_contents($source);
        if ($sourceContent === false) {
            $io->error('Could not read source file: ' . $source);
            return 1;
        }
        try {
            $xmlSource = simplexml_load_string($sourceContent);
            if ( $xmlSource === false || !($xmlSource instanceof \SimpleXMLElement) ) {
                $io->error('Could not parse source file: ' . $source);
                return 1;
            }
            $sourceLanguage = (string)($xmlSource->file['source-language'] ?? 'default');
            $io->writeln("Source language is: " . $sourceLanguage);
        } catch (\Exception $e) {
            $io->error('Could not parse source file: ' . $source . ' - ' . $e->getMessage());
            return 1;
        }
        $targetFile = pathinfo($source)['dirname'] . '/' . $targetLanguage. "." . pathinfo($source)['filename'] . '.xlf';
        $io->writeln("Target File is: " . $targetFile);
       

        return $this->translateXlfFile($io , $xmlSource, $targetLanguage, $targetFile ); ;
    }

    private function translateXlfFile( $io , \SimpleXMLElement $xmlSource, string $targetLanguage , string $targetFile): int
    {
        $errorCount = 0 ;
        $SourceArray = $this->doParsingFromRoot($xmlSource);

        if (empty($SourceArray)) {
            $io->error('No translations found in source file');
            return 1;
        }


        if (file_exists($targetFile)) {
            $targetContent = file_get_contents($targetFile);
            if ($targetContent === false) {
                $io->error('Could not read source file: ' . $targetFile);
                return 1;
            }
            try {
                $xmlTarget = simplexml_load_string($targetContent);
                if ( $xmlTarget === false || !($xmlTarget instanceof \SimpleXMLElement) ) {
                    $io->error('Could not parse source file: ' . $targetFile);
                    return 1;
                }
            } catch (\Exception $e) {
                $io->error('Could not parse source file: ' . $targetFile . ' - ' . $e->getMessage());
                return 1;
            }
            $targetArray = $this->doParsingFromRoot($xmlTarget , $targetLanguage);


        } else {
            $io->writeln('Target file does not exist, will create it: ' . $targetFile);
            $targetArray = [] ;
        }

        $io->newLine(1);
        $io->writeln("Source has : " . count( $SourceArray) . " translations, target has: " . count( $targetArray ) . " translations");
        $io->newLine(1);

        $resultArray = [];



        foreach ($SourceArray as $id => $SourceElement) {
            $sourceText = $SourceElement[0]['source'] ?? '';
            if (empty($sourceText)) {
                $io->writeln("No source text found for ID " . $id . ", skipping.");
                continue;
            }


            if (isset($targetArray[$id]) && is_array($targetArray[$id]) && count($targetArray[$id]) > 0) {
                if( ( isset( $targetArray[$id][0]['source']) && ( $targetArray[$id][0]['source'] == $sourceText )
                        ||
                      empty( $targetArray[$id][0]['source'])
                    )
                    && isset($targetArray[$id][0]['target'])
                    && !empty($targetArray[$id][0]['target'])
                ) {
                    // If the source text is already translated in the target file, skip it
                    $resultArray[$id] = $targetArray[$id] ;
                    if( empty( $targetArray[$id][0]['source']) ) {
                        // but we need to set the source text, as in Old XLIFF files the source text is not always set
                        $resultArray[$id][0]['source'] = $sourceText ;
                        $io->writeln("Translation for ID: " . $id . " already exists, but added Source  in target file.");
                    } else {
                        $io->writeln("Translation for ID: " . $id . " already exists in target file.");
                    }
                   
                    continue;
                }

            }


            try {

                // Call DeepL API to translate the source text
                $translatedText = $this->translator->translateText(
                    $sourceText,
                    "en" ,
                    $targetLanguage
                );

            } catch (Exception $e) {
                $io->error( $e->getMessage()) ;

            }



            if (empty($translatedText)) {
                $io->error('Error translating text for ID ' . $id);
                $this->setApproved = false ;
                $errorCount ++ ;
                $resultArray[$id] = $targetArray[$id] ;
                sleep($this->wait);
                continue;
            }
            $io->writeln("<success>Translation for ID: " . $id . " en= '" . $sourceText . "' 
            to " . $targetLanguage . "= '" . $translatedText . "</success>'");

            sleep($this->wait);
            // Add the translated text to the target array
            $resultArray[$id][0] = [
                'source' => $sourceText,
                'target' => $translatedText,
            ];
        }

        // now we check the target array for existing translations taht are NOT in the source array
        foreach ($targetArray as $id => $translatedText) {
            if (isset($resultArray[$id]) && is_array($resultArray[$id]) && count($resultArray[$id]) > 0) {
                // If the ID is already in the result array, skip it
                continue;
            }
            if (isset($translatedText[0]['target']) && !empty($translatedText[0]['target'])) {
                // If the target text is already translated, skip it
                $io->writeln("Translation for ID: " . $id . " already exists in target file.");
                $resultArray[$id] = $translatedText ;
            }
        }
        $io->newLine(1);
        $this->storeResultToTargetFile($io, $targetFile, $resultArray, $targetLanguage);
        $io->newLine(1);
        return $errorCount ;

    }

    protected function doParsingFromRoot(\SimpleXMLElement $root , $languageKey = 'default'): array
    {
        $parsedData = [];
        $bodyOfFileTag = $root->file->body;
        $requireApprovedLocalizations = false ;

        if ($bodyOfFileTag instanceof \SimpleXMLElement) {
            foreach ($bodyOfFileTag->children() as $translationElement) {
                /** @var \SimpleXMLElement $translationElement */
                if ($translationElement->getName() === 'trans-unit' && !isset($translationElement['restype'])) {
                    // If restype would be set, it could be metadata from Gettext to XLIFF conversion (and we don't need this data)
                    if ($languageKey === 'default') {
                        // Default language coming from an XLIFF template (no target element)
                        $parsedData[(string)$translationElement['id']][0] = [
                            'source' => (string)$translationElement->source,
                            'target' => (string)$translationElement->source,
                        ];
                    } else {
                        $approved = (string)($translationElement['approved'] ?? 'yes');
                        if (!$requireApprovedLocalizations || $approved === 'yes') {
                            $parsedData[(string)$translationElement['id']][0] = [
                                'source' => (string)$translationElement->source,
                                'target' => (string)$translationElement->target,
                            ];
                        }
                    }
                } elseif ($translationElement->getName() === 'group' && isset($translationElement['restype']) && (string)$translationElement['restype'] === 'x-gettext-plurals') {
                    // This is a translation with plural forms
                    $parsedTranslationElement = [];
                    foreach ($translationElement->children() as $translationPluralForm) {
                        /** @var \SimpleXMLElement $translationPluralForm */
                        if ($translationPluralForm->getName() === 'trans-unit') {
                            // When using plural forms, ID looks like this: 1[0], 1[1] etc
                            $formIndex = substr((string)$translationPluralForm['id'], strpos((string)$translationPluralForm['id'], '[') + 1, -1);
                            if ($languageKey === 'default') {
                                // Default language come from XLIFF template (no target element)
                                $parsedTranslationElement[(int)$formIndex] = [
                                    'source' => (string)$translationPluralForm->source,
                                    'target' => (string)$translationPluralForm->source,
                                ];
                            } else {
                                $approved = (string)($translationPluralForm['approved'] ?? 'yes');
                                if (!$requireApprovedLocalizations || $approved === 'yes') {
                                    $parsedTranslationElement[(int)$formIndex] = [
                                        'source' => (string)$translationPluralForm->source,
                                        'target' => (string)$translationPluralForm->target,
                                    ];
                                }
                            }
                        }
                    }
                    if (!empty($parsedTranslationElement)) {
                        if (isset($translationElement['id'])) {
                            $id = (string)$translationElement['id'];
                        } else {
                            $id = (string)$translationElement->{'trans-unit'}[0]['id'];
                            $id = substr($id, 0, (int)strpos($id, '['));
                        }
                        $parsedData[$id] = $parsedTranslationElement;
                    }
                }
            }
        }
        return $parsedData;
    }

    protected function storeResultToTargetFile($io, $targetFile, $resultArray, $targetLanguage) {

        /*
        <?xml version="1.0" encoding="utf-8" standalone="yes" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" target-language="de" datatype="plaintext" original="messages" date="2021-11-23T08:12:03Z" product-name="deepltest">
                <header/>
                <body>
                    <trans-unit id="a.exists.translated" resname="a.exists.translated">
                        <source>I am translated</source>
                        <target>ich bin übersetzt</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>
        */
        $io->writeln("Storing results to target file: " . $targetFile);
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2"></xliff>');
        $file = $xml->addChild('file');
        $file->addAttribute('source-language', 'en');
        $file->addAttribute('target-language', $targetLanguage);
        $file->addAttribute('datatype', 'plaintext');
        $file->addAttribute('original', 'messages');
        $file->addAttribute('date', date('Y-m-d\TH:i:s\Z'));
        $body = $file->addChild('body');

        foreach ($resultArray as $id => $translation) {
            $transunit = $body->addChild('trans-unit');
            $transunit->addAttribute('id', $id);
            $transunit->addAttribute('resname', $id);
            if( $this->setApproved) {
                $transunit->addAttribute('approved', "yes");
            }
            $transunit->addChild('source', htmlspecialchars($translation[0]['source'] ?? ''));
            $transunit->addChild('target', htmlspecialchars($translation[0]['target'] ?? ''));

        }
        try {

            $io->write("xml generated for target file: " . $targetFile);
            $targetBak = $targetFile . "." . time() . ".bak" ;
            $io->write("Create backup target file: " . $targetBak );
            copy($targetFile, $targetBak );
            $formattedXml = $this->formatXML($xml->asXML());
            file_put_contents($targetFile, $formattedXml);
            $io->success('Target file created successfully: ' . $targetFile);
            return 0 ;
        } catch (\Exception $e) {
            $io->error('Error writing to target file: ' . $targetFile . ' - ' . $e->getMessage());
            return 1;
        }
    }

    function formatXML($xmlString) {
        // Erstelle ein neues DOMDocument Objekt
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Lade das XML und behalte Whitespace
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xmlString);

        // Setze die Formatierung
        $dom->formatOutput = true;

        // Gebe das formatierte XML zurück
        return $dom->saveXML();
    }
}