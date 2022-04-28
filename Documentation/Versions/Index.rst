.. include:: ../Includes.txt


.. _versions:

Versions
========

.. contents:: :local:

2.8.4
-----

- [BUGFIX] Remove replace for "typo3-ter/dce" in composer.json (Daniel Haupt)
- [BUGFIX] Prevent undefined array key warning in AfterSaveHook (creationell)


2.8.3
-----

- [BUGFIX] Add missing slash when resolving t3:// links
- [TASK] Simplify ext_emconf


2.8.2
-----

- [DEPRECATION] Mark "file:" and "t3://" as template path deprecated
- [BUGFIX] Do not use deprecated method in FileInfoViewHelper
- [BUGFIX] Prepend Environment::getPublicPath() to resolved t3://file links
- [BUGFIX] Do not apply langauge overlay, when in free mode
- [BUGFIX] TCA output flexform (thanks to Sebastian Iffland)


2.8.1
-----

**10th Anniversary of the DCE extension for TYPO3** 🎉 2012-2022

- [TASK] Update copyright
- [TASK] Prevent exception in backend module, when a DCE has no custom icon set
- [TASK] Remove file picker wizard (inputLink) for DCE template paths
- [BUGFIX] Apply language overlay to translated container elements
- [BUGFIX] Remove unused extension configuration in AfterSaveHook
- [BUGFIX] Fix PHP warning, when accessing non existing array key (PHP 8 support)


2.8.0
-----

This version has been sponsored by `web-crossing GmbH`_.

.. _web-crossing GmbH: https://www.web-crossing.com/

- [FEATURE] Support DCE container within EXT:news detail view
- [DOCS] Remove invalid links to forge.typo3.org
- [BUGFIX] Remove unnecessary extend of AbstractCondition


2.7.7
-----

- [TASK] Replace ResourceFactory::getInstance() calls
- [BUGFIX] Use DBAL expressions in update wizards
- [BUGFIX] Use ObjectManager when creating new Extbase repository instance
- [BUGFIX] Fix escaping of WrapWithCurlyBracesViewHelper


2.7.6
-----

- [BUGFIX] Fix DceCodeMirrorFieldRenderType declaration


2.7.5
-----

- [BUGFIX] Fix accidentally added namespace import when creating DCE cache
- [TASK] Use DceRepository instance for extracting uid from CType


2.7.4
-----

- [BUGFIX] Fix usage of iconGroups in tt_content's CType configuration
- [DEVOPS] Update php-cs-fixer and phpstan
- [CLEANUP] Indention fixes
- [TASK] Make DCE's CacheManager non-singleton
- [TASK] Use correct file permissions when creating cache folder


2.7.3
-----

- [BUGFIX] Remove usage of $_EXTKEY
- [TASK] Cleanup ext_tables.sql
- [TASK] Remove "starttime" and "endtime" from DCE's TCA
- [DOC] Improve README
- [DOC] Add Gridelements notice (resolveChildFlexFormData)


2.7.2
-----

- [DEVOPS] Fixing code style issues
- [BUGFIX] Do not perform file_exists after unlinking (deleting) cache file
- [BUGFIX] Do not clear DCE caches everytime any TYPO3 cache gets cleared
- [BUGFIX] Fix typehinting for updateTcaMappingsAction in DceModuleController


2.7.1
-----

- Security related bugfix release


2.7.0
-----
- [BUGFIX] Remove unused language fields and fix copying/duplicating DCEs on root level
- [FEATURE] Symfony expressions for Simple Backend View header
- [BUGFIX] Fix non-rendered container items, when content elements get rendered multiple times
- [BUGFIX] Update outdated color picker config
- [DEVOPS] Update DDEV environment and add project environment with Composer
- [TASK] Add required "extra.typo3/cms.extension-key" value
- [FEATURE] TYPO3 11 Compatibility (dropped TYPO3 8 support)


2.6.2
-----

- Security related bugfix release


2.6.1
-----
- [BUGFIX] Add missing "classmap" argument in ext_emconf autoload section
- [FEATURE] Add support for EXT:container
- [BUGFIX] Only add FrontendRestrictionContainer when TYPO3_MODE equals "FE"


