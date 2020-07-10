.. include:: ../Includes.txt

.. _users-manual-general:


General
-------

On this tab, you can make the general settings. You can decide whether the element is visible or not, give it a
meaningful name and identifier and define all the fields that are needed.

.. image:: Images/new-dce.png
   :alt: Create new DCE, general tab

Title
^^^^^

This is the name of the DCE, which is also shown in the type selection of a content element.
You can point to LLL: references here and translate the title for different languages.

Identifier
^^^^^^^^^^

The identifier is used as CType for new content elements based on this DCE. All identifiers of DCE are prefixed
with "dce\_". For example: "dce_example". When the identifier of a DCE remains empty, the old syntax based on ``uid`` is
used automatically. The identifier must be written in lowercase, undercores are allowed.

Example: ``dce_dceuid1`` or ``dce_what_ever``

Inactive
^^^^^^^^

When the DCE is inactive or hidden, it is not shown as the type when a content element is created.
It is only shown in the DCE BE module.


Fields
^^^^^^

.. image:: Images/new-dce-field.png
   :alt: A new DCE field

In the fields section, you can add a number of different fields that this DCE should contain.
You have to add at least one field.

A field has three types available:

- **Element**
  This is a field in your new content element, like a text field, a checkbox, an image or a
  whole rich text editor (RTE). The composition for this field is done in the configuration.
  All TCA field types are supported.
- **Tab**
  This creates a new tab register. All fields that are defined below this tab are shown in BE on a new tab page.
  You may also rename the first "General" tab by creating a tab as the first item.
- **Section**
  **It is highly encouraged to not use sections anymore!**
  Check if DCE Container can help you or use EXT:gridelements for your purposes.

Common to all types is the *title* field, where you define a speaking label for the editor.
You can use ``LLL:`` reference here. For the type *Tab* there are no more options to define.

.. caution::
   When you add/update tabs and/or rearrange fields, the FlexForm structure changes!
   Already existing content elements with FlexForm data, need to get migrated, afterwards.


Field options
+++++++++++++

For the default type *Element* you have to define a **title** (with ``LLL:`` support) and a **variable** name which is
to be used in the Fluid template. Variable names have to be written in **lowerCamelCase** and the variable names must
be unique inside each DCE.

.. note::
   The configuration for the fields is stored in **FlexForm (XML)** format. Look for TCEforms in the FlexForms section
   of the `T3DataStructure <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/DataFormats/T3datastructure/Index.html>`_
   documentation to get detailed information for the definition of the field configuration.

To make it a bit easier there is a handy select box provided with the most used possible input field types.
If you select one entry the corresponding FlexForm XML code is inserted in the configuration input field.

.. image:: Images/field-configuration-dropdown.png
   :alt: Handy dropdown which most common needed TCA configurations

.. image:: Images/configuration-simple-input-field.png
   :alt: Pasted configuration for "Simple input field"

For fields which use the TCA types **group**, **select** or **inline**, there are additional configuration attributes
provided by DCE available.

dce_load_schema
~~~~~~~~~~~~~~~

::

    <dce_load_schema>1</dce_load_schema>

When adding a group field and link it with e.g. News (by Georg Ringer), then the field contains a comma-delimited list
with uids of the selected news. That is not very useful in Fluid templates.

But when this attribute is activated, the used table is inspected. If an Extbase model and repository exist for this
table then the repository is instantiated and a ``findByUid()`` is called for every ``uid``. The complete Extbase models are
then taken over to the Fluid template (as an array).

If the table is not part of an Extbase extension, the corresponding record is loaded from the database and handed
over as an associated array.

.. caution::
   This function works only with one table, if you configure more tables it does not work.

Using the table tt_content and adding content elements which are based on another DCE, automatically the
corresponding DCE will be loaded and filled. In the template of the second DCE the template of the inserted DCE can be
called and rendered:

::

    <f:for each="{field.otherDces}" as="othersDce">
        {otherDce.render -> f:format.raw()}
    </f:for>

You need to use the raw view helper of Fluid because otherwise the rendered HTML will be escaped.
If you use the ``f:format.html`` view helper the curly braces get escaped and variables will not be interpreted anymore.

It is also possible to access directly the value of single fields:

::

    {otherDce.fieldname}


dce_load_entity_class
~~~~~~~~~~~~~~~~~~~~~

::

    <dce_load_entity_class>VendorName\Extension\Domain\Model\YourModel</dce_load_entity_class>

Uses this class (and its repository) instead of guessing the model class name from table name.

dce_get_fal_objects
~~~~~~~~~~~~~~~~~~~

::

    <dce_get_fal_objects>1</dce_get_fal_objects>

If you have defined a FAL field and this attribute is activated, the value is directly replaced with a
``TYPO3\CMS\Core\Resource\File`` object from the repository.

dce_ignore_enablefields
~~~~~~~~~~~~~~~~~~~~~~~

::

    <dce_ignore_enablefields>1</dce_ignore_enablefields>

Setting this attribute ignores the enable fields of the requested table. All enable fields like deleted, hidden,
starttime, endtime get ignored then. This can be used for outputting hidden records.

dce_enable_autotranslation
~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    <dce_enable_autotranslation>1</dce_enable_autotranslation>

If you load a page via group field, then always this page is loaded, regardless of the language that is just used.
Using this attribute shows the translated page if it exists (``$GLOBALS['TSFE']->sys_page->getPageOverlay()``).

That also works with other records, not only with records of the pages table, then ``getRecordOverlay()`` will be used.
