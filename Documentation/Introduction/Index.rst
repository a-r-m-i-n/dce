.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

With this extension you can easily create new dynamic content elements.

In opposite to native content elements (fluid_styled_content), DCEs are based on FlexForms.
FlexForms allows you to define dynamic content structures, without need to extend tt_content database table.


The Idea Behind DCE
-------------------

**D** ynamic **C** ontent **E** lements (DCE)

The name and basic functionality was inspired  by TemplaVoila‘s FCE (Flexible Content Element) feature.
FCE was one of the last missing major features to replace TemplaVoila entirely.

Content Elements in TYPO3
-------------------------

TYPO3 provides a bunch of content elements (CE) with EXT:fluid_styled_content. You can easily hide unused elements or
fields, but it is not that easy to build new content element types (CTypes).

If you want to extend content elements in TYPO3 natively, you need to write an extension and provide the TCA
configuration. Also you need to provide a hook, if you want to define the look-like of your content element in backend
as well.


Goals of DCE
------------

+ Easy creation of new custom content elements
+ With as many configurable fields as required
+ Flexible and comfortable templating for each CE (using Fluid)
+ An easy learning curve for editors (using customized content elements)
+ and for integrators as well, who start with TYPO3 CMS


DCE Features
------------

Fields and tabs
"""""""""""""""

+ Create as many fields as required
+ Separate multiple fields with tabs (better overview)
+ Full TCA support
+ Helpful dropdown in backend, containing common used TCA snippets
+ Also supports Sections (from TemplaVoila) - but it is **not recommended** to use

Schema loading
""""""""""""""

+ Special handling for group, select and inline fields, which relates to different records
+ Converts comma separated lists of uids to ready to use arrays or objects
+ Uses extbase models (instead of associated arrays, if requested table has one configured)
+ Resolves FAL relations (media) automatically

DCE Container
"""""""""""""

+ Creates **virtual container** around several content elements of the same CType
+ Useful for e.g. sliders
+ You can define a maximum of items per container
+ You can interrupt a container manually, in each content element
+ Containers are visually highlighted in page module (backend)

Detail pages
""""""""""""

+ Use different templates for single DCE instances
+ Controlled by configurable $_GET parameter

Easy templating (using Fluid)
"""""""""""""""""""""""""""""

+ All output of DCE (in FE and BE) runs with Fluid Templating Engine
+ Inline fluid templating support
+ Templates can also get exported to files
+ A helpful dropdown provides defined variables (fields), common Fluid view helpers and all DCE view helpers in backend

Simple Backend View
"""""""""""""""""""

+ Just define the fields you want to preview in backend, by clicking
+ Header and bodytext are separated. The field used for header is also used for the label in e.g. list view
+ Also FAL media can get previewed (in bodytext)
+ Alternatively you can provide a full custom fluid template for backend preview rendering

Miscellaneous
"""""""""""""

+ Each DCE can have its own and custom icon
+ Show/hide new content element in Content Element Wizard (with description)
+ Show custom tt_content (native) fields in your DCE, using a palette displayed above FlexForm
+ Show access/media/categories tab (natively)
+ Control the CType of your content elements by defining an identifier
+ Frontend cache control
+ Direct output option (enabled by default). Bypasses lib.contentElement - significant performance boost
+ TypoScript User Condition (to check if current page contains specific DCE)
+ Support for TYPO3's Import/Export extension
