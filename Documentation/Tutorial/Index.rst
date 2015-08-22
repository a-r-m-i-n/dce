.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _tutorial:

Tutorial
========

At the beginning we will show you a short example, followed by a complete description of the settings that are possible for a DCE.

We will create a content element with a title, a description and a list of images with a normal view showing the title and the first image and a detail view with the title, the description and the complete list of images.

.. tip::

   jweiland.net has created `two video tutorials <http://jweiland.net/typo3-hosting/service/video-anleitungen/typo3-extensions/dce.html>`_ in German for new users of DCE extension.


Create a DCE
------------

There are two ways to create a new DCE or edit existing ones:

- You can go to DCE backend module, that you will find in the Admin Tools or
.. image:: ../Images/Tutorial/dceBackendModule.png
	:alt: DCE backend module in Admin Tools
- go to the root page (id=0) in list view and create a new "DCE" record. Some actions like deleting or change order of DCEs are currently not possible in backend module, but in list view.
.. image:: ../Images/Tutorial/rootNode.png
	:alt: DCE backend module in Admin Tools

When you have initially created the DCE, enter "Gallery element" for the title.

Now create a new field, choose the type *Element* and enter "Galleryname" as title. You have to enter the variable name in lowerCamelCase. Name it "galleryName". For the configuration choose the option "Simple input field" from the select list box, this fills the text box below the select box with the configuration for an input field.

.. image:: ../Images/Tutorial/fieldGalleryName.png
	:alt: Definition of field GalleryName

Add another new field, choose the type *Element* and enter "Description" as title and "description" as variable name. As configuration select "Full RTE" from the list box.

.. image:: ../Images/Tutorial/fieldDescription.png
	:alt: Definition of field Description

Next create a new field with title "Pictures" and variable name "pictures". As configuration you have to select "File Abstraction Layer" of the type "Inline". In line 8 of the configuration you see the marker *<!-- Name of variable! -->*. Replace thisVariableName with the variable name of this field ("pictures").

.. image:: ../Images/Tutorial/fieldPictures.png
	:alt: Definition of field Pictures

Save the changes. This is necessary for the next steps.

.. image:: ../Images/Tutorial/newDceGalleryElement.png
	:alt: New DCE Gallery Element

Now go to the template tab, you will see a default template in the "Template content (fluid)" text box.

::

	{namespace dce=ArminVieweg\Dce\ViewHelpers}
	<f:layout name="Default" />

	<f:section name="main">
		Your template goes here...
	</f:section>

Replace the text "Your template goes here..." with the fluid code to display the title, the first image and a link to the detail view.

::

	{namespace dce=ArminVieweg\Dce\ViewHelpers}
	<f:layout name="Default" />

	<f:section name="main">
		Gallery: {field.galleryName}<br />
		<br />
		<f:for each="{dce:fal(field:'pictures', contentObject:contentObject)}" as="fileReference" iteration="iterator">
			<f:if condition="{iterator.isFirst}">
				<f:image src="{fileReference.uid}" alt="" treatIdAsReference="1" width="200px"/>
			</f:if>
		</f:for>
		<br />
		<f:link.page pageUid="{page.uid}" additionalParams="{detailUid: '{contentObject.uid}'}">Detail</f:link.page>
		<hr />
	</f:section>

The select box helps you to create the fluid template. When you select an entry from the select box the corresponding code is inserted at the actual cursor position. So it is easy to insert the correct variable names into the fluid template.

Go to the "Detail page" and enable the check box "Enable detail page". In the field "Detail page identifier (get parameter)" you have to enter "detailUid".

The complete "Detail page template (fluid)" should be this:

::

	{namespace dce=ArminVieweg\Dce\ViewHelpers}
	<f:layout name="Default" />

	<f:section name="main">
		Gallery: {field.galleryName}<br />
		Description:<br />
		<f:format.html>{field.description}</f:format.html><br />
		Pictures: <br />
		<f:for each="{dce:fal(field:'pictures', contentObject:contentObject)}" as="fileReference" iteration="iterator">
			<f:image src="{fileReference.uid}" alt="" treatIdAsReference="1" width="200px"/>
		</f:for>
		<br />
		<f:link.page pageUid="{page.uid}">Back</f:link.page>
	</f:section>

After saving your new content element, it is ready to use (even for content editors). It is always located in the CTYPE-dropdown box of new tt_content items. It is also possible to add an option to the create page content wizard, you have to enable the option "Show DCE in content element Wizard" on the wizard tab sheet for this integration.
