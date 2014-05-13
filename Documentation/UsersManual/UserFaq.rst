.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-faq:

FAQ
===


**How to get the first image of a FAL image list?**

::

    <f:for each="{dce:fal(field:'picture', contentObject:contentObject)}" as="fileReference" iteration="iterator">
        <f:if condition="{iterator.isFirst}">
            <f:image src="{fileReference.uid}" alt="" treatIdAsReference="1" width="200px"/>
        </f:if>
    </f:for>

The loop run through the complete FAL image list, but only the first element is issued.


**How to readout an image in a Fluid template and give it a click enlarge function?**

If you have defined a field in DCE where you can select images than you can access the file name in the Fluid template. The location where the image is stored is also defined in the TCA, which is mostly something like *uploads/pics*.

In the Fluid template you can write following:

::

	<a href="{f:uri.image(src:'uploads/pics/{field.yourPicture}')}" class="whatEverYourCssLibraryWantHere">
		<f:image src="uploads/pics/{field.yourPicture}" alt="Thumbnail" maxWidth="100" maxHeight="100" />
	</a>

With the f:image ViewHelper a thumbnail of the image, that should be shown, is issued. TYPO3 creates an image with a reduced size and stores it in *typo3temp/pics/*.

In the href parameter of the link, which should show the big version of the image when it is clicked, you use the f:uri.image ViewHelper. In principle it is the same as the f:image ViewHelper, but instead of an image only a URL is created. The benefit of using this ViewHelper is that you also can use height and width to limit the size of the big image (e.g. 800x600).


**How to get the fields of a section?**

You can access the fields of a section by iterating over the section elements:

::

	<f:for each="{field.sectionField}" as="sectionField">
		{sectionField.text}<br />
	</f:for>


**How to translate fields in the BE?**

The translation of fields in the backend is planned for version 2.0 of the extension.

`Backend module: Translatable fields <http://forge.typo3.org/issues/58540>`_


**How to render the content of an RTE field?**

You have to enclose the RTE field with the format.html ViewHelper to get the HTML tags of the RTE rendered.

::

	<f:format.html>{field.rteField}</f:format.html>


**How to access variables of other DCE elements?**

You can access directly the TypoScript with {tsSetup.lib.xyz.value} .


**How to output an image that is selected via FAL in the BE?**

If you choose "Type:inline File Absraction Layer" as element for FAL images it is important to write the field name in the FlexForm: *<fieldname>rightImage</fieldname>*. The same name must be written in the Fluid template:

::

	<f:for each="{dce:fal(field:'rightImage', contentObject:contentObject)}" as="fileReference">
		<f:image src="{fileReference.uid}" alt="" treatIdAsReference="1" />
	</f:for>

This outputs all images.


**How to create a dynamic content like an accordion?**

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

	<div class="accordion"><f:render section="rowSection" /></div>


**How to link to the detail page?**

The link to changeover to the detail page looks like this:

::

	<f:link.page pageUid="{page.uid}" additionalParams="{detailUid: '{contentObject.uid}'}">Detail</f:link.page>

Where detailUid is the value of the field "Detail page identifier (get parameter)" you have set on the "Detail page" tab.



