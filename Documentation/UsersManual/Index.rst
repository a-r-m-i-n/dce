.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Users manual
============

Every option you can set while creating or modifying a DCE is described here in detail. Mandatory fields are signed by a yellow triangle. At least these fields have to be filled out.

General
-------

On this tab you can made the general settings. You can decide whether the element is visible or not, give it a meaningful name and define all the fields that are needed.

.. image:: ../Images/UsersManual/newDceEmpty.png
	:alt: Create new DCE, general tab

Hide
^^^^

When the DCE ist hidden, it is not shown as type when a content element is created. It is only shown in the DCE BE module.

Title
^^^^^

This is the name of the DCE, that is also shown in the type selection of a content element.

Fields
^^^^^^

In the fields section you can add a number of different fields that this DCE should contain. You have to add at least one field. There are different field types possible to offer the possibility for creating complex content elements:

- **Element:** This is a field of your new content element, like a text field, a check box, an image or a whole RTE. The composition for this content element is done in the configuration. All field types are supported, which also works in Flexforms.
- **Tab:** This is a tab register. All fields that are defined below this tab are shown in BE on a tab page. You may also rename the first "General" tab by creating a tab as first item.
- **Section:** With a selection you collect elements to groups which belongs together.

Common to all types is the *title* field, where you define a speaking label for the editor. For the type *Tab* there are no more options to define.

Element
+++++++

For the type *Element* you have to define a variable name which is to be used in the fluid template. Variable names have to be written in **lowerCamelCase** and the variable names must be unique inside a single DCE.

The following validators are applied on the variable field:

* The field is required (required)
* The field must not start or end with a space (trim)
* The field can only contain following characters: [a-zA-Z0-9] (is_in)
* The field should not begin with a letter (tx_dce_formevals_noLeadingNumber)
* The field must be correspond to the lowerCamelCase convention (tx_dce_formevals_lowerCamelCase)

The configuration for the fields is stored in Flexform (XML) format. Look for TCEforms in the Flexforms section of the :ref:`T3DataStructure <t3api:t3ds>` documentation to get detailed information for the definition of the field configuration. To make it a bit easier there is a select box with some of the most used possible input field types. If you select one entry the corresponding Flexform XML code is inserted in the configuration input field.

For fields of the types *group*, *select* or *inline*, there are additional configuration attributes available. These attributes are boolean values, that are activated with a value of 1.

**dce_load_schema**

::

	<dce_load_schema>1</dce_load_schema>

When adding a group field and link it with News (by Georg Ringer), than the field contains a comma delimited list with uid's of the selected news. That is not very useful in Fluid templates. But if this attribute is activated than the used table is inspected. If an Extbase model and repository exists for this table, than the repository is instantiated and a *findByUid()* is called for every uid. The complete Extbase models are than taken over to the Fluid template. If the table is not part of an Extbase extension than the corresponding record is loaded from the database and handed over as an associated array.

This function works only with one table, if you configure more tables it dos not work. The `issue 47541`_ covers this behavior in forge.

.. _issue 47541: http://forge.typo3.org/issues/47541

Using the table tt_content and adding content elements which are based on an other DCE, automatically the corresponding DCE will be loaded and filled. In the template of the second DCE the template of the inserted DCE can be called and rendered.

::

	<f:for each="{field.otherDces}" as="othersDce">
		{otherDce.render -> f:format.raw()}
	</f:for>

You need to use the raw viewhelper of fluid, because otherwise the rendered html will be escaped. If you use the f:format.html() viewhelper the curly braces get escaped and variables will not be interpreted anymore.

It is also possible to access directly the value of single fields:

::

	{otherDce.fieldname}

**dce_load_entity_class**

::

	<dce_load_entity_class>VendorName\Extension\Domain\Model\YourModel</dce_load_entity_class>

Uses this class (and its repository) instead of guessing the model class name from table name.

**dce_get_fal_objects**

::

	<dce_get_fal_objects>1</dce_get_fal_objects>

If you have defined a FAL field and this attribute is activated, the value is directly replaced with a TYPO3\CMS\Core\Resource\File object from the repository. Than it is needless to use the FAL ViewHelper in the Fluid template.

**dce_ignore_enablefields**

::

	<dce_ignore_enablefields>1</dce_ignore_enablefields>

Setting this attribute ignores the enablefields of the requested table. All enablefields like deleted, hidden, starttime, endtime were than ignored. This can be used for outputting hidden records.

**dce_enable_autotranslation**

::

	<dce_enable_autotranslation>1</dce_enable_autotranslation>

