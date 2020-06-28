.. include:: ../Includes.txt


.. _additional-informations-detail-pages-with-slugs:


Detail pages with slugs & custom title
--------------------------------------

With DCE 2.6.0 a new feature has been introduced: **Slugs and custom titles for DCE detail pages**

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

  * based on slug expression (without sanitation)
  * you can replace, prepend or append the DCE detail page title


.. _additional-informations-detail-pages-with-slugs-installation:

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


Now, all DCEs with detail page enabled and identifier set to "detailDceUid" get beautified slugs.


Configuration
~~~~~~~~~~~~~

On tab "Detail page" in every DCE you find two new options:

- :ref:`users-manual-detailpage-slug-expression`
- :ref:`users-manual-detailpage-slug-title`


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
