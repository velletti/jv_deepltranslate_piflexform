<?php

$EM_CONF['jv_deepltranslate_piflexform'] = [
    'title' => 'DeepL Translate Flexforms',
    'description' => 'Extends the extension wv_deepltranslate core to handle translations of flexforms',
    'category' => 'module,backend',
	'author' => 'Joerg Velletti',
	'author_email' => 'typo3@velletti.de',
    'state' => 'beta',
    'version' => '12.4.3',
    'constraints' => [
        'depends' => [
            'deepltranslate_core' => '4.4.0-5.9.99',
            'typo3' => '11.4.0-12.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
