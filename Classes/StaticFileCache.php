<?php
/**
 * Static File Cache
 *
 * @package NcStaticfilecache
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache;

use SFC\NcStaticfilecache\Cache\UriFrontend;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Controller\CommandLineController;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Static File Cache
 *
 * @author Michiel Roos
 * @author Tim Lochmüller
 */
class StaticFileCache implements SingletonInterface {

	/**
	 * Configuration of the extension
	 *
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected $extKey = 'nc_staticfilecache';

	/**
	 * File table (old)
	 *
	 * @var string
	 */
	protected $fileTable = 'tx_ncstaticfilecache_file';

	/**
	 * If is debug enabled
	 *
	 * @var bool
	 */
	protected $isDebugEnabled = FALSE;

	/**
	 * Is clear cache processing enabled
	 *
	 * @var boolean
	 */
	protected $isClearCacheProcessingEnabled = TRUE;

	/**
	 * Cache
	 *
	 * @var UriFrontend
	 */
	protected $cache;

	/**
	 * Get the current object
	 *
	 * @return StaticFileCache
	 */
	public static function getInstance() {
		return GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\StaticFileCache');
	}

	/**
	 * Constructs this object.
	 */
	public function __construct() {
		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
		$cacheManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$this->cache = $cacheManager->getCache('static_file_cache');

		$this->configuration = GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\Configuration');
	}

	/**
	 * Enables the clear cache processing.
	 *
	 * @return void
	 * @see clearCachePostProc
	 */
	public function enableClearCacheProcessing() {
		$this->isClearCacheProcessingEnabled = TRUE;
	}

	/**
	 * Disables the clear cache processing.
	 *
	 * @return void
	 * @see clearCachePostProc
	 */
	public function disableClearCacheProcessing() {
		$this->isClearCacheProcessingEnabled = FALSE;
	}

	/**
	 * Clear cache post processor.
	 * The same structure as DataHandler::clear_cache
	 *
	 * @param    array       $params : parameter array
	 * @param    DataHandler $pObj   : partent object
	 *
	 * @return    void
	 */
	public function clearCachePostProc(array &$params, DataHandler &$pObj) {
		if ($this->isClearCacheProcessingEnabled === FALSE) {
			return;
		}

		if ($params['cacheCmd']) {
			$this->clearStaticFile($params);
			return;
		}

		// Do not do anything when inside a workspace
		if ($pObj->BE_USER->workspace > 0) {
			return;
		}

		$uid = intval($params['uid']);
		$table = strval($params['table']);

		if ($uid <= 0) {
			return;
		}

		// Get Page TSconfig relavant:
		list($tscPID) = BackendUtility::getTSCpid($table, $uid, '');
		$TSConfig = $pObj->getTCEMAIN_TSconfig($tscPID);

		if (!$TSConfig['clearCache_disable']) {
			// If table is "pages":
			if (ExtensionManagementUtility::isLoaded('cms')) {
				$list_cache = array();
				if ($table == 'pages') {

					// Builds list of pages on the SAME level as this page (siblings)
					$res_tmp = $this->getDatabaseConnection()
						->exec_SELECTquery('A.pid AS pid, B.uid AS uid', 'pages A, pages B', 'A.uid=' . intval($uid) . ' AND B.pid=A.pid AND B.deleted=0');

					$pid_tmp = 0;
					while ($row_tmp = $this->getDatabaseConnection()
						->sql_fetch_assoc($res_tmp)) {
						$list_cache[] = $row_tmp['uid'];
						$pid_tmp = $row_tmp['pid'];

						// Add children as well:
						if ($TSConfig['clearCache_pageSiblingChildren']) {
							$res_tmp2 = $this->getDatabaseConnection()
								->exec_SELECTquery('uid', 'pages', 'pid=' . intval($row_tmp['uid']) . ' AND deleted=0');
							while ($row_tmp2 = $this->getDatabaseConnection()
								->sql_fetch_assoc($res_tmp2)) {
								$list_cache[] = $row_tmp2['uid'];
							}
							$this->getDatabaseConnection()
								->sql_free_result($res_tmp2);
						}
					}
					$this->getDatabaseConnection()
						->sql_free_result($res_tmp);

					// Finally, add the parent page as well:
					$list_cache[] = $pid_tmp;

					// Add grand-parent as well:
					if ($TSConfig['clearCache_pageGrandParent']) {
						$res_tmp = $this->getDatabaseConnection()
							->exec_SELECTquery('pid', 'pages', 'uid=' . intval($pid_tmp));
						if ($row_tmp = $this->getDatabaseConnection()
							->sql_fetch_assoc($res_tmp)
						) {
							$list_cache[] = $row_tmp['pid'];
						}
						$this->getDatabaseConnection()
							->sql_free_result($res_tmp);
					}
				} else {
					// For other tables than "pages", delete cache for the records "parent page".
					$list_cache[] = $tscPID;
				}

				// Delete cache for selected pages:
				if (is_array($list_cache)) {
					$ids = $this->getDatabaseConnection()
						->cleanIntArray($list_cache);
					foreach ($ids as $id) {
						$cmd = array('cacheCmd' => $id);
						$this->clearStaticFile($cmd);
					}
				}
			}
		}

		// Clear cache for pages entered in TSconfig:
		if ($TSConfig['clearCacheCmd']) {
			$Commands = GeneralUtility::trimExplode(',', strtolower($TSConfig['clearCacheCmd']), TRUE);
			$Commands = array_unique($Commands);
			foreach ($Commands as $cmdPart) {
				$cmd = array('cacheCmd' => $cmdPart);
				$this->clearStaticFile($cmd);
			}
		}
	}

