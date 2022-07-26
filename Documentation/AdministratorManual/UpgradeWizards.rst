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

.. _administrator-upgrade-wizards-file2fal:

FileToFalUpdate
~~~~~~~~~~~~~~~

Migrates old "type: group, internal_type: file" DCEs and content elements based on it.
Moving files from uploads/ to fileadmin/ and index by FAL. Also migrating section fields, with old image configurations.

Required for TYPO3 10 and higher.

**All operations get logged in a separate log file:** ``var/log/dce_update_wizards.log``

What it does
^^^^^^^^^^^^

First, the update wizard detects DCEs with fields which got the old ``type: group, internal_type: file`` configuration given.
It also checks the existence of media files within content elements based on those DCEs.

When executing the update wizard, the following changes get applied:

1. Create new directories in fileadmin (based on configured ``uploadfolder``). When your files were located in ``/public/uploads/pics``
   the directory ``/public/fileadmin/uploads/pics`` will get created
2. Moving the media files
3. Indexing moved media files in FAL
4. Updating the old DCE field configuration with "FAL: File Abstraction Layer" configuration, respecting
   - minitems
   - maxitems
   - allowed (file extensions)
5. Create new sys_file_reference entries (one for each media file)
6. Update tt_content's pi_flexform contents (replace comma separated list of file names with amount of existing sys_file_references)

The steps 4 and 5 work differently for section fields. Instead of using FAL/inline it creates a group field pointing to sys_file.
And instead of creating sys_file_reference entries, it writes the uids of sys_file in the pi_flexform.

In order to apply the changes to TYPO3's system caches, **you need to flush all system caches manually**.

Next steps
^^^^^^^^^^

Unfortunately this migration script is not able to also update your Fluid templates automatically.

To help you with that, the log file (``var/log/dce_update_wizards.log``) contains a list of affected DCEs,
once the update wizard has been executed successfully.

There are two common cases:

**Single image output**

When you limited the field to one single image, your Fluid template probably looks like that, when it's about outputting the image:

.. code-block:: html

    <f:image src="uploads/pics/{field.img}" />

Because FAL **always** returns a collection of images, you need to change this output to:

.. code-block:: html

    <f:image image="{field.img.0}" />

Because we use FAL now, we replace the **src** by the **image** attribute. Now, within ``{field.img.0}`` we got a
FileReference object. In order to only return the first (and only) image in this collection, we append ``.0`` to the variable.
Also we get rid of the image base path, which get handled by FAL now.


**Multi image output**

When you already dealt with multiple images, your template most likely looks like that:

.. code-block:: html

	<f:for each="{field.images -> dce:explode()}" as="image">
	    <f:image src="uploads/pics/{image}" />
	</f:for>

With the old configuration multiple images has been stored as comma separated string (e.g. ``image1.jpg,image2.jpg``).
Now, ``field.images`` contains the collection of FileReference objects, which we can iterate through this way:

.. code-block:: html

	<f:for each="{field.images}" as="image">
	    <f:image image="{image}" />
	</f:for>

We just need to remove the usage of ``-> dce:explode()`` view helper and apply the same changes to the image view helper,
as we did for a single image.

This adjustment also works with images inside of sections (there, ``sys_file`` is being used instead of ``sys_file_reference``).
