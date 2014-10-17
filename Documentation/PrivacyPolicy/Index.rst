.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _privacy-policy:

Privacy Policy
====================

The backend module of DCE contains an image which is located on my server. It shows the user if there
is a new DCE version available.

It passes:

- the TYPO3 version
- the DCE version
- and the backend language

Based on these informations I'm able to say: "Yes, a new version is available, but not for your TYPO3 version.". These values are passed completely anonymously and help me to improve the extension.

Because I have the data I am also able to get statistics. Like: Which TYPO3 version is used most often?
I'm going to publish some interesting graphs based on these data on the `Facebook page`_ of DCE extension.

.. _Facebook page: https://www.facebook.com/TYPO3.DCE.Extension


Disable the update check
------------------------

In the extension settings of DCE you are able to disable the check of new versions of DCE.

Then no image will be loaded and no data will be passed. In `this forge ticket`_ you'll see the changed code and ensure that no data will be passed anymore, if the option is set.

.. _this forge ticket: https://forge.typo3.org/issues/62302