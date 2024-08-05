.. include:: ../Includes.txt


.. _administrator-upgrading:


Upgrading DCE
-------------

New version 3.0 of DCE and TYPO3 v12 contain various changes, which requires some manual adjustments.

DCE provides some upgrade wizards in install tool of TYPO3, which pop up when necessary.

.. note::
   DCE 3.2 which introduced TYPO3 v13 support, does not require any manual adjustments or upgrades.


Steps
=====

Just change your requirements section to

::

    "t3/dce": "^3.0"

and perform ``composer update``.

Then go to TYPO3 Install Tool and check (and perform) the **upgrade wizards** and **database compare**!

Also, make sure you've deleted the DCE cache files (located in `var/cache/code/cache_dce`).


Templates in fileadmin
======================

If you still use DCE templates located in fileadmin, loaded via FAL, you need to make manual adjustments.

The only way to load DCE template files, is using the `EXT:` syntax. For example:

::

    EXT:my_provider_extension/Resources/Private/Templates/Dce/MyDce.html


Good to know
============

f:format.html without config
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When you use ``f:format.html`` view helper in your templates (frontend or backend) you will get
the error

::

    Invoked ContentObjectRenderer::parseFunc without any configuration

According to the `deprecation changelog <https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-96520-EnforceNon-emptyConfigurationInCObjparseFunc.html>`_,
you can simply change ``f:format.html`` to ``f:format.raw``.
