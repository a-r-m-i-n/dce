.. include:: ../Includes.txt


.. _additional-informations-user-conditions:


User conditions
---------------

User conditions can be used in the TypoScript setup. DCE provides such a user condition:

DceOnCurrentPage
~~~~~~~~~~~~~~~~

This user condition checks if the current page contains a content element based on given DCE.

Usage in TypoScript:

.. code-block:: typoscript

    [dceOnCurrentPage("42")]
    [dceOnCurrentPage("teaser")]


The 42 is a sample for the ``uid`` of a DCE.
If you have defined an identifier, you can also use it in user condition parameter.
