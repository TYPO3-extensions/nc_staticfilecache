ChangeLog
^^^^^^^^^

2015-04-17 Tim Lochmüller <tim@fruit-lab.de>

- Fix #6648 The explanation database table field should be emptied while an update

2015-04-16  Tim Lochmüller <tim@fruit-lab.de>

- [!!!] Create a command controller and mark the CLI and Tasks class as deprecated. The classes are removed in a few versions
- Move the crawler hook to the new (namespace based) location
- Move the Info module to the new (namespace based) location
- Migrate the L10N file in the root folder to the new L10N file in Resources/Private/Language/locallang.xml
- Change extension icon from gif to png
- Move the main class to the new (namespace based) location
- Remove the old CLI and the old tasks, because the scheduler do not break directly. Please create the tasks as extbase command controller tasks and change your env
- Move ChangeLog to new ReSt documentation
- Move Readme to new ReSt documentation
- Fix #58178 Handling of non-ASCII URIs
- Fix #65700 Static File Cache module not working correctly on latest TYPO3 7.1
- Increase compatibility to 6.2.x - 7.1.x
- Fix #64769 Enable HTTPS caching impossible
- Release of v3.0.0 to TER

2015-04-15  Tim Lochmüller <tim@fruit-lab.de>

- Remove closing PHP Tags (CGL)
- Format the PHP classes (CGL)
- Remove old XCLASS code lines in the footer of the classes
- Remove $GLOBALS['TYPO3_DB'] to helper method
- Fix PhpDoc
- import namespace classes
- handle GeneralUtility::mkdir_deep in the right way
- fix undefined variable notice

2015-01-12  Jürgen Kußmann <juergen.kussmann@aoe.com>

- Add/Update dependency to TYPO3 6.2 and PHP 5.3
- Use TYPO3-CORE-classes with namespaces

2014-11-11  Klaus Bitto <klaus@netcreators.nl>

- Release of v2.5.2 to TER.

2014-11-01  Tim Lochmueller  <tim@fruit-lab.de>

- Fix #61980: Use M code for ExpireByType instead of A (https://forge.typo3.org/issues/61980)
- Fix #62538: URLs ending with "/" not handled properly (https://forge.typo3.org/issues/62538)

2014-04-10  Klaus Bitto <klaus@netcreators.nl>

- Release of v2.5.1 to TER.

2014-03-26  Tim Lochmueller  <tim@fruit-lab.de>

- Remove global statements and use $GLOBAL
- Smarter explanation for plugins like ExtBase plugins, if the page is not cacheable. More details about extensionName, pluginName...

2014-03-25  Tim Lochmueller  <tim@fruit-lab.de>

- Wrong severity in call to t3lib_div::devLog()
- Wrap removed function t3lib_div::intval_positive in the Scheduler task to support 6.x
- Fix old PHP4 constructor call in the cleaner task
- Backport changes from TER version to trunk
- Set dependencies to TYPO3 4.5 minimum
- Wrap testInt once more, to prefer the TYPO3 CMS 4.7 t3lib_utility_Math function
- Cleanup and add same classes to the ext_autoload.php
- Remove PATH_t3lib usage to fit 6.x

2014-03-11  Klaus Bitto  <klaus@netcreators.nl>

- Fixed bug: Call to undefined method TYPO3\CMS\Core\Utility\GeneralUtility::intval_positive() in class.tx_ncstaticfilecache_tasks_processDirtyPages_AdditionalFieldProvider.php (Thank you, Hendrik Reimers)
- Increased compatibility to TYPO3 6.2.

2014-03-07  Klaus Bitto  <klaus@netcreators.nl>

