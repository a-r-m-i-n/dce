.. _administrator-installation:


Installation
------------

You can install DCE with or without composer.

.. important::
   When upgrading an existing installation, follow the :ref:`DCE upgrading guide <administrator-upgrading>`.

.. note::
   If your project uses an older TYPO3 version, install an older, compatible DCE version instead.
   Check the available versions and their TYPO3 requirements on `Packagist <https://packagist.org/packages/t3/dce>`_
   or in the `TYPO3 Extension Repository <https://extensions.typo3.org/extension/dce/>`_.

Requirements
~~~~~~~~~~~~

* TYPO3 14.3 or newer within the TYPO3 14 release line
* PHP 8.4
* PHP extensions DOM and JSON

With composer
~~~~~~~~~~~~~

Require DCE on the command line:

.. code-block:: bash

    $ composer req t3/dce:"^4.0"


Composer installs and activates DCE automatically. Afterwards, set up the extensions and apply the required database
schema changes:

.. code-block:: bash

    $ vendor/bin/typo3 extension:setup


Without composer
~~~~~~~~~~~~~~~~

You can also fetch DCE from `TER <https://extensions.typo3.org/extension/dce/>`_ and install it without Composer.
Import and activate the extension through :guilabel:`System > Extensions`.

.. tip::
   Manual activation in :guilabel:`System > Extensions` is only required for installations without Composer.

DCE provides no further options here.