If you load a page via group field than always this page is loaded, regardless of the language that is just used. Using this attribute shows the translated page if it exists ($GLOBALS['TSFE']->sys_page->getPageOverlay()). That also works with other records, not only with records of the pages table, than getRecordOverlay() will be used.



Section
+++++++

For the type *Section* you have to define a variable name which is to be used in the fluid template. Variable names have to be written in **lowerCamelCase** and the variable names must be unique inside a single DCE.
The *Section fields tag (singular)* contains the name of a single entry. For example: The section contains employees, so this field should be labeled with "Employee". For editors who create a content element based on this DCE a link will appear like "Add new Employee".

.. image:: ../Images/UsersManual/sectionDceElement.png
	:alt: Define a section in the DCE
.. image:: ../Images/UsersManual/sectionDceContent.png
	:alt: Display of the section when adding a DCE content element

For a section you can define as many fields you like. The section fields can contain fields of the types element, tab or section. You can create very complex BE input forms with the combination of the three field types.

Template
--------

On this tab you define the template which is used for displaying the content of the DCE in the FE. You can use the full power of fluid at this place.

Template type
^^^^^^^^^^^^^

File
++++

The option *File* let you choose a file that contains the fluid content that should be used as the template for this DCE. The file name is selected in the "Template file (fluid)" input field. This option makes it possible that you put the templates under revision control due to the fact that the files are stored in the file system.

.. image:: ../Images/UsersManual/newDceEmptyTemplateFile.png
	:alt: Create new DCE, template tab


Inline
++++++

The default template type *Inline* let you directly edit the content of the fluid template inside the text area of the "Template content (fluid)" entry field.

.. image:: ../Images/UsersManual/newDceEmptyTemplate.png
	:alt: Create new DCE, template tab


About fluid templating
^^^^^^^^^^^^^^^^^^^^^^

Lets have a look at the default template code and explain the parts.

::

	{namespace dce=ArminVieweg\Dce\ViewHelpers}
	<f:layout name="Default" />

	<f:section name="main">
		Your template goes here...
	</f:section>

In the first line the namespace for the DCE ViewHelpers is defined. This should not be removed.

With the 2nd line this individual template is included in the main template "Default". You will find the main template "Default" at this place: *typo3conf/ext/dce/Resources/Private/Layouts/Default.html* with this content:

::

	<div class="tx-dce-pi1">
		<f:render section="main" />
	</div>

If you want to get rid of the div container you can use the layout "None" instead of "Default".

The individual template with all your HTML code and CSS classes must be inside the section "main", it should replace the dummy line 5 ("Your template goes here...").

With the select box in the "Template content (fluid)" section you can insert variables and ViewHelpers into the template. The selected variable or ViewHelper is inserted at the current cursor position in the template.

Dynamic parts in fluid template
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

There are several groups inside the select box, which offers you help working with fluid:

- Available variables
- Available base variables
- Famous viewhelpers
- DCE viewhelpers


Available variables
+++++++++++++++++++

This group shows all previously defined variables. You have to save the DCE before new created fields appears in the dropdown field.
All custom variables are available with:

::

	{field.nameOfVariable}


Available base variables
++++++++++++++++++++++++

Besides the custom created variables, they are also some variables existing which are available in all DCEs:

+ ``{dce}`` - The DCE object
+ ``{contentObject}`` -  Content object, this is the DCE instance
+ ``{TSFE}`` -  TSFE object, TypoScriptFrontendController
+ ``{page}`` - Properties of the current page
+ ``{tsSetup}`` - TypoScript setup of the current page

Famous viewhelper
+++++++++++++++++

This group lists often used viewhelpers provided by fluid itself. Detailed information about the Fluid ViewHelper you will find in the official `TYPO3 documentation <http://docs.typo3.org/flow/TYPO3FlowDocumentation/stable/TheDefinitiveGuide/PartV/FluidViewHelperReference.html>`_

* f:count
* f:debug
* f:for
* f:format.crop
* f:format.html
* f:if
* f:image
* f:link.email
* f:link.external
* f:link.page
* f:render

DCE viewhelper
++++++++++++++

DCE also provides on viewhelpers, which may help using the field data in fluid. When you select such DCE viewhelper from dropdown, you'll get an example to the current cursor position of how to use it.

**dce:arrayGetIndex**

Normally you can access array values with: {array.0}, {array.1}, etc. if they have numeric keys. This ViewHelper converts named keys to numeric ones.
Furthermore if you are able to set the index dynamically (i.e. from variable). Index default is 0. Example:
::

	{array -> dce:arrayGetIndex(index:'{iteration.index}')}


