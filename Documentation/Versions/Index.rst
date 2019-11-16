.. include:: ../Includes.txt


.. _versions:

Versions
========

.. contents:: :local:

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
