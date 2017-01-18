<?php

$additionalTtContentFields = [
    'tx_ncstaticfilecache_cache' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:nc_staticfilecache/Resources/Private/Language/locallang.xml:nc_staticfilecache.field',
        'config' => [
            'type' => 'check',
            'default' => '1',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $additionalTtContentFields);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_ncstaticfilecache_cache;;;;1-1-1');