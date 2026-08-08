.. _users-faq:


FAQ
===

.. contents:: :local:


How to access to FAL images?
----------------------------

You can simply iterate over the variable you have defined for the FAL field. Usage of old FAL view helper is not
necessary anymore. But you need to, add these two lines to the field configuration:

.. code-block:: xml

    <dce_load_schema>1</dce_load_schema>
    <dce_get_fal_objects>1</dce_get_fal_objects>


In your Fluid template you can simply use the images like this:

.. code-block:: html

    <f:for each="{field.myImages}" as="image" iteration="iterator">
        <f:image image="{image}" />
    </f:for>

If you want to output only the first image you can use this one liner:

.. code-block:: html

    <f:image image="{field.myImages.0}" />


Related ``tt_content`` fields are also resolved for convenient use in Fluid templates:

- ``{contentObject.media}`` is resolved as a relation.
- ``{contentObject.assets}`` is resolved when Fluid Styled Content is loaded and is the field displayed by DCE's
  media-tab option.
- ``{contentObject.categories}`` is resolved when the field is present.


How to nest DCE content elements?
---------------------------------

In DCE you are able to nest other DCEs. That means you create a child DCE and a parent DCE and assign the children to
the parent. To enable the parent DCE to store children you need to add a new field with type "group".

**Example field configuration (Variable name: "children"):**

.. code-block:: xml

    <config>
        <type>group</type>
        <allowed>tt_content</allowed>
        <size>5</size>
        <minitems>0</minitems>
        <maxitems>999</maxitems>
        <dce_load_schema>1</dce_load_schema>
    </config>


The dce_load_schema flag does the trick here. It realizes, that the tt_content item you have added is a DCE and returns
the DCE object itself. For any other content element, which is no DCE, it will return the tt_content row as
associative array.

But we assume, that you have just added content elements based on DCEs to the "children" field.
In your fluid template you can now do this:

.. code-block:: html

    <ul>
        <f:for each="{field.children}" as="childDce" iteration="iterator">
            <li>{childDce.render -> f:format.raw()}</li>
        </f:for>
    </ul>


- first we are iterating through all children, using the f:for loop
- then we call and output the ``render()`` method of child DCE
- because fluid escapes all html by default, we need to use the ``f:format.raw`` view helper

If you do not want to output the whole template of the child DCE, access individual fields through ``get``:

.. code-block:: html

    <f:for each="{field.children}" as="childDce" iteration="iterator">
        <li>{childDce.get.myCoolField}</li>
    </f:for>


Am I also able to use file collections?
---------------------------------------

Since version 0.11.x of DCE you are. Just add a group field, set allowed tablename to "sys_file_collection" and add the
**dce_load_schema** option.

Example field configuration:

.. code-block:: xml

    <config>
        <type>group</type>
        <allowed>sys_file_collection</allowed>
        <size>5</size>
        <minitems>0</minitems>
        <maxitems>999</maxitems>
        <dce_load_schema>1</dce_load_schema>
    </config>

Your Fluid template receives an array of FileCollection models. Both ``File`` and ``FileReference`` items can be passed
directly to the image ViewHelper:

.. code-block:: html

    <f:for each="{field.collections}" as="collection">
        <f:for each="{collection.items}" as="item">
            <f:image image="{item}" maxWidth="250" alt="" />
        </f:for>
    </f:for>


How to readout an image in a Fluid template and give it a click enlarge function?
---------------------------------------------------------------------------------

Current DCE file fields provide an array of FAL ``FileReference`` objects. To render the first selected image with a
link to its processed full-size variant, use:

.. code-block:: html

    <f:if condition="{field.yourPicture.0}">
        <a href="{f:uri.image(image: field.yourPicture.0)}" class="your-lightbox-class">
            <f:image image="{field.yourPicture.0}" alt="Thumbnail" maxWidth="100" maxHeight="100" />
        </a>
    </f:if>

With the f:image view helper a thumbnail of the image, that should be shown, is issued.
TYPO3 creates an image with a reduced size and stores it in *fileadmin/_processed_/*.

In the href parameter of the link, which should show the big version of the image when it is clicked,
you use the f:uri.image view helper. In principle it is the same as the f:image view helper, but instead of an image
only a URL is created.

The benefit of using this view helper is that you also can use height and width to limit the size of the big image
(e.g. 800x600).


How to apply custom CKeditor configuration in DCE?
--------------------------------------------------

When you've defined a DCE field as RTE (rich text editor), you also defined the "richtextConfiguration" to be used,
e.g. "default", "minimal" or something custom.

TYPO3 resolves the RTE preset in this order: field-specific PageTS, the field's ``richtextConfiguration``, the global
``RTE.default.preset`` PageTS setting, and finally the ``default`` preset. Therefore, a DCE field's
``richtextConfiguration`` takes precedence over the global default. Only field-specific PageTS can override it.

How to render the content of an RTE field?
------------------------------------------

You have to enclose the RTE field with the format.html view helper to get the HTML tags of the RTE rendered.

.. code-block:: html

    <f:format.html>{field.rteField}</f:format.html>

You can also use the inline notation:

.. code-block:: html

    {field.rteField -> f:format.html()}



How to access the current TypoScript setup?
-------------------------------------------

In frontend, you can access directly the TypoScript setup from current page, using ``{tsSetup.lib.xyz.value}``.


How to link to the detail page?
-------------------------------

The link to changeover to the detail page looks like this:

.. code-block:: html

    <f:link.page additionalParams="{detailDceUid: contentObject.uid}">Detail</f:link.page>

Where ``detailDceUid`` is the value of the field "Detail page identifier (GET parameter)" you have set on the
"Detail page" tab.


How to wrap my content elements with a container?
-------------------------------------------------

Sometimes you need a wrapping element in HTML template, for all content elements from same type. I recommend to use
`EXT:container <https://extensions.typo3.org/extension/container>`_, because it brings columns to content elements
which are structured in database.

You can also use the DCE feature :ref:`DCE Container <users-manual-dcecontainer>`, which simulates a container for
certain content elements in a row, based on the same DCE.


How to change the long title in content wizard for DCE group
------------------------------------------------------------

If you enable DCEs to be visible in content wizard, they can be grouped in a new group, introduced by DCE,
called "Dynamic Content Elements". This can be too much text in some cases.

If you want to rename this group just use this code in PageTS:

.. code-block:: typoscript

    mod.wizards.newContentElement.wizardItems.dce.header = Whatever you want

You can also modify the position of the group, in PageTS. This is the default value:

.. code-block:: typoscript

    mod.wizards.newContentElement.wizardItems.dce.after = default
