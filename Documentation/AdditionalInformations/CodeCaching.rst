.. include:: ../Includes.txt


.. _additional-informations-code-caching:


Code Caching
------------

DCE generates PHP code and XML for new content elements during TYPO3's bootstrapping. To decrease database queries
during this process, DCE 2.2 introduced an own small CacheManager.

The code cache for DCEs is always enabled.

.. caution::
   Any changes made to a DCE or a DCE field, require to clear TYPO3's system cache. Otherwise changes are not visible
   in backend or frontend.


Why DCE ships its own Cache Manager?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In DCE 2.1 the TYPO3's core cache manager has been used to cache DCE code. But TYPO3 does not allow to use its
Cache Manager during bootstrapping (limbo mode) in TYPO3 10 anymore. Therefore DCE provides it's own cache manager.

The shipped cache manager uses the same paths as TYPO3 uses for its code cache.

When clearing TYPO3's system caches, the DCE code cache also gets cleared.
