.. include:: ../Includes.txt

.. _administrator-manual-typoscript-configuration:


Typoscript configuration
-------------


Files root paths
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

DCE is now able to load and use template files from multiple locations.
You can check the default configuration in ``Configuration/TypoScript/setup.typoscript``.

Example of setup override:

.. code-block:: typoscript

    plugin.tx_dce.view.layoutRootPaths.10 = fileadmin/templates/my_theme/Layouts/

    plugin.tx_dce.view.templateRootPaths.10 = fileadmin/templates/my_theme/Templates/
    
    plugin.tx_dce.view.partialRootPaths.10 = fileadmin/templates/my_theme/Partials/