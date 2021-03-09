.. include:: ../Includes.txt


.. _additional-informations-dce-viewhelper:


DCE ViewHelper
--------------

Since DCE 2.6, DCE ships a DCE view helper, which allows you to fetch a DCE instance of content element by uid,
in **any** Fluid template.

Because the DCE extension registred the global Fluid namespace "dce", you can instantly use the DceViewHelper,
e.g. in your website Fluid template:

.. code-block:: html

    <dce:dce uid="123">
        {dce.render}
    </dce:dce>

    <dce:dce uid="123">
        {dce.renderDetailpage}
    </dce:dce>

    <dce:dce uid="456">
        {dce.get.lastName}, {dce.get.firstName}
    </dce:dce>


Within the ``dce:dce`` view helper tag, you can

- render the whole DCE (detailpage) template
- or define an own template for this particular usage of the content element

The **uid** argument is mandatory.


Available variables
~~~~~~~~~~~~~~~~~~~

Inside of the DCE view helper you can access the following variables:

- ``{dce}``
- ``{contentObject}``
- ``{fields}`` and ``{field}``
