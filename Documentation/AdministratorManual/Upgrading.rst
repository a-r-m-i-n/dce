.. include:: ../Includes.txt


.. _administrator-upgrading:


Upgrading DCE
-------------

Current DCE releases do not contain upgrade wizards. No DCE data migration is required when upgrading from TYPO3 v13
to v14.

Upgrade from TYPO3 v13
======================

Update TYPO3 and DCE with Composer, run the TYPO3 database schema update and flush all caches. Afterwards, verify the
DCE backend module, existing DCE content elements and the frontend output.

Upgrade from TYPO3 v12
======================

Prepare the installation before updating the TYPO3 core:

#. Update DCE to version 3.3.2 while the installation still runs TYPO3 v12.
#. Add required database columns, but keep obsolete DCE MM tables until the DCE upgrade wizards have finished.
#. Run all DCE and TYPO3 upgrade wizards offered by the Install Tool.
#. Complete the database schema update and flush all caches.
#. Verify that no DCE upgrade wizard is pending and that existing DCE content elements work correctly.
#. Upgrade TYPO3 one major version at a time, first to v13 and then to v14, using a matching DCE version at each step.

.. warning::

   Installing DCE 3.3.2 alone is not sufficient. All upgrade wizards offered by that version must be completed before
   installing a current DCE release. The current release can no longer migrate legacy DCE data structures.

Older installations
===================

Do not update a very old TYPO3 installation and DCE to their current versions in a single Composer operation. Create a
database and file backup, upgrade TYPO3 one major version at a time and use a matching DCE version at each step. Once the
installation runs TYPO3 v12, install DCE 3.3.2 and follow the steps above.

DCE 3.3.2 contains the migrations for legacy field relations, FlexForm sheet identifiers, malformed variable names, old
file fields and file references. Do not remove obsolete DCE MM tables before these migrations have finished. Some file
field migrations also require manual changes to the affected Fluid templates.

If TYPO3 or DCE has already been upgraded without running these migrations, restore the pre-upgrade backup or use a
temporary TYPO3 v12 installation with DCE 3.3.2 and the old database. Current DCE releases can no longer migrate these
legacy data structures.
