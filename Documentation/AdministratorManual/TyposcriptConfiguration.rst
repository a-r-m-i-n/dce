.. include:: ../Includes.txt

.. _administrator-manual-typoscript-configuration:


TypoScript configuration
------------------------

Files root paths
^^^^^^^^^^^^^^^^

Since version 2.9 DCE is able to load and use template files from multiple locations.
You can check the default configuration in ``Configuration/TypoScript/setup.typoscript``.

Example of setup override:

.. code-block:: typoscript

    plugin.tx_dce.view.templateRootPaths.10 = EXT:my_template/Resources/Private/Dce/Templates/
    plugin.tx_dce.view.partialRootPaths.10 = EXT:my_template/Resources/Private/Dce/Partials/
    plugin.tx_dce.view.layoutRootPaths.10 = EXT:my_template/Resources/Private/Dce/Layouts/
