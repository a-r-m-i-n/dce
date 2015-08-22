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


Upgrade wizard
--------------

The core switched from normal class names to namespaced one. As DCE did. Therefore the namespace in DCE templates, used for referencing to
DCE's view helpers, changed.

Furthermore some old viewhelpers are gone or marked as deprecated and should be updated!

To do this, just execute the upgrade wizard. It checks your all your templates in all DCEs and updates them if necessary.
This also works with templates stored to files, if the file is writable for the webserver. If not you'll see it.

If your code is using a VCS like Git or SVN, please don't forget to download the updated templates and commit them to your system ;-)

The following actions will be performed:

+ Find *{namespace dce=Tx_Dce_ViewHelpers}* and replace with *{namespace dce=ArminVieweg\Dce\ViewHelpers}*
+ Find *dce:format.raw* and replace with *f:format.raw*
+ Find *dce:image* and replace with *f:image*
+ Find *dce:uri.image* and replace with *f:uri.image*


