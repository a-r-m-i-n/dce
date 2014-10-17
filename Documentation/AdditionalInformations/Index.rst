.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _additional-informations:

Additional informations
=======================

User conditions
---------------

User conditions can be used in the TypoScript setup. DCE provides such a user condition:

**user_dceOnCurrentPage**
This user condition checks if the current page contains a DCE (instance).
Usage in TypoScript:

::

	[userFunc = user_dceOnCurrentPage(42)]

The 42 is a sample for the UID of a DCE type.