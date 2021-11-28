.. include:: ../Includes.txt

.. _users-faq:


FAQ
===

.. contents:: :local:


How to access to FAL images?
----------------------------

You can simply iterate over the variable you have defined for the FAL field. Usage of old FAL view helper is not
necessary anymore. But you need to, add these two lines to the field configuration:

::

    <dce_load_schema>1</dce_load_schema>
    <dce_get_fal_objects>1</dce_get_fal_objects>


In your Fluid template you can simply use the images like this:

::

    <f:for each="{field.myImages}" as="image" iteration="iterator">
        <f:image image="{image}" />
    </f:for>

If you want to output only the first image you can use this one liner:

::

    <f:image image="{field.myImages.0}" />


Also the following tt_content fields (selectable in DCE options) are automatically resolved to an array of models,
for easy usage in Fluid template (**{contentObject.xxx}**):

- media
- categories
- assets

How to nest DCE content elements?
---------------------------------

In DCE you are able to nest other DCEs. That means you create a child DCE and a parent DCE and assign the children to
the parent. To enable the parent DCE to store children you need to add a new field with type "group".

**Example field configuration (Variable name: "children"):**

::

    <config>
        <type>group</type>
        <internal_type>db</internal_type>
        <allowed>tt_content</allowed>
        <size>5</size>
        <minitems>0</minitems>
        <maxitems>999</maxitems>
        <show_thumbs>1</show_thumbs>

        <dce_load_schema>1</dce_load_schema>
    </config>


The dce_load_schema flag does the trick here. It realizes, that the tt_content item you have added is a DCE and returns
the DCE object itself. For any other content element, which is no DCE, it will return the tt_content row as
associative array.

But we assume, that you have just added content elements based on DCEs to the "children" field.
In your fluid template you can now do this:

::

    <ul>
        <f:for each="{field.children}" as="childDce" iteration="iterator">
            <li>{childDce.render -> f:format.raw()}</li>
        </f:for>
    </ul>


- first we are iterating through all children, using the f:for loop
- then we call and output the ``render()`` method of child DCE
- because fluid escapes all html by default, we need to use the ``f:format.raw`` view helper

If you don't want to output the whole template of the child DCE you can also address single fields, with:

::

    <f:for each="{field.children}" as="childDce" iteration="iterator">
        <li>{childDce.myCoolField}</li>
    </f:for>


.. caution::
   Since TYPO3 9 Fluid does not allow to use magic ``__call`` method anymore, to resolve field values dynamically.


Since DCE 2.1 you can access fields of child DCEs this way:

::

    <li>{childDce.get.myCoolField}</li>

The previous way to access field values, has been marked as deprecated and will be removed in next major version of DCE.




Am I also able to use file collections?
---------------------------------------

Since version 0.11.x of DCE you are. Just add a group field, set allowed tablename to "sys_file_collection" and add the
**dce_load_schema** option.

Example field configuration:

::

    <config>
        <type>group</type>
        <internal_type>db</internal_type>
        <allowed>sys_file_collection</allowed>
        <size>5</size>
        <minitems>0</minitems>
        <maxitems>999</maxitems>
        <show_thumbs>1</show_thumbs>
        <dce_load_schema>1</dce_load_schema>
    </config>

Your Fluid template gets an array of FileCollection models, now. Here is an example how to output several images from
the FileCollection:

::

    <f:for each="{fields.collections}" as="collection">
        <f:for each="{collection.items}" as="item">
            <f:image src="{item.uid}" maxWidth="250" treatIdAsReference="{f:if(condition:'{item.originalFile}', then: '1', else: '0')}" alt="" />
        </f:for>
    </f:for>

The if condition in the treatIdAsReference is recommended because FileCollections returns different types of objects
depending of the type of the collection. Folder based collections returns the file directly, static based collections
a file reference. With this condition both cases are covered.

.. note::
   When item is a ``FileReference`` you can pass it to image view helper like this:
   ``<f:image image="{item}" maxWidth="250" />``


How to readout an image in a Fluid template and give it a click enlarge function?
---------------------------------------------------------------------------------

If you have defined a field in DCE where you can select images, then you can access the file name in the Fluid template.
The location where the image is stored is also defined in the TCA, which is mostly something like *uploads/pics*.

In the Fluid template you can write following:

::

    <a href="{f:uri.image(image:'{field.yourPicture}')}" class="whatEverYourCssLibraryWantHere">
        <f:image image="{field.yourPicture}" alt="Thumbnail" maxWidth="100" maxHeight="100" />
    </a>

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

The configuration in FlexForms will be overwritten by PageTS. When you've set e.g.

::

    RTE.default.preset = minimal

in PageTS, this will overwrite the "richtextConfiguration" of the DCE field. To avoid this issue, reset the
``RTE.default.preset`` setting in PageTS.


How to render the content of an RTE field?
------------------------------------------

You have to enclose the RTE field with the format.html view helper to get the HTML tags of the RTE rendered.

::

    <f:format.html>{field.rteField}</f:format.html>

You can also use the inline notation:

::

    {field.rteField -> f:format.html()}



How to access variables of other DCE elements?
----------------------------------------------

You can access directly the TypoScript with ``{tsSetup.lib.xyz.value}``.


How to migrate old image fields to new FAL fields?
--------------------------------------------------

Well, this is a problem, which is not solved yet. Of course you can change the field configuration to inline FAL, but
the already stored data/images are not compatible with FAL.

If you do this, you will get this exception: ``#1300096564: uid of file has to be numeric.``

The old way of image handling was that the name of the file is stored inside of the field. The new FAL way is an ``uid``
of the equivalent FAL record. There is no conversion tool existing AFAIK.

Furthermore these filenames are inside of an FlexFormXML, so the steps of a conversion would be:

1. Identify DCEs using old images (by DCE configuration)
2. Get tt_content records and convert FlexFormXML to array
3. Get the old filename and find FAL record
4. Replace filename with ``uid`` of FAL record
5. Save the tt_content record and update the field configuration


How to link to the detail page?
-------------------------------

The link to changeover to the detail page looks like this:

::

    <f:link.page pageUid="{page.uid}" additionalParams="{detailUid: '{contentObject.uid}'}">Detail</f:link.page>

Where detailUid is the value of the field "Detail page identifier (get parameter)" you have set on the
"Detail page" tab.


How to wrap my content elements with a container?
-------------------------------------------------

Sometimes you need a wrapping element in HTML template, for all content elements from same type. We recommend to use
gridelements, because it brings columns to content elements which are structured in database.

You can also use the DCE feature "DCE Container", which simulates a container for certain content elements in a row,
based on the same DCE.


How to add content elements to my DCE
-------------------------------------

If you are looking for a way to create columns and put content elements in these columns right in page modules -
this is not supported by DCE.

For this case I recommend the `grid elements extension <http://typo3.org/extensions/repository/view/gridelements>`_.

But if you create a group or select field you may define the tt_content table and add existing content elements.
This is not much comfortable but very flexible, because you may also add any other table of any extension installed.
And with the *dce_load_schema* flag you will receive an assosiative array of requested row, or if the table is from an
Extbase extension you will get a model of the requested table.


How to change the long title in content wizard for DCE group
------------------------------------------------------------

If you enable DCEs to be visible in content wizard, they can be grouped in a new group, introduced by DCE,
called "Dynamic Content Elements". This is in some cases to much text.

If you want to rename this group just use this code in PageTS:

::

    mod.wizards.newContentElement.wizardItems.dce.header = Whatever you want
