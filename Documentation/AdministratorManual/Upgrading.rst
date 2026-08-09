.. _administrator-upgrading:


Upgrading DCE
-------------

DCE 4.0 does not contain upgrade wizards. The manual changes described below are required when upgrading existing
installations.

Upgrade from TYPO3 v13
======================

Update TYPO3 and DCE with Composer. Existing DCE records using the former default wizard group ``common`` must be
changed to the TYPO3 v14 group ``default``:

.. code-block:: sql

    UPDATE tx_dce_domain_model_dce SET wizard_category = 'default' WHERE wizard_category = 'common';

The ``dce:format.cdata`` ViewHelper has been removed. Replace usages in stored and file-based templates with Fluid's
native CDATA syntax:

.. code-block:: html

    <![CDATA[
        {{{field.xml}}}
    ]]>

Then run the TYPO3 database schema update, flush all caches and verify the DCE backend module, existing DCE content
elements and frontend output.

Upgrade from TYPO3 v12
======================

Prepare the installation before updating the TYPO3 core:

#. Update DCE to version 3.3.2 while the installation still runs TYPO3 v12.
#. Add required database columns, but keep obsolete DCE MM tables until the DCE upgrade wizards have finished.
#. Run all DCE and TYPO3 upgrade wizards offered by the Install Tool.
#. Complete the database schema update and flush all caches.
#. Verify that no DCE upgrade wizard is pending and that existing DCE content elements work correctly.
#. Upgrade directly to TYPO3 v14.3 and DCE 4.0, and apply the manual changes described in the section above. From DCE's
   perspective, an intermediate upgrade to TYPO3 v13 is not required if all upgrade wizards provided by DCE 3.3.2 have
   been completed while the installation was still running TYPO3 v12.

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
