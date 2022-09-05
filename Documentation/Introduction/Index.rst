.. include:: ../Includes.txt

.. _introduction:


Introduction
============


What does it do?
----------------

With this extension, you can easily create new dynamic content elements.

In opposite to native content elements (fluid_styled_content), DCEs are based on FlexForms.
FlexForms allows you to define dynamic content structures, without the need to extend tt_content database table.

.. note::
   tt_content is the database table, which stores all content elements.

.. note::
   FlexForms allows defining dynamic forms, which get stored as XML in tt_content column "pi_flexform".
   This makes it hard to perform queries on fields defined in your DCE content elements, unless you've enabled
   :ref:`TCA mapping <additional-informations-tca-mapping>`_.


Content Elements in TYPO3
-------------------------

TYPO3 itself provides a bunch of content elements (CE) with EXT:fluid_styled_content.
You can easily hide unused elements or fields, but it is not that easy to build new content element types (CTypes).

If you want to extend content elements in TYPO3 natively, you need to write an extension and provide the custom TCA
configuration on your own. Also, you need to provide a hook or implement a preview renderer, if you want to define
the look-like of your content element in the backend, too.

Goals of DCE
------------

+ Make it easy to create new custom content elements
+ With as many configurable fields as you want
+ Flexible and comfortable templating for each CE (using Fluid)
+ A lower learning curve for editors (using customized content elements)
+ and for integrators as well, who start with TYPO3 CMS


DCE Features
------------

+ Over 10 years of experience
+ `Hundreds of installations per day <https://packagist.org/packages/t3/dce/stats>`_ in TYPO3 CMS projects


Fields and tabs
"""""""""""""""

+ Create as many fields as required
+ Separate multiple fields with tabs (better overview)
+ Full TCA support
+ Helpful dropdown in the backend, containing common used TCA snippets
+ TCA Mapping feature, which allows you to map single values from FlexForm XML to an existing or new column in tt_content
+ Custom FlexForm config option "dce_skip_translation", brings ``l10n_mode => 'exclude'`` behaviour (TCA only feature) to FlexForms

Schema loading
""""""""""""""

+ More custom FlexForm config options, starting with "dce_" to fetch objects instead of uid lists
+ Special handling for ``group``, ``select`` and ``inline`` fields, which relates to different records
+ Converts comma-separated lists of uids to ready to use associative arrays or objects/models
+ Uses Extbase models (instead of associated arrays, if the requested table has one configured)
+ Resolves FAL relations (media) automatically
+ Resolves assigned categories (``sys_category``) automatically
+ Resolves file collections (``sys_file_collection``) automatically

DCE Container
"""""""""""""

+ Creates **virtual container** around several content elements of the same CType
+ Fluid template of container can get adjusted
+ Useful for e.g. sliders
+ You can define a maximum of items per container
+ You can interrupt a container manually, in each content element
+ Containers are visually highlighted in page module (backend)
+ Used colors to identify several containers are configurable (via PageTS)
+ When container items also got detail pages enabled, you can hide all other content elements in container,
  when the detail page is active

Detail pages
""""""""""""

+ Use a different templates for single DCE instances
+ Controlled by configurable ``$_GET`` parameter
+ Fake detail pages, thanks to **slugs** you can configure with **Symfony Expressions**
+ Also the detail page title can get adjusted with an expression
+ When **DCE container** is also enabled, content elements which' detail page is **not** triggered, on current page,
  can get hidden automatically
+ DCE provides a custom XML Sitemap data provider (EXT:seo) to index detail pages of DCEs, like regular pages

Easy templating (using Fluid)
"""""""""""""""""""""""""""""

+ All output of DCE (in FE and BE) runs with Fluid Templating Engine
+ Inline Fluid templating support (code mirror editor)
+ Templates can also get exported to files
+ A helpful dropdown provides defined variables (fields), common Fluid view helpers and all DCE view helpers in the backend
+ DCE ViewHelper which allows you to fetch a content element based on DCE in any Fluid template: ``<dce:dce uid="1">{field.title}</dce:dce>``

Simple Backend View
"""""""""""""""""""

+ Just define the fields you want to preview in the backend, by clicking
+ Header and bodytext are separated. The field used for header is also used for the label in e.g. list view
+ Also FAL media can get previewed (in bodytext)
+ Alternatively you can provide a full custom Fluid template for backend preview rendering

More
""""

+ **Control the CType** of your content elements by defining an **identifier**
+ Each DCE can have its **own icon** representation. You can also provide a custom icon.
+ Configure new content element in **New Content Element Wizard** (with description)
+ Display custom tt_content (native) fields in your DCE, using a **palette** displayed above the FlexForm fields
+ Show access, media and categories tab (natively)
+ Frontend cache control
+ **Direct output** option (enabled by default). Bypasses lib.contentElement - significant performance boost
+ TypoScript **User Condition** (to check if current page contains specific DCE)
+ Support for TYPO3's Import/Export extension
+ EXT:container and EXT:gridelements support
+ Complete documentation
