.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

With this extension you can easily create dynamic content elements. It is an alternative to flexible content elements (FCE) but without the need of TemplaVoila (TV). In close collaboration with fluid it is possible to create complex things quite easily.

The Idea Behind DCE
-------------------

**D** ynamic **C** ontent **E** lements (DCE)

The name and basic functionality was inspired  by TemplaVoila‘s FCE feature. FCE was one of the last missing major features to replace TemplaVoila.

Content Elements in TYPO3
^^^^^^^^^^^^^^^^^^^^^^^^^

TYPO3 has just a few basic content elements (CE). There is no possibility to create new CE without developing new extensions  or massively rearranging TCA. Layout or section frame attributes can be abused for new CE, but this is not easy to use for editors! Also the abuse of RTE fields (using tables, etc.) has too much flexibility for editors. Laying out CE in frontend may be a PITA, because of TypoScript (CSS styled content).

Goals of DCE
^^^^^^^^^^^^

+ Easy creation of new custom content elements
+ With as many configurable fields as required
+ Flexible and comfortable templating for each CE (using Fluid)
+ An easier learning curve for editors

DCE Features
^^^^^^^^^^^^

Fields and tabs
"""""""""""""""

+ Create as many fields as required
+ Separate multiple fields with tabs (better overview)
+ Name and configure them like you want

Schema loading
""""""""""""""

+ Special handling for group, select and inline fields
+ Convert comma separated lists of uids to ready to use arrays
+ Uses extbase models (instead of associated arrays, if requested table has one configured)

Sections
""""""""

+ Uses TemplaVoila implementation in TYPO3 core
+ Create as many groups of fields as you want

Detail pages
""""""""""""

+ Use different templates for single DCE instances
+ Controlled by $_GET parameters

Easy templating (using Fluid)
"""""""""""""""""""""""""""""

+ All output of DCE (in FE and BE) runs with Fluid Templating Engine
+ Even cached localconf- and ext_tables php files uses Fluid
+ Fluid gives you all flexibility you need for laying out content elements

Backend preview templates
"""""""""""""""""""""""""

+ Define tt_content header and bodytext attribute for each DCE individually. Of course, using Fluid

DCE user conditions
"""""""""""""""""""

+ Check if the current page contains a specified DCE
+ Add CSS and JS only on pages where you need it
+ Modify any TypoScript configuration you want

Comfortable import/export
"""""""""""""""""""""""""

+ Quick import and export of DCEs and DCE fields
+ Uses ImpExp extension in TYPO3 core
+ DCE instances (tt_content) have relation to DCE
+ When importing DCE and DCE instances, the uid of DCE will be automatically updated if uid is already assigned on target system

