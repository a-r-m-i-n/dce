.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-faq:

FAQ
===


How to access to FAL images?
-----------------------------------------------

You can simply iterate over the variable you've defined for the FAL field. Usage of old FAL viewhelper is not necessary
anymore. But you need to, add these two lines to the field configuration:

::

    <dce_load_schema>1</dce_load_schema>
    <dce_get_fal_objects>1</dce_get_fal_objects>


In your fluid template you can simply use the images like this:

::

    <f:for each="{field.myImages}" as="image" iteration="iterator">
        <f:image image="{image}" />
    </f:for>

If you want to output only the first image you can use this one liner:

::

    <f:image image="{field.myImages.0}" />


Also the following tt_content fields (selectable in DCE options) are automatically resolved to an array of models,
for easy usage in fluid template (**{contentObject.xxx}**):

- media
- categories
- assets



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

Your fluid template gets an array of FileCollection models, now. Here is an example how to output several images from the FileCollection:

::

    <f:for each="{fields.collections}" as="collection">
        <f:for each="{collection.items}" as="item">
            <f:image src="{item.uid}" maxWidth="250" treatIdAsReference="{f:if(condition:'{item.originalFile}', then: '1', else: '0')}" alt="" />
        </f:for>
    </f:for>

The if condition in the treatIdAsReference is recommended because FileCollections returns different types of objects depending of the type of the collection. Folder based collections returns the file directly, static based collections a file reference. With this condition both cases are covered.

File collections are available since TYPO3 6.0.


How to readout an image in a Fluid template and give it a click enlarge function?
---------------------------------------------------------------------------------

If you have defined a field in DCE where you can select images than you can access the file name in the Fluid template. The location where the image is stored is also defined in the TCA, which is mostly something like *uploads/pics*.

In the Fluid template you can write following:

::

	<a href="{f:uri.image(src:'uploads/pics/{field.yourPicture}')}" class="whatEverYourCssLibraryWantHere">
		<f:image src="uploads/pics/{field.yourPicture}" alt="Thumbnail" maxWidth="100" maxHeight="100" />
	</a>

With the f:image ViewHelper a thumbnail of the image, that should be shown, is issued. TYPO3 creates an image with a reduced size and stores it in *typo3temp/pics/*.

In the href parameter of the link, which should show the big version of the image when it is clicked, you use the f:uri.image ViewHelper. In principle it is the same as the f:image ViewHelper, but instead of an image only a URL is created. The benefit of using this ViewHelper is that you also can use height and width to limit the size of the big image (e.g. 800x600).


How to get the fields of a section?
-----------------------------------

You can access the fields of a section by iterating over the section elements:

::

	<f:for each="{field.sectionField}" as="sectionField">
		{sectionField.text}<br />
	</f:for>


How to use FAL within sections?
-------------------------------

Checkout this wiki article: https://forge.typo3.org/projects/extension-dce/wiki/How_to_use_FAL_within_sections


How to use link property for FAL images?
----------------------------------------

Checkout this wiki article: https://forge.typo3.org/projects/extension-dce/wiki/How_to_link_your_FAL_images


How to translate fields in the BE?
----------------------------------

Instead of writing down the absolute title of the field, you may also add a path to a locallang file and a key inside of this file.

Example:

::

	LLL:fileadmin/templates/locallang.xml:myCoolTranslatedField


The locallang.xml file content:

::

	<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
	<T3locallang>
		<meta type="array">
			<type>module</type>
			<description>Language labels for the DCEs in backend</description>
		</meta>
		<data type="array">
			<languageKey index="default" type="array">
				<label index="myCoolTranslatedField">My cool translated field</label>
			</languageKey>
			<languageKey index="de" type="array">
				<label index="myCoolTranslatedField">Mein cooles, übersetztes Feld</label>
			</languageKey>
		</data>
	</T3locallang>


A more comfortable way to translate fields in the backend is planned for version 2.0 of the extension. (`Backend module: Translatable fields <http://forge.typo3.org/issues/58540>`_)


How to render the content of an RTE field?
------------------------------------------

You have to enclose the RTE field with the format.html ViewHelper to get the HTML tags of the RTE rendered.

::

	<f:format.html>{field.rteField}</f:format.html>

You can also use the inline notation:

::

    {field.rteField -> f:format.html()}



How to access variables of other DCE elements?
----------------------------------------------

You can access directly the TypoScript with {tsSetup.lib.xyz.value} .


How to migrate old image fields to new FAL fields?
--------------------------------------------------

Well, this is a problem, which is not solved yet. Of course you can change the field configuration to inline FAL, but the already stored data/images are not compatible with FAL.

If you do this, you will get this exception: ``#1300096564: uid of file has to be numeric.``

The old way of image handling was that the name of the file is stored inside of the field. The new FAL way is an uid of the equivalent FAL record. There is no conversion tool existing AFAIK.
Furthermore these filenames are inside of an FlexformXML, so the steps of a conversion would be:

1. Identify DCEs using old images (by DCE configuration)
2. Get tt_content records and convert FlexformXML to array
3. Get the old filename and find FAL record
4. Replace filename with uid of FAL record
5. Save the tt_content record and update the field configuration


How to create a dynamic content like an accordion?
--------------------------------------------------

- Create a new DCE
- Add a section
- Add an element in the section, this will be the tab element of which you can add as many as you want.
- In the template tab point to an own layout (accordionLayout), which is a simple HTML file.

The template tab looks like this:

::

	<f:layout name="accordionLayout" />

	<f:section name="sectionName">
        <f:for each="{field.sectionName}" as="section">
            <div class="flag">{section.yourField}</div>
        </f:for>
	</f:section>

The accordionLayout file contains this:

::

	<div class="accordion"><f:render section="sectionName" /></div>


How to link to the detail page?
-------------------------------

The link to changeover to the detail page looks like this:

::

	<f:link.page pageUid="{page.uid}" additionalParams="{detailUid: '{contentObject.uid}'}">Detail</f:link.page>

Where detailUid is the value of the field "Detail page identifier (get parameter)" you have set on the "Detail page" tab.


How to wrap my content elements with a container?
-------------------------------------------------

Sometimes you need a wrapping element in HTML template, for all content elements from same type. We recommend to use
gridelements, because it brings columns to content elements which are structured in database.

You can also use the DCE feature "DCE Container", which simulates a container for certain content elements in a row,
based on the same DCE. This article tells you more: https://forge.typo3.org/projects/extension-dce/wiki/DCE_Container



How to add content elements to my DCE
-------------------------------------

If you are looking for a way to create columns and put content elements in these columns right in page modules - this is not supported by DCE. For this case I recommend the `grid elements extension <http://typo3.org/extensions/repository/view/gridelements>`_.

But if you create a group or select field you may define the tt_content table and add existing content elements. This is not much comfortable but very flexible, because you may also add any other table of any extension installed. And with the *dce_load_schema* flag you'll receive an assosiative array of requested row, or if the table is from an extbase extension you'll get a model of the requested table.


How to change the long title in content wizard for DCE group
------------------------------------------------------------

If you enable DCEs to be visible in content wizard, they can be grouped in a new group, introduced by DCE, called "Dynamic Content Elements". This is in some cases to much text. If you want to rename this group just use this code in PageTS:

::

	mod.wizards.newContentElement.wizardItems.dce.header = Whatever you want


How to update DCE from (very) old versions?
-------------------------------------------

Checkout these links:

- https://forge.typo3.org/projects/extension-dce/wiki/Updating-DCE-0x-to-1x
- https://forge.typo3.org/projects/extension-dce/wiki/Updating-DCE-from-version-below-12

