<?php

/**
 * Extension backend registration
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {
    // Add Web>Info module:
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction('web_info',
        \SFC\NcStaticfilecache\Module\CacheModule::class, null,
        'LLL:EXT:nc_staticfilecache/Resources/Private/Language/locallang.xml:module.title');
}
