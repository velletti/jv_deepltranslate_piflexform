<?php

$EM_CONF['jv_deepltranslate_piflexform'] = [
    'title' => 'DeepL Translate Flexforms',
    'description' => 'Extends the extension wv_deepltranslate to handle translations of flexforms',
    'category' => 'module,backend',
	'author' => 'Joerg Velletti',
	'author_email' => 'typo3@velletti.de',
    'state' => 'beta',
    'version' => '12.4.0',
    'constraints' => [
        'depends' => [
            'wv_deepltranslate' => '4.3.0-4.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
