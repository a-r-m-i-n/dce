.. include:: ../Includes.txt


.. _administrator-upgrading:


Upgrading DCE
-------------

New version 3.0 of DCE and TYPO3 v12 contain various changes, which requires some manual adjustments.

DCE provides some upgrade wizards in install tool of TYPO3, which pop up when necessary.


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
