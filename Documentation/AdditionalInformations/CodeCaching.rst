.. include:: ../Includes.txt


.. _additional-informations-code-caching:


Code Caching
------------

DCE generates PHP code and XML for new content elements during TYPO3's bootstrapping. To decrease database queries
during this process, DCE 2.2 introduced an own small CacheManager.

By default the code cache for DCEs is enabled.

.. caution::
   Any changes made to a DCE or a DCE field, require to clear TYPO3's system cache. Otherwise changes are not visible
   in backend or frontend.


Flags
~~~~~

disable_dce_code_cache
^^^^^^^^^^^^^^^^^^^^^^

You can disable the DCE code cache entirely, using the following configuration in ``ext_localconf.php``:

.. code-block:: php

    <?php
    $GLOBALS['TYPO3_CONF_VARS']['USER']['disable_dce_code_cache'] = true;

This will tell DCE to always invalidate and recreate cache files. **Use this for development purposes only!**

When you disable TYPO3's system cache, you also need to disable DCE code cache, to get fully uncached results.


Why DCE ships its own Cache Manager?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In DCE 2.1 the TYPO3's core cache manager has been used to cache DCE code. But TYPO3 does not allow to use its
Cache Manager during bootstrapping (limbo mode) in TYPO3 10 anymore. Therefore DCE provides it's own cache manager.

The shipped cache manager uses the same paths as TYPO3 uses for its code cache.

When clearing TYPO3's system caches, the DCE code cache also gets cleared.