	/**
	 * Clear static file
	 *
	 * @param    object $_params : array containing 'cacheCmd'
	 *
	 * @return    void
	 */
	public function clearStaticFile(&$_params) {
		if (isset($_params['cacheCmd']) && $_params['cacheCmd']) {
			$cacheCmd = $_params['cacheCmd'];
			switch ($cacheCmd) {
				case 'all':
				case 'pages':
					$directory = '';
					if ((boolean)$this->configuration->get('clearCacheForAllDomains') === FALSE) {
						if (isset($_params['host']) && $_params['host']) {
							$directory = $_params['host'];
						} else {
							$directory = GeneralUtility::getIndpEnv('HTTP_HOST');
						}
					}

					$this->debug('clearing all static cache');
					$this->deleteStaticCache(0, $directory);
					break;
				case 'temp_CACHED':
					// Clear temp files, not frontend cache.
					break;
				default:
					$doClearCache = MathUtility::canBeInterpretedAsInteger($cacheCmd);

					if ($doClearCache) {
						$this->debug('clearing cache for pid: ' . $cacheCmd);
						$this->deleteStaticCache($cacheCmd);
					} else {
						$this->debug('Expected integer on clearing static cache', LOG_WARNING, $cacheCmd);
					}
					break;
			}
		}
	}

	/**
	 * Returns records for a page id
	 *
	 * @param    integer $pid Page id
	 *
	 * @return    array        Array of records
	 */
	public function getRecordForPageID($pid) {

		#DebuggerUtility::var_dump($this->cache->getByTag('page_' . intval($pid)));
		#die();

		return $this->getDatabaseConnection()
			->exec_SELECTgetRows('*', $this->fileTable, 'pid=' . intval($pid));
	}

	/**
	 * Detecting if shift-reload has been clicked. Will not be called if re-
	 * generation of page happens by other reasons (for instance that the page
	 * is not in cache yet!) Also, a backend user MUST be logged in for the
	 * shift-reload to be detected due to DoS-attack-security reasons.
	 *
	 * @param    array                        $params : array containing pObj among other things
	 * @param    TypoScriptFrontendController $parent : The calling parent object
	 *
	 * @return    void
	 */
	public function headerNoCache(array &$params, TypoScriptFrontendController $parent) {
		if (strtolower($_SERVER['HTTP_CACHE_CONTROL']) === 'no-cache' || strtolower($_SERVER['HTTP_PRAGMA']) === 'no-cache') {
			if ($parent->beUserLogin) {
				$this->debug('no-cache header found', LOG_INFO);
				$cmd = array('cacheCmd' => $parent->id);
				$this->clearStaticFile($cmd);
			}
		}
	}

