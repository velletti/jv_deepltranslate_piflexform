.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: Includes.txt

.. _start:


============
Installation
============

     composer require jvelletti/jv_deepltranslate_piflexform

Configuration
"""""""""""""
requires a php array like this:


 $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['jv_deepltranslate_piflexform']['fieldsToTranslate'] = [
            // Fields from default flexforms
           'settings.content'            => [],
           'settings.content1'           => [],
           'settings.content2'           => [],
           'settings.content3'           => [],

            // Accordion (flexform_accordion.xml)
            // settings.accordionElements->el->'...'->settings.accordionElement->'el'->flexFormFieldsToTranslate

            'settings.accordionElements' => [
                'translateNestedFlexform' => true,
                'mainFlexformField' => 'settings.accordionElement',
                'flexFormFieldsToTranslate' => [
                    'headline',
                    'text',
                ],
           ],
] ;






