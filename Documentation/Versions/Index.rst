.. include:: ../Includes.txt


.. _versions:

Versions
========

.. toctree::
        :maxdepth: 2

    Index


2.0.0
-----

- Change package name and namespace to **t3/dce** and `\T3\Dce`
- Added direct_output mode
- Fixed behaviour of detail pages
- Global namespace registration for `dce:` in Fluid templates
- Allow partials and layouts fallback
- Add `{$variable}` to field configuration (used for FAL)
- Add csh-description for all fields
- Add complete documentation
- Refactored user conditions (Symfony expression language)
- Many fixes and improvements

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
- Several bugfixes
    - Permission issue for non-admins fixed
    - Removed php warnings in backend
    - Fixed Typolink 8.7 code snippet
