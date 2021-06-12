.. include:: ../Includes.txt


.. _administrator-installation:


Installation
------------

You can install DCE with or without composer.

With composer
~~~~~~~~~~~~~

Just require DCE on cli:

::

    $ composer req t3/dce:"^2.7"


When composer is done you need to enable the extension in the extension manager.


Without composer
~~~~~~~~~~~~~~~~

You can also fetch DCE from `TER <https://extensions.typo3.org/extension/dce/>`_ and install it the old-fashioned way.

.. image:: Images/extension-manager.png
   :alt: DCE in extension manager of TYPO3 CMS

.. tip::
   You need to enable the extension in Extension Manager with or without composer in use!
   Or use CLI tools for that.

DCE provides no further options here.


Gridelements notice
-------------------

Caution, when you also use EXT:gridelements in your TYPO3 project!

It can happen that DCE **content elements have no contents available** in Fluid template, when they
**are located inside a grid column**. This affects also all plugins, using pi_flexform.

When Gridelements is installed, it applies "magic" to pi_flexform by default. In order to make TYPO3 work as expected,
you need to disable this behaviour on your own, using TypoScript like this:

::

    tt_content.gridelements_pi1.dataProcessing.10.default.options.resolveChildFlexFormData = 0
