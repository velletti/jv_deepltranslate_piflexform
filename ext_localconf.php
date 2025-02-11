<?php

if (!defined('TYPO3')) {
    die();
}

(static function (): void {

    // Hook to handle translations of flexforms
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['checkFlexFormValue']['jv_deepltranslate_piflexform']
        = \Jvelletti\JvDeepltranslatePiflexform\Hooks\TranslateHook::class;


    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['jv_deepltranslate_piflexform']['fieldsToTranslate'] = [
        // Fields from default flexforms
       'settings.content'            => [],
       'settings.content1'           => [],
       'settings.content2'           => [],
       'settings.content3'           => [],

       'settings.headline'           => [],
       'settings.headlineColumns'    => [],
       'settings.headlineColumn1'    => [],
       'settings.headlineColumn2'    => [],
       'settings.headlineColumn3'    => [],
       'settings.headlineColumn4'    => [],
       'settings.headlineTable'      => [],
       'settings.headlineFirstPart'  => [],
       'settings.headlineSecondPart' => [],

       'settings.info1'              => [],
       'settings.info2'              => [],
       'settings.info3'              => [],

       'settings.subline1'           => [],
       'settings.subline2'           => [],
       'settings.videoLinkText'      => [],

       'settings.dateMonth'          => [],
       'settings.category'           => [],

       'settings.quote'              => [],
       'settings.author'             => [],
       'settings.location'           => [],

       'settings.referenceType'      => [],


        // Nested flexforms => function translateNestedFlexform()
        // Example (function matrix):
        // settings.tableRow->el->'...'->settings.tableRowElement->'el'->flexFormFieldsToTranslate

        // Accordion (flexform_accordion.xml)
       'settings.accordionElements' => [
          'translateNestedFlexform' => true,
          'mainFlexformField' => 'settings.accordionElement',
          'flexFormFieldsToTranslate' => [
             'headline',
             'text',
          ],
       ],

        // Contacts (flexform_contacts.xml)
       'settings.contactsElements' => [
          'translateNestedFlexform' => true,
          'mainFlexformField' => 'settings.contactsElement',
          'flexFormFieldsToTranslate' => [
             'text',
          ],
       ],

        // Function matrix (flexform_functionMatrix.xml)
       'settings.tableRow' => [
          'translateNestedFlexform' => true,
          'mainFlexformField' => 'settings.tableRowElement',
           // We leave out the fields "option{nr}" (=> value is 'yes'/'no')
          'flexFormFieldsToTranslate' => [
             'headlineRow',
             'descriptionRow',
             'alternativeText1',
             'alternativeText2',
             'alternativeText3',
             'alternativeText4',
          ],
       ],
    ];

})();
