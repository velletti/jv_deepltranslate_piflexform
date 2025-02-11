# jv_deepltranslate_piflexform

## Overview
`jv_deepltranslate_piflexform` is a TYPO3 extension that integrates DeepL translation services into the TYPO3 backend. 
It provides functionalities to translate also pi Flexform in tt_content elements using DeepL.
uses web-vision/deepltranslate_core as base library to integrate DeepL API.

## Installation
To install this extension, use Composer:
```bash
composer require jvelletti/jv_deepltranslate_piflexform

## configuration
1. install first the extension `deepltranslate_core` and configure the API key if not done
2. add a configuration array in the  `ext_tables.php` in yout template extension  to configure the extension

```php

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

``` 



## Internal reminder for the extension maintainer: 
To Update this extension in TER: 
change version Number to "x.y.z" in ext_emconf.php, Documentation\ in Settings.cfg and Index.rst
create Tag "x.y.z"
git push --tags

create new zip file:
cd vendor/jvelletti/jv-deepltranslate-piflexform
git archive -o jv_deepltranslate_piflexform_x.y.z.zip" HEAD

Upload ZIP File to https://extensions.typo3.org/my-extensions
git push

check:
https://intercept.typo3.com/admin/docs/deployments
https://packagist.org/packages/jvelletti/jv_deepltranslate_piflexform
https://extensions.typo3.org/extension/jv_deepltranslate_piflexform/