**dce:GP**

Gets get or post variables. Never use this ViewHelper for direct output!! This would provoke XSS (Cross site scripting). Example:
::

	{dce:GP(subject:'myCoolGetParameter')}


**dce:explode**

Performs trimExplode (of GeneralUtility) to given string and returns an array. Available options are: *delimiter* (default: ```,```) and *removeEmpty* (```1```). Example:
::

	{string -> dce:explode(delimiter:'\n')}


**dce:fal** (Not supported in sections)

Get file references of FAL. The option contentObject **must** pass the contentObject to the viewhelper, the option field must contain the variable name of field which contains the media. Example:
::

	<f:for each="{dce:fal(field:'thisVariableName', contentObject:contentObject)}" as="fileReference">
		<f:image src="{fileReference.uid}" alt="" treatIdAsReference="1" />
	</f:for>

**dce:format.addcslashes**

Add slashes to a given string using the php function "addcslashes". Available option is: *charlist* (default: ```','```). Example:
::

	<dce:format.addcslashes>{field.myVariable}</dce:format.addcslashes>

**dce:format.tiny**

Removes tabs and line breaks. Example:
::

	<dce:format.tiny>
		Removes tabs and
		linebreaks.
	</dce:format.tiny>

**dce:format.ucfirst**

Convert a string's first character to uppercase. Example:
::

	{variable -> dce:format.ucfirst()}

**dce:format.wrapWithCurlyBraces**

Use this ViewHelper if you want to wrap something with curly braces {}. Available options are: *prepend* and *append*, which add strings before or after the given variable, but inside of curley braces. Example:
::

	<dce:format.wrapWithCurlyBraces prepend="" append="">{field.myVariable}</dce:format.wrapWithCurlyBraces>


**dce:isArray**

Checks if given value is an array. Example:
::

	{variable -> dce:isArray()}

**dce:thisUrl**

Returns url of current page. Available options are: *showHost* (Default: ```1```), *showRequestedUri* (Default: ```1```) and *urlencode* *showRequestedUri* (Default: ```0```). Example:
::

	{dce:thisUrl(showHost:1, showRequestedUri:1, urlencode:0)}

**dce:typolink**

This view helper handles parameter strings using typolink function of TYPO3. It creates the whole <a>-tag. Works perfectly with typolink wizard for input fields. Available option is: *parameter*. Example:
::

	<dce:typolink parameter="{field.url}">This is the link text</dce:typolink>

Since DCE 1.1 it also supports the attributes: class, title and target. They overwrite the value set in parameter.

**dce:typolinkUrl**

Same like *dce:typolink*. Returns just the URL (no <a>-tag). Example:
::

	{dce:typolinkUrl(parameter:'{field.url}')}


Wizard
------

It is possible to add this DCE to the list of the create content element Wizard. This is enabled by default.

.. image:: ../Images/UsersManual/newDceEmptyWizard.png
	:alt: Create new DCE, wizard tab

Show DCE in content element Wizard
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

When this option is enabled than an entry for this DCE is added to the list of the create content element Wizards.

Wizard category
^^^^^^^^^^^^^^^

You can select here in which category of the wizard the DCE should appear. Beside a new category "Dynamic content elements" also the TYPO3 own categories are available.

Wizard description
^^^^^^^^^^^^^^^^^^

This is a short description text which is shown in the wizard and should describe what is the function of this content element. It can be also be left empty.

Wizard icon
^^^^^^^^^^^

The icon that is displayed in front of the entry in the wizard list can be choose from a large amount of available icons.

Wizard custom icon (24x24 pixel)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If none of the included icons fits your imagination you can also upload an icon in the format PNG or GIF with 24x24 pixel.

Detail page
-----------

The detail page can be used to display the fields of the DCE in another manner. For example, if you have many fields defined for your DCE you can display the most important fields with the normal template and the complete amount of fields with the detail template.

The decision to display either the normal template or the detail page template is done by detecting the detail page identifier (get parameter). If it is found and it contains the uid of the actually shown DCE, the content is rendered with the detail page template otherwise the normal template is used.

.. image:: ../Images/UsersManual/newDceEmptyDetailPage.png
	:alt: Create new DCE, detail page tab

If you have more than one DCE on a page, for example a list of Items, and you select the detail view for one of the items than all items are shown as before and the selected item is shown with the detail view. If you select the detail view of another item than the previous selected item is again shown with the normal template.

It is also possible to create an extra page for displaying the detail view where you load the content of the selected DCE via TypoScript.

