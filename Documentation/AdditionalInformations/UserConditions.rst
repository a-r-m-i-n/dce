.. _additional-informations-user-conditions:


User conditions
---------------

User conditions can be used in the TypoScript setup. DCE provides such a user condition:

DceOnCurrentPage
~~~~~~~~~~~~~~~~

This user condition checks if the current page contains a content element based on given DCE.

Usage in TypoScript:

.. code-block:: typoscript

    [dceOnCurrentPage("teaser")]
        page.10 = TEXT
        page.10.value = The current page contains a teaser DCE.
    [END]

You can use either the identifier or the uid of a DCE. The equivalent uid-based condition is:

.. code-block:: typoscript

    [dceOnCurrentPage("42")]
        page.10 = TEXT
        page.10.value = The current page contains DCE 42.
    [END]

The condition respects the standard record restrictions and follows the page configured as content source through
``content_from_pid``.
