.. include:: ../Includes.txt


.. _administrator-upgrading:


Upgrading DCE
-------------

Upgrading to latest version 2.0 is simple, when you are at least on **TYPO3 8.7** and **DCE 1.5**.

DCE provides some upgrade wizards in install tool of TYPO3, which pop up when necessary.


With composer
=============

Just change your requirements section to

::

    "t3/dce": "^2.7"

and perform ``composer update``.

Then go to TYPO3 Install Tool and check (and perform) the **upgrade wizards** and **database compare**!


Without composer
================

Because DCE 2.0 changed namespaces an update may occure error messages, like:

::

    Fatal error: Class 'T3\Dce\ViewHelpers\ArrayGetIndexViewHelper' not found


When you already have this error, you can simply delete the ``typo3conf/autoload`` folder. TYPO3 will recreate it.


To avoid this error before it happens, perform these steps:

1. Uninstall DCE in extension manager
2. Perform update (manual upload or TER update)
3. Reinstall DCE
4. Go to install tool and perform the **upgrade wizards** and **database compare**