	/**
	 * Check if the SFC should create the cache
	 *
	 * @param    TypoScriptFrontendController $pObj        : The parent object
	 * @param    string                       $timeOutTime : The timestamp when the page times out
	 *
	 * @return    void
	 */
	public function insertPageIncache(TypoScriptFrontendController &$pObj, &$timeOutTime) {
		$isStaticCached = FALSE;
		$this->debug('insertPageIncache');

		// Find host-name / IP, always in lowercase:
		$host = strtolower(GeneralUtility::getIndpEnv('HTTP_HOST'));
		$uri = GeneralUtility::getIndpEnv('REQUEST_URI');
		if ($this->configuration->get('recreateURI')) {
			$uri = $this->recreateURI();
		}
		$uri = urldecode($uri);

		$isHttp = (strpos(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), 'http://') === 0);
		$loginsDeniedCfg = (!$pObj->config['config']['sendCacheHeaders_onlyWhenLoginDeniedInBranch'] || !$pObj->loginAllowedInBranch);
		$staticCacheable = $pObj->isStaticCacheble();

		$fieldValues = array();
		$additionalHash = '';

		// Hook: Initialize variables before starting the processing.
		$initializeVariablesHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_initializeVariables'];
		if (is_array($initializeVariablesHooks)) {
			foreach ($initializeVariablesHooks as $hookFunction) {
				$hookParameters = array(
					'TSFE'            => $pObj,
					'host'            => &$host,
					'uri'             => &$uri,
					'isHttp'          => &$isHttp,
					'fieldValues'     => &$fieldValues,
					'loginDenied'     => &$loginsDeniedCfg,
					'additionalHash'  => &$additionalHash,
					'staticCacheable' => &$staticCacheable,
				);
				GeneralUtility::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}

		// Only process if there are not query arguments, no link to external page (doktype=3) and not called over https:
		if (strpos($uri, '?') === FALSE && $pObj->page['doktype'] != 3 && ($isHttp || $this->configuration->get('enableHttpsCaching'))) {
			// Workspaces have been introduced with TYPO3 4.0.0:
			$version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
			$workspacePreview = ($version >= 4000000 && $pObj->doWorkspacePreview());

			// check the allowed file types
			$basename = basename($uri);
			$fileExtension = pathinfo($basename, PATHINFO_EXTENSION);
			$fileTypes = explode(',', $this->configuration->get('fileTypes'));

			$file = $uri;
			if (empty($fileExtension) || !in_array($fileExtension, $fileTypes)) {
				$file .= '/index.html';
			} else {
				$uri = dirname($uri);
			}

			$file = preg_replace('#//#', '/', $file);

			// This is supposed to have "&& !$pObj->beUserLogin" in there as well
			// This fsck's up the ctrl-shift-reload hack, so I pulled it out.
			if ($pObj->page['tx_ncstaticfilecache_cache'] && (boolean)$this->configuration->get('disableCache') === FALSE && $staticCacheable && !$workspacePreview && $loginsDeniedCfg) {

				// If page has a endtime before the current timeOutTime, use it instead:
				if ($pObj->page['endtime'] > 0 && $pObj->page['endtime'] < $timeOutTime) {
					$timeOutTime = $pObj->page['endtime'];
				}

				// write DB-record and staticCache-files, after DB-record was successful updated or created
				$timeOutSeconds = $timeOutTime - $GLOBALS['EXEC_TIME'];

				$content = $pObj->content;
				if ($this->configuration->get('showGenerationSignature')) {
					$content .= "\n<!-- cached statically on: " . strftime($this->configuration->get('strftime'), $GLOBALS['EXEC_TIME']) . ' -->';
					$content .= "\n<!-- expires on: " . strftime($this->configuration->get('strftime'), $GLOBALS['EXEC_TIME'] + $timeOutSeconds) . ' -->';
				}

				$this->debug('writing cache for pid: ' . $pObj->id);

				// Hook: Process content before writing to static cached file:
				$processContentHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_processContent'];
				if (is_array($processContentHooks)) {
					foreach ($processContentHooks as $hookFunction) {
						$hookParameters = array(
							'TSFE'        => $pObj,
							'content'     => $content,
							'fieldValues' => &$fieldValues,
							'file'        => $file,
							'host'        => $host,
							'uri'         => $uri,
						);
						$content = GeneralUtility::callUserFunction($hookFunction, $hookParameters, $this);
					}
				}

				$recordIsWritten = $this->writeStaticCacheRecord($pObj, $fieldValues, $host, $uri, $file, $additionalHash, $timeOutSeconds, '');
				if ($recordIsWritten === TRUE) {

					// new cache
					$cacheUri = ($isHttp ? 'http://' : 'https://') . $host . $uri;
					$tags = array(
						'page_' . $pObj->page['uid'],
					);
					$this->cache->set($cacheUri, $content, $tags, $timeOutSeconds);
				}
			} else {
				$explanation = '';
				// This is an 'explode' of the function isStaticCacheable()
				if (!$pObj->page['tx_ncstaticfilecache_cache']) {
					$this->debug('insertPageIncache: static cache disabled by user', LOG_INFO);
					$explanation = 'static cache disabled on page';
				}
				if ((boolean)$this->configuration->get('disableCache') === TRUE) {
					$this->debug('insertPageIncache: static cache disabled by TypoScript "tx_ncstaticfilecache.disableCache"', LOG_INFO);
					$explanation = 'static cache disabled by TypoScript';
				}
				if ($pObj->no_cache) {
					$this->debug('insertPageIncache: no_cache setting is true', LOG_INFO);
					$explanation = 'config.no_cache is true';
				}
				if ($pObj->isINTincScript()) {
					$this->debug('insertPageIncache: page has INTincScript', LOG_INFO);

					$INTincScripts = array();
					foreach ($pObj->config['INTincScript'] as $k => $v) {
						$infos = array();
						if (isset($v['type'])) {
							$infos[] = 'type: ' . $v['type'];
						}
						if (isset($v['conf']['userFunc'])) {
							$infos[] = 'userFunc: ' . $v['conf']['userFunc'];
						}
						if (isset($v['conf']['includeLibs'])) {
							$infos[] = 'includeLibs: ' . $v['conf']['includeLibs'];
						}
						if (isset($v['conf']['extensionName'])) {
							$infos[] = 'extensionName: ' . $v['conf']['extensionName'];
						}
						if (isset($v['conf']['pluginName'])) {
							$infos[] = 'pluginName: ' . $v['conf']['pluginName'];
						}

						$INTincScripts[] = implode(',', $infos);
					}
					$explanation = 'page has INTincScript: <ul><li>' . implode('</li><li>', $INTincScripts) . '</li></ul>';
					unset($INTincScripts);
				}
				if ($pObj->isUserOrGroupSet() && $this->isDebugEnabled) {
					$this->debug('insertPageIncache: page has user or group set', LOG_INFO);
					// This is actually ok, we do not need to create cache nor an entry in the files table
					//$explanation = "page has user or group set";
				}
				if ($workspacePreview) {
					$this->debug('insertPageIncache: workspace preview', LOG_INFO);
					$explanation = 'workspace preview';
				}
				if (!$loginsDeniedCfg) {
					$this->debug('insertPageIncache: loginsDeniedCfg is true', LOG_INFO);
					$explanation = 'loginsDeniedCfg is true';
				}

				// new cache
				$cacheUri = ($isHttp ? 'http://' : 'https://') . $host . $uri;
				$tags = array(
					'page_' . $pObj->page['uid'],
					'explanation'
				);
				$this->cache->set($cacheUri, $explanation, $tags, 0);

				$this->writeStaticCacheRecord($pObj, $fieldValues, $host, $uri, $file, $additionalHash, 0, $explanation);
				$this->debug('insertPageIncache: ... this page is not cached!', LOG_INFO);
			}
		}

		// Hook: Post process (no matter whether content was cached statically)
		$postProcessHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['insertPageIncache_postProcess'];
		if (is_array($postProcessHooks)) {
			foreach ($postProcessHooks as $hookFunction) {
				$hookParameters = array(
					'TSFE'           => $pObj,
					'host'           => $host,
					'uri'            => $uri,
					'isStaticCached' => $isStaticCached,
				);
				GeneralUtility::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}
	}

	/**
	 * Log cache miss if no_cache is true
	 *
	 * @param    array  $params : Parameters delivered by the calling object
	 * @param    object $parent : The calling parent object
	 *
	 * @return    void
	 */
	public function logNoCache(&$params, $parent) {
		if ($params['pObj']) {
			if ($params['pObj']->no_cache) {
				$timeOutTime = 0;
				$this->insertPageInCache($params['pObj'], $timeOutTime);
			}
		}
	}

	/**
	 * Remove expired pages. Call from cli script.
	 *
	 * @param CommandLineController $parent : The calling parent object
	 *
	 * @return    void
	 */
	public function removeExpiredPages(CommandLineController $parent = NULL) {
		$clearedPages = array();

		$rows = $this->getDatabaseConnection()
			->exec_SELECTgetRows('file, host, pid, (' . $GLOBALS['EXEC_TIME'] . ' - crdate - cache_timeout) as seconds', $this->fileTable, '(cache_timeout + crdate) <= ' . $GLOBALS['EXEC_TIME'] . ' AND crdate > 0');

		if ($rows) {
			/* @var $tce \TYPO3\CMS\Core\DataHandling\DataHandler */
			$tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
			$tce->start(array(), array());

			foreach ($rows as $row) {
				$pageId = $row['pid'];

				if (isset($parent)) {
					$parent->cli_echo("Removed pid: " . $pageId . "\t" . $row['host'] . $row['file'] . ", expired by " . $row['seconds'] . " seconds.\n");
				}

				// Check whether page was already cleared:
				if (!isset($clearedPages[$pageId])) {
					$tce->clear_cacheCmd($pageId);
					$clearedPages[$pageId] = TRUE;
				}
			}
		} elseif (isset($parent)) {
			$parent->cli_echo("No expired pages found.\n");
		}
	}

	/**
	 * Set a cookie if a user logs in or refresh it
	 *
	 * This function is needed because TYPO3 always sets the fe_typo_user cookie,
	 * even if the user never logs in. We want to be able to check against logged
	 * in frontend users from mod_rewrite. So we need to set our own cookie (when
	 * a user actually logs in).
	 *
	 * Checking code taken from class.t3lib_userauth.php
	 *
	 * @param    object $params : parameter array
	 * @param    object $pObj   : partent object
	 *
	 * @return    void
	 */
	public function setFeUserCookie(&$params, &$pObj) {
		$cookieDomain = NULL;
		// Setting cookies
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain']) {
			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain']{0} == '/') {
				$matchCnt = @preg_match($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'], GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'), $match);
				if ($matchCnt === FALSE) {
					GeneralUtility::sysLog('The regular expression of $GLOBALS[TYPO3_CONF_VARS][SYS][cookieDomain] contains errors. The session is not shared across sub-domains.', 'Core', 3);
				} elseif ($matchCnt) {
					$cookieDomain = $match[0];
				}
			} else {
				$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
			}
		}

		// If new session and the cookie is a sessioncookie, we need to set it only once!
		if (($pObj->fe_user->loginSessionStarted || $pObj->fe_user->forceSetCookie) && $pObj->fe_user->lifetime == 0) { // isSetSessionCookie()
			if (!$pObj->fe_user->dontSetCookie) {
				if ($cookieDomain) {
					SetCookie($this->extKey, 'fe_typo_user_logged_in', 0, '/', $cookieDomain);
				} else {
					SetCookie($this->extKey, 'fe_typo_user_logged_in', 0, '/');
				}
			}
		}

		// If it is NOT a session-cookie, we need to refresh it.
		if ($pObj->fe_user->lifetime > 0) { // isRefreshTimeBasedCookie()
			if ($pObj->fe_user->loginSessionStarted || isset($_COOKIE[$this->extKey])) {
				if (!$pObj->fe_user->dontSetCookie) {
					if ($cookieDomain) {
						SetCookie($this->extKey, 'fe_typo_user_logged_in', time() + $pObj->fe_user->lifetime, '/', $cookieDomain);
					} else {
						SetCookie($this->extKey, 'fe_typo_user_logged_in', time() + $pObj->fe_user->lifetime, '/');
					}
				}
			}
		}
	}

	/**
	 * Puts a message to the devlog.
	 *
	 * @param string $message  The message to log
	 * @param int    $severity The severity value from warning to fatal error (default: 1)
	 * @param bool   $additionalData
	 *
	 * @return    void
	 */
	protected function debug($message, $severity = LOG_NOTICE, $additionalData = FALSE) {
		if ($this->configuration->get('debug') || $severity <= LOG_CRIT) {

			// map PHP or nc_staticfilecache error levels to
			// GeneralUtility::devLog() severity level
			$arMapping = array(
				LOG_EMERG   => 3,
				LOG_ALERT   => 3,
				LOG_CRIT    => 3,
				LOG_ERR     => 3,
				LOG_WARNING => 2,
				LOG_NOTICE  => 1,
				LOG_INFO    => -1,
				LOG_DEBUG   => 0,
			);

			GeneralUtility::devlog(trim($message), $this->extKey, isset($arMapping[$severity]) ? $arMapping[$severity] : 1, $additionalData);
		}
	}

	/**
	 * Recreates the URI of the current request.
	 *
	 * Especially in simulateStaticDocument context, the different URIs lead to the same result
	 * and static file caching would store the wrong URI that was used in the first request to
	 * the website (e.g. "TheGoodURI.13.0.html" is as well accepted as "TheFakeURI.13.0.html")
	 *
	 * @return    string        The recreated URI of the current request
	 */
	protected function recreateURI() {
		$objectManager = new ObjectManager();
		/** @var UriBuilder $uriBuilder */
		$uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
		return $uriBuilder->reset()
			->setAddQueryString(TRUE)
			->build();
	}

	/**
	 * Deletes the static cache in database and filesystem.
	 *
	 * @param    integer $pid       : (optional) Id of the page perform this action
	 * @param    string  $directory : (optional) The directory to use on deletion
	 *                              below the static file directory
	 *
	 * @return    void
	 */
	protected function deleteStaticCache($pid = 0, $directory = '') {
		$pid = intval($pid);

		if ($pid > 0) {

			$cacheEntries = array_keys($this->cache->getByTag('page_' . $pid));
			foreach ($cacheEntries as $cacheEntry) {
				$this->cache->remove($cacheEntry);
			}

			// @deprecated Cache of a single page shall be removed
			$rows = $this->getDatabaseConnection()
				->exec_SELECTgetRows('*', $this->fileTable, 'pid=' . $pid);
			foreach ($rows as $row) {
				$this->getDatabaseConnection()
					->exec_DELETEquery($this->fileTable, 'uid=' . $row['uid']);
			}
			return;
		}

		// Cache of all pages shall be removed (clearCacheCmd "all" or "pages")
		try {

			$this->cache->flush();

			// @deprecated (Remove old cache information table)
			$this->getDatabaseConnection()
				->exec_TRUNCATEquery($this->fileTable);
		} catch (\Exception $e) {
			$this->debug($e->getMessage(), LOG_CRIT);
		}
	}

	/**
	 * Gets the name of the database table holding all cached files.
	 *
	 * @return    string        Name of the database holding all cached files
	 */
	public function getFileTable() {
		return $this->fileTable;
	}

	/**
	 *    Check for existing entries with the same uid and file, if a record exists, update timestamp, otherwise create a new record.
	 *
	 * @param object  $pObj
	 * @param array   $fieldValues
	 * @param string  $host
	 * @param string  $uri
	 * @param string  $file
	 * @param string  $additionalHash
	 * @param integer $timeOutSeconds
	 * @param string  $explanation
	 *
	 * @return boolean
	 */
	private function writeStaticCacheRecord($pObj, array $fieldValues, $host, $uri, $file, $additionalHash, $timeOutSeconds, $explanation) {
		$databaseConnection = $this->getDatabaseConnection();
		$rows = $databaseConnection->exec_SELECTgetRows('uid', $this->fileTable, 'pid=' . $pObj->page['uid'] . ' AND host = ' . $databaseConnection->fullQuoteStr($host, $this->fileTable) . ' AND file=' . $databaseConnection->fullQuoteStr($file, $this->fileTable) . ' AND additionalhash=' . $databaseConnection->fullQuoteStr($additionalHash, $this->fileTable));

		// update DB-record
		if (is_array($rows) === TRUE && count($rows) > 0) {
			$fieldValues['tstamp'] = $GLOBALS['EXEC_TIME'];
			$fieldValues['cache_timeout'] = $timeOutSeconds;
			$fieldValues['explanation'] = $explanation;
			$fieldValues['ismarkedtodelete'] = 0;
			$result = $databaseConnection->exec_UPDATEquery($this->fileTable, 'uid=' . $rows[0]['uid'], $fieldValues);
			if ($result === TRUE) {
				return TRUE;
			}
		}

		// create DB-record
		$fieldValues = array_merge($fieldValues, array(
			'crdate'         => $GLOBALS['EXEC_TIME'],
			'tstamp'         => $GLOBALS['EXEC_TIME'],
			'cache_timeout'  => $timeOutSeconds,
			'explanation'    => $explanation,
			'file'           => $file,
			'pid'            => $pObj->page['uid'],
			'reg1'           => $pObj->page_cache_reg1,
			'host'           => $host,
			'uri'            => $uri,
			'additionalhash' => $additionalHash,
		));
		return $databaseConnection->exec_INSERTquery($this->fileTable, $fieldValues);
	}

	/**
	 * Get database connection
	 *
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
