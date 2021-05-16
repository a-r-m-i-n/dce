.. include:: ../Includes.txt


.. _additional-informations-faking-detail-pages:


Faking detail pages
-------------------

With DCE 2.6.0 a new feature has been introduced, which allows you to simulate or fake detail pages,
based on DCE content elements: **Slugs and custom titles for DCE detail pages**

Special Thanks to **Silverback** (https://silverback.st/) who sponsored this feature!

.. image:: Images/sponsor-silverback-logo.png
  :alt: Silverback
  :target: https://silverback.st/


Features
~~~~~~~~

* Slugs for detail pages
* Thanks to Symfony Expression Language, you can use multiple DCE fields as slug
* Field contents used for slug, get sanitized
* Fallback if slugs are not unique (in this case, the uid of content element is appended to slug)
* Modify current page title on detail page

  * based on own title slug expression
  * you can replace, prepend or append the DCE detail page title


.. _additional-informations-faking-detail-pages-installation:

Installation
~~~~~~~~~~~~

Once you've upgraded to DCE 2.6, you just need to apply the following **routing enhancer configuration**
in your sites configuration yaml:

.. code-block:: yaml

    routeEnhancers:
      DceWithDetailpage:  # you are free to choose a unique name here
        type: Simple
        routePath: '/show/{detailDceUid}'  # the given argument, must match with set detailpage_identifier
        aspects:
          detailDceUid:  # Same here
            type: PersistedAliasMapper
            tableName: tt_content
            routeFieldName: tx_dce_slug


Now, all DCEs with detail page enabled and identifier set to **detailDceUid** get beautified slugs.


Configuration
~~~~~~~~~~~~~

On tab "Detail page" in every DCE you find three new options:

- :ref:`users-manual-detailpage-slug-expression`
- :ref:`users-manual-detailpage-title-expression`
- :ref:`users-manual-detailpage-use-title`


How it works
~~~~~~~~~~~~

DCE adds a new column ``tx_dce_slug`` to tt_content table. Only content elements based on DCEs with detail page enabled
and slug expression set, use this new field.

An after-save-hook updates the slug every time:

- a content element get updated (or created)
- the slug expression in DCE changed

  - then, all content elements existing, based on current DCE, get updated
  - slugs get removed, when slug expression is empty

With this static slug set, the routing enhancer configuration works with standard PersistedAliasMapper.


Tips
~~~~

To make the illusion of a detail page perfect, here are some tips for you.

.. _additional-informations-faking-detail-pages-tips-hide:

Hide other content elements
===========================

.. tip::
   Since DCE 2.3, with enabled DCE container feature, you can automatically hide all other container items.
   Check out the option, **"Hide other container items, when detail page is triggered"** in :ref:`DCE Container options <users-manual-dcecontainer-hide-other-content-elements>`.
   This checkbox is only available, when DCE container **and** detail page features are enabled.

If you have more DCEs with detail templates on one page, the template will just switch for one of your content elements.
The other DCE will still be visible and displays the normal template.

When you want to hide all other content elements you need to do some typoscript.


Fluid Styled Content
^^^^^^^^^^^^^^^^^^^^

.. code-block:: typoscript

    [request.getQueryParams()['detailDceUid'] > 0]
        styles.content.get.select.uidInList {
            data = GP:detailDceUid
            intval = 1
        }
    [end]

This small snippet checks if the GET param "detailDceUid" is set. If it is set, it tells the select function in
CSS Styled content, to display just this one content element, by passing the GET parameter value to the query.

Of course, we need to avoid SQL injection, by casting the value to an integer by using (stdWrap.)intval.

This example removes all content element from normal column. If you want to remove all elements but the selected one in
another col, you just need to write eg. "styles.content.getLeft" or ".getRight" or ".getBorder".

**Caution:** This snippet will probably not work, because mostly TYPO3 Integrators uses this
to assign the contents to the template:

::

    page.10 < styles.content.get

The lower than sign (``<``) copies the given value. But with our snippet above we override the original one.
The copy will not be affected. The easiest way would be to use a reference instead:

::

    page.10 =< styles.content.get


Then you are able to change something in CSS Styled Content typoscript, which also affects the output.


Bootstrap Package
^^^^^^^^^^^^^^^^^

In Bootstrap Package fetching content elements happens a bit different. Here, lib.dynamicContent is utilized by
the f:cObject view helper from within the templates, so we modify this lib:

.. code-block:: typoscript

    [request.getQueryParams()['detailDceUid'] > 0]
        lib.dynamicContent.20.select.uidInList {
             override.cObject = TEXT
             override.cObject.data = GP:detailDceUid
             override.cObject.intval = 1
             override.if {
                 value.data = register:colPos
                equals = 0
             }
        }
    [end]


.. _additional-informations-faking-detail-pages-tips-xml:

Include detail pages to XML sitemap (EXT:seo)
=============================================

The following TypoScript snippet, shows an example of how you can configure the XML sitemap in EXT:seo.

.. code-block:: typoscript

    plugin.tx_seo {
      config {
       xmlSitemap {
          sitemaps {
             detailDceUid {
                provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
                config {
                   table = tt_content
                   sortField = sorting
                   lastModifiedField = tstamp
                   additionalWhere = AND tx_dce_slug != ""
                   pid = 1
                   recursive = 0
                   url {
                      pageId = 1
                      fieldToParameterMap {
                         uid = detailDceUid
                      }
                   }
                }
             }
          }
        }
      }
    }

When you changed the detailpage identifier (default: "detailDceUid"), you also need to update it in configuration above.
Also, you need to adjust the options ``pid`` and ``url.pageId``.