- Integrated TYPO3 6 compatibility adjustments for TER release as v2.4.0. (Selective merge from https://svn.typo3.org/TYPO3v4/Extensions/nc_staticfilecache/trunk/.)

2010-10-13  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Database elements are removed if directory on the filesystem exists, but could not be accessed

2010-09-20  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Removing of static files returned wrong boolean value
- Fixed bug #9850: Small coding errors (thanks to Axel Jung)
- Raised version to 2.3.3

2010-09-08  Oliver Hader  <oliver@typo3.org>

- Cleanup: Fixed svn:eol-style of PHP and text files
- Fixed bug: Only remove database elements if removal in filesystem was successful
- Added feature: Integrate logging to devLog if clearing static caches failes
- Raised version to 2.3.2

2010-07-19  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Typing error in hook name
- Fixed bug: Infomodule shows creation time instead of last modification

2010-07-15  Oliver Hader  <oliver@typo3.org>

- Cleanup: Fixed naming and formatting
- Cleanup: Removed superfluous hook in processDirtyPages() method that was only available in Trunk

2010-07-14  Franz Ripfel  <franz.ripfel@abezet.de>

- Fixed bug: Clearing cache of a single page deleted also all folders and files of subpages

2010-07-13  Oliver Hader  <oliver@typo3.org>

- Fixed bug: TYPO3 cache gets cleared on removing expired pages with the markDirtyInsteadOfDeletion setting enabled (thanks to Juergen Kussmann)

2010-05-28  Oliver Hader  <oliver@typo3.org>

- Added feature: Integrate possibility to disable the clear cache post processing on deman during runtime

2010-05-27  Oliver Hader  <oliver@typo3.org>

- Fixed bug: markDirtyInsteadOfDeletion property shall only consider specific pages - thus not clear all or pages cache
- Cleanup: Fixed formatting and inline type hints
- Fixed bug: Database element is not removed if clearing files did not succeed
- Fixed bug: Pages with an endtime that would expire a page before the general expiration time is not considered
- Fixed bug: Additional hash is not written for database elements
- Fixed bug: Additional hash is not considered for lookups when empty

2010-05-25  Oliver Hader  <oliver@typo3.org>

- Added feature: Integrate hook to post process the cache scenario after (no matter whether static cache was written)

2010-04-30  Oliver Hader  <oliver@typo3.org>

- Added feature: Integrate hook to handle deleting a static cached directory

2010-04-19  Oliver Hader  <oliver@typo3.org>

- Follow-up to bug #5290: Expect the scheme name at first position and allow to modifiy with hook

2010-04-15  Oliver Hader  <oliver@typo3.org>

- Follow-up to feature of predefining/extending values that are stored in the database
- Added feature: Add additionalhash to implement individual and more specific database elements (utilized by hooks)
- Fixed bug: removeExpiredPages triggeres clearing cache of a page multiple times

2010-04-14  Oliver Hader  <oliver@typo3.org>

- Cleanup: Moved logging part of writing cache files to accordant place
- Cleanup: Moved information that determine whether a page is cachable and added to them to hook parameters
- Cleanup: Moved implementation to write compressed content to separate method
- Cleanup: Extended parameters of createFile_processContent hook by URI and hostname
- Cleanup: Renamed internal variable name
- Added feature: Add possibility to predefine/extend values that are stored in the database

2010-02-22  Michiel Roos  <michiel@netcreators.com>

- Updated the manual

2010-02-20  Michiel Roos  <michiel@netcreators.com>

- Feature #3286: Enable usage of value 'reg1' from cache pages (Thanks to Alienor.net)

2010-02-19  Michiel Roos  <michiel@netcreators.com>

- Feature #4179: Create gzipped versions of cache files (Thanks to Steffen Gebert)
- Fixed bug #5290: nc_staticfilecache caches contents of https pages! (Thanks to Stefan Galinski)
- Fixed bug #6525: EM refers to cc_devlog (Thanks to Steffen Gebert)

2010-02-17  Michiel Roos  <michiel@netcreators.com>

- Fixed bug #6504: port based installations doesn't work (Thanks to Stefan Galinski)

2010-01-30  Michiel Roos  <michiel@netcreators.com>

- Change: Show original URI on hover in infomodule

2010-01-22  Michiel Roos  <michiel@netcreators.com>

- Fixed bug #6158: Scheduler tasks: missing ext_autoload (Thanks to Peter Schuster)

2010-01-14  Michiel Roos  <michiel@netcreators.com>

- Fixed bug #4715: List what element are of INT type. (Thanks to Mads Jensen)
- Added feature #6026: Provide scheduler tasks (Thanks to Michael Klapper)

2010-01-14  Oliver Hader  <oliver@typo3.org>

- Fixed bug: tx_ncstaticfilecache::processDirtyPages() removes entries from diry queue even if the processing did not succeed

2009-08-31  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Method tx_ncstaticfilecache::deleteStaticCacheDirectory() is protected but should be public

2009-08-13  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Current page Id is not outputted in form of backend info module

2009-08-10  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Visualization of tree in backend info module
- Fixed bug: Visaulization does not depend on selected page of real page branch in backend info module
- Fixed bug: Markup is incorrect on rendering the table in the backend info module
- Fixed bug: Expanding/collapsing did not stay at the selected page in the backend info module

2009-08-07  Oliver Hader  <oliver@typo3.org>

- Added feature: Integrate possibility to disable static caching for a page branch (tx_ncstaticfilecache.disableCache)

2009-07-21  Oliver Hader  <oliver@typo3.org>

- Added feature: New hook 'createFile_initializeVariables' to initialize variabled before starting the processing

2009-06-30  Oliver Hader  <oliver@typo3.org>

- Fixed bug: CLI debug output in processDirtyPages() does not contain directory name
- Fixed bug: Clearing cached pages (clear_cacheCmd=pages) does not trigger clearing static cache
- Cleanup: Added methods to determine extension configuration and select specific properties
- Fixed bug: Processing of dirty pages is shown in info module even if using the dirty flag is not enabled
- Fixed bug: If necessary, the root of the cache directory should be deleted first

2009-06-23  Daniel Poetzinger  <dev@aoemedia.de>

- Added feature: Integrate processing instruction for crawler extension

2009-06-23  Oliver Hader  <oliver@typo3.org>

- Cleanup: Added method to be used on delegating actions to the static cache data manipulation object
- Cleanup: Fixed ChangeLog and formatting of processing instruction for crawler extension

2009-06-22  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Information whether page is marked dirty is missing in info module
- Added feature: Integrate possibility to remove all expired pages in the info module
- Added feature: Moved rendering of rows in info module to own method to be overridable by XCLASSes
- Cleanup: Refactored clean dirty pages parts
- Cleanup: Added method to determine the table name used to store cache information
- Added feature: Integrate possibility to process all dirty pages in the info module

2009-06-12  Oliver Hader  <oliver@typo3.org>

- Fixed bug: Info module does not show pages with a dokType above 199

2009-05-08  Oliver Hader  <oliver@typo3.org>

!!!- Added feature: Changed database table to use InnoDB engine
- Follow-up to feature #2598: Added missing 'isdirty' field to SQL definitions
- Follow-up to feature #2598: Added new CLI task 'processDirtyPages' to process elements marked as dirty
- Follow-up to feature #2598: Set 'isdirty' flag zero when database element gets updated

2009-05-07  Oliver Hader  <oliver@typo3.org>

- Cleanup: Changed formatting of class tx_ncstaticfilecache and SQL file (non-functional changes)
- Set version to 2.4.0-dev
- Set version to 3.0.0-dev
- Cleanup: Added protected/public definitions and set min. requirement to TYPO3 4.2.0
- Cleanup: Removed superfluous class for debug output and integrated it to regular class
- Cleanup: Removed CLI cleaner for elderly TYPO3 releases (< 4.1)
- Fixed bug: Fixed some hanging record sets
- Fixed bug: Info module does not work anymore due to calls to protected methods/variables
- Added feature: Store original URI of request and possibility to recreate the URI by typoLink
- Added feature #2598: Keep static cache files even if the cache gets flushed by TYPO3
- Fixed bug: Show generation signature only when the request is served by static cache
- Added feature: New hook 'createFile_processContent' to modify content before being written to cached file

2008-02-22  Michiel Roos  <michiel@netcreators.com>

- Added Changelog ;-)
- Removed version_compare() from insertPageIncache()
- Rename modfunc1 to infomodule