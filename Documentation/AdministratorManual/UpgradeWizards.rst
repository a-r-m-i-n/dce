.. include:: ../Includes.txt


.. _administrator-upgrade-wizards:


Upgrade wizards
---------------

DCE ships a bunch of upgrade wizards with, to upgrade from older releases of DCE:

.. contents:: :local:


FixMalformedDceFieldVariableNamesUpdate
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This update checks all DCE field variables for being valid. If not it can correct them automatically.


MigrateDceFieldDatabaseRelationUpdate
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In a very old version of DCE (0.x) the relations between DCE fields and DCEs were m:n. This wizard helps to migrate
old MM relations.

.. warning::
   Do not delete the old MM tables, before you have performed this upgrade wizard.
   Database compare offers you to delete the tables, what you can do, afterwards.


MigrateFlexformSheetIdentifierUpdate
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the past DCE named tabs in FlexForm configuration like this:

::

    <sheet0></sheet0>


But this has the effect that all your data is broken, when you change
the order of tabs in a DCE. Now the sheets have a named identifier. You
can set the identifier in the variable field which is also visible for
tab fields, now.

The FlexForm configuration looks like this now:

::

    <sheet.tabGeneral></sheet.tabGeneral>

The very first sheet has the identifier/variable "tabGeneral" by default. This wizard takes are about this.


MigrateOldNamespacesInFluidTemplateUpdate
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This converts all Fluid templates which still uses namespace declarations like

::

    {namespace dce=ArminVieweg\Dce\ViewHelpers}

These are not required anymore, because ``dce:`` is globally registered in Fluid, when DCE is installed.
