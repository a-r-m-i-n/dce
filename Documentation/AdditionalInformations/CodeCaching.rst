.. _additional-informations-code-caching:


Code Caching
------------

DCE generates PHP code and XML for new content elements during TYPO3's bootstrapping. To decrease database queries
during this process, DCE 2.2 introduced an own small CacheManager.

The code cache for DCEs is always enabled.

This cache contains generated DCE registration, TCA and FlexForm code. It is independent of the per-DCE
"Cache DCE frontend plugin" option, which controls whether the frontend controller action is cacheable.

.. caution::
   Any changes made to a DCE or a DCE field, require to clear TYPO3's system cache. Otherwise changes are not visible
   in backend or frontend.


Why DCE ships its own Cache Manager?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In DCE 2.1 the TYPO3's core cache manager has been used to cache DCE code. But TYPO3 does not allow to use its
Cache Manager during bootstrapping (limbo mode) in TYPO3 10 anymore. Therefore DCE provides it's own cache manager.

The shipped cache manager stores generated files below ``var/cache/code/dce``.

When clearing TYPO3's system caches in the backend or with the ``cache:flush`` CLI command, the DCE code cache is
rebuilt automatically.

When warming an empty cache from the command line, run ``cache:flush --group system`` before
``cache:warmup --group system`` so the DCE code cache exists before TYPO3 builds its system caches.