2.6.0
-----
- [TASK] Update module icons and header
- [TASK] Extend .gitignore
- [DEVOPS] Apply fix for DDEV 1.15
- [FEATURE] Add DceViewHelper
- [FEATURE] Add dedicated "detailpage_title_expression" field for detailpage title generation
- [TASK] Code style
- [TASK] Show "Detail page" as badge in DCE module, when enabled
- [BUGFIX] Do not show new slug settings in TYPO3 8
- [TASK] Add documentation for new slug features
- [FEATURE] Add "use slug as title" DCE option and provide new PageTitleProvider
- [FEATURE] Slugs for detail pages
- [BUGFIX] Register old "PageLayoutViewDrawItemHook" when EXT:gridelements is active

Thanks to **Silverback** (https://silverback.st/) for sponsoring this release!


2.5.2
-----
- [BUGFIX] Use old PageLayoutViewDrawItemHook when "fluidBasedPageModule" is disabled in TYPO3 10
- [TASK] Apply FrontendRestrictionContainer to QueryBuilder in ContainerFactory
- [BUGFIX] Fix DCE container when content-fallback takes effect
- [BUGFIX] Remove deprecated $string{0} syntax from DceRepository


2.5.1
-----
- [TASK] Improve ContainerFactory
- [DEVOPS] Add DDEV Environment
- [BUGFIX] Do not use Extbase Controller Action to return DCE Instance
- [BUGFIX] Add mandatory renderType
- [BUGFIX] Change configuration position fpr palettes and language-path
- [TASK] Register LinkAnalyserSlot as EventListener in TYPO3 10
- [TASK] Use new PreviewRenderer in v10 for backend templates


2.5.0
-----
- [TASK] Improve MigrateDceFieldDatabaseRelationTrait
- [BUGFIX] Fix exception when using Postgtres
- [TASK] Small documentation improvements
- [BUGFIX] Keep page cache-able, when using DCE containers, in TYPO3 8
- [BUGFIX] Fix multiple entries of the same category in {contentObject.categories}
- [TASK] Code style fixes
- [DEVOPS] Fix broken code quality check pipeline
- [BUGFIX] Prevent Linkanalyzer to check non DCE records
- [BUGFIX] Make CacheManager respect fileCreateMask
- [TASK] Refactor "File Abstraction Layer" code snippets
- [BUGFIX] Codemirror fix for TYPO3 10
- [BUGFIX] Available templates switcher cuts of .xml instead of wrong .xlf
- [BUGFIX] Only apply "Prevent header copy suffix" when status is "new" in after save hook
- [TASK] Use new UpdateWizards also in TYPO3 9
- [BUGFIX] Fix instantiating of ObjectManager
- [TASK] Provide UpdateWizards, based on old Updates


2.4.1
-----
- [BUGFIX] Fix wrong version constraints


2.4.0
-----
- [FEATURE] TYPO3 10 Compatibility


2.3.1
-----
- [BUGFIX][!!!] Fix queries which build content elements
- [BUGFIX] Do not throw exceptions in Content Element Generator


2.3.0
-----
- [FEATURE] Add new option "container_detail_autohide"
- [BUGFIX] Fix Doctrine DBAL queries with empty field where clauses


2.2.1
-----

- [BUGFIX] Fix wrong escaping of quotes, in OutputPlugin Generator
- [TASK] Improve error message, when a DCE Field has a mapping to a non-existing tt_content column
- [TASK] Use Doctrine API to get list of table and column names
- [TASK] Make DCE work, even without fluid_styled_content installed
- [TASK] Add hint to documentation, that tab DCE fields changes the FlexForm structure
- [TASK] Improve README.md
- [BUGFIX] Fix hidden DCE container items


2.2.0
-----

- [FEATURE] New "Prevent header copy suffix" DCE option
- [BUGFIX] Fix deprecated clear-cache call
- [BUGFIX] Do not use "module-help" icon in DCE backend module
- [FEATURE][!!!] Remove old TYPO3_DB calls with Doctrine DBAL
- [FEATURE] Make mapped "tx_dce_index" contents searchable in backend
- [TASK] Improve and document Code Caching feature
- [BUGFIX] Use heredoc for generated FlexForm XML
- [BUGFIX] Fix missing FQCN in generated PHP code
- [FEATURE] Implement own CacheManager


2.1.0
-----
- [TASK] Use native database connection, when existing
- [FEATURE][!!!] Re-implement Caching of generated PHP code
- [TASK] Improve code snippets
- [BUGFIX][!!!] Make access field values of child DCEs work in TYPO3 9 (before: ``{dce.fieldName}``, now: ``{dce.get.fieldName}``)
- [BUGFIX] Fix localizedUid conditions in FalViewHelper


2.0.6
-----
- [BUGFIX] Do not throw exception in backend when proper flexform missing
- [FEATURE] Improve tx_dce_dce behaviour
- [BUGFIX] Remove unused code which causes errors


2.0.5
-----
- [BUGFIX] Allow lowercase only for DCE identifier
- [BUGFIX] Include tx_gridelements_columns in db query (thanks to Matthias Bernad)


2.0.4
-----
- [BUGFIX] Do not display "Edit DCE" button for all content elements


2.0.3
-----
- [BUGFIX] Add default value for tx_dce_dce column in tt_content table
- **[BUGFIX][!!!] Do not use hidden DCE fields**


2.0.2
-----

- [TASK] Add "Upgrading DCE" to documentation
- [BUGFIX] Allow null value for input in LanguageService::sL
- [BUGFIX] Fix resolving of non-dce tt_content records


2.0.1
-----

- [BUGFIX] Check for correct table when resolving related records


2.0.0
-----

- Change package name and namespace to **t3/dce** and ``\T3\Dce``
- Add identifier to DCE, which allows to control the CType of the content element
- Added direct_output mode
- Fixed behaviour of detail pages
- Global namespace registration for ``dce:`` in Fluid templates
- Allow partials and layouts fallback
- Add ``{$variable}`` to field configuration (used for FAL)
- Add csh-description for all fields
- Refactored user conditions (Symfony expression language)
- Removed update check functionality
- Removed all extension manager settings
- Refactored and cleaned up code base
- New FlexForm rendering (using DomDocument instead of FluidTemplate)
- DCE is **100% deprecation notice free** in TYPO3 9.5 LTS
- Add complete documentation (!)


1.6.0
-----

- Added TYPO3 9.5 LTS and dropped TYPO3 7.6 LTS support


1.5.x
-----

- Completely refactored code injection (for TCA).

  Instead of requiring a dynamic generated php file, the code is
  dynamically executed, at the points where TYPO3 expects it.

  So all cache clear functionality and options has been removed.

  If you want to make new DCEs visible you need to clear the system
  cache or disable the cache at all, like you need to do when modifying
  the TCA manually.

  DCE should behave exactly the same like before, just without need of
  cache files in typo3temp.

- Major bugfixes and improvements (DceContainer & SimpleBackendView)
- Removed f:layout in DCE templates, by default
- Applied refactorings


1.4.x
-----

- DCE major release with many new features and improvements
- One breaking change, regarding backend templates.
- Check out release slides for all new stuff: http://bit.ly/2SFbIzC
- Bug and compatibility fixes for TYPO3 8.6
- More fixes for TYPO3 7/8 compatibility in backend.
- Bug and compatibility fixes for TYPO3 8
- Also improved dev-ops tools.
- The typolink view helpers in DCE are marked as deprecated. Please use f:link.typolink or f:uri.typolink instead.
- Compatibility fixes for TYPO3 7.6 and 8.7 LTS. Also updated RTE code snippets.
- Fixed Typolink view helper for TYPO3 7.6 LTS and added conditional code snippets (for 7.6 and 8.7 snippets) in
  DCE configuration.
- Fixes big performance issue in backend and increases compatibility to EXT:flux.
- Massive refactorings, to improve speed of DCE extension. Special thanks to Bernhard Kraft!
- Small bugfix and add example to code snippets of RTE, of how to define a preset for CKeditor.
- Permission issue for non-admins fixed
- Removed php warnings in backend
- Fixed Typolink 8.7 code snippet