Example TypoScript for the detail page using TYPO3 6.2 with installed extension bootstrap_package:

::

	lib.dynamicContent{
	  20 = CONTENT
	  20 {
		table = tt_content
		select {
		  pidInList = ###PID where the DCE reside###
		  uidInList.data = GP:###Identificator###
		  where.insertData = 1
		}
	  }
	}

Enable detail page
^^^^^^^^^^^^^^^^^^

To enable the functionality for using a detail page you have to check this option.

Detail page identifier (get parameter)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This is the parameter which must be used in the Get parameter of the URL to enable the display of the detail page. The value must be the uid of the displayed content object. You can use it in a link.page ViewHelper with an additional parameter like this (detailUid is here used as the get parameter): *additionalParams="{detailUid: '{contentObject.uid}'}"*

The {contentObject.uid} is a variable that is available in all Fluid templates. The contentObject is the database entry of the tt_content table which contains our DCE instance. So {contentObject.uid} is the uid of the DCE instance.

Allowed are characters, numbers, minus and underscore [a-zA-Z0-9_\-].

Template type
^^^^^^^^^^^^^

Like the *normal* template you can choose between the inline template code and using a template file.

Detail page template (fluid)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Using the inline template type you have to insert the template code in the same manner like for the normal template (field "Template content (fluid)").

Miscellaneous
-------------

This tab contains all settings which are difficult to put in a category.

.. image:: ../Images/UsersManual/newDceEmptyMisc1b.png
	:alt: Create new DCE, miscellaneous tab


.. image:: ../Images/UsersManual/newDceEmptyMisc2.png
	:alt: Create new DCE, miscellaneous tab

Cache DCE frontend plugin
^^^^^^^^^^^^^^^^^^^^^^^^^

This option activates or deactivates the caching of the DCE in the frontend. Every DCE that should be available in the frontend must be initialized in the localconf.php with calling the method Tx_Extbase_Utility_Extension::configurePlugin(). This option take effect if the showAction of the DceController is cached or non cached.

Preview texts template type
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Just as on the tab Template or Detail page there is the possibility given to choose between the Inline and File template. In this case it affects the following two fields, the so called preview templates.

Preview templates (fluid)
^^^^^^^^^^^^^^^^^^^^^^^^^

The backend of TYPO3 shows in the page module per default the fields header and bodytext of a tt_content entry.

While DCEs and the resulting DCE instances have free configurable fields, this corresponds not with the two fields that are displayed in the backend. That means that there is no possible option to differentiate between different DCE instances.

In order that this is still possible, the fields header and bodytext can be filled automatically when a DCE instance is saved. For this the two templates are used. With it the content of the two fields can be freely defined.

For a new DCE the fields are filled as follows:

In the header show the value of the first field that reside in the DCE. The bodytext shows all DCE fields in an unordered list, without the first field, in the format:

::

	Variable name: Variable value

The variable value is shortened if the length exceeds 50 characters.

At this point you can freely define what should be displayed in the backend. That is very helpful if you have a lot of fields defined for a DCE.

The preview texts are automatically created when a DCE instance is saved and can be used at other places via TypoScript.

Another hook take effect if the DCE itself is changed and saved. In this case all instances of this DCE are updated. This can be a problem if many instances of the DCE exists. For this you have an option in the extension configuration to deactivate the preview auto update.

Disables the "div.csc-default" wrapping
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This option disables the wrapping of the content element with the *<div class="csc-default" />* which can be sometimes necessary.

Enable access tab in backend
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If this option is activated the tab with the access rights is shown in the backend. Here you can define detailed, when the DCE is to be shown and who is allowed to see the DCE.

Enable categories tab in backend
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If this option is activated the tab with category picker is shown in the backend. This option just works since TYPO3 6.0, because the category API has been introduced in this version. In lower TYPO3 versions the checkbox is not available.

DCE palette fields
^^^^^^^^^^^^^^^^^^

This is a comma separated list of fields which should be shown in the head area of this DCE in the backend.

The default value is this: sys_language_uid, l18n_parent, colPos, spaceBefore, spaceAfter, section_frame, hidden

Fluid layout and partial root path
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The two last fields define for all Fluid templates where to find the layouts and the partials. Layouts and partials can be part of Fluid templates and are used to avoid redundancies and keep the code cleaner.

Preview
-------

This tab shows a preview of how this DCE will look in the backend. The preview is at first visible when the DCE is saved at least once.

Because of the temporary database entry without write access, not all functions can be tested in the preview.

.. image:: ../Images/UsersManual/newDceEmptyPreview.png
	:alt: Create new DCE, preview tab
