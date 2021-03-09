.. include:: ../Includes.txt


.. _additional-informations-dce-expression-utility:


DCE Expression Utility
----------------------

In DCE you have several places where you can use Symfony Expressions, to define the output.

* Slug generation for DCE detail pages (see :ref:`users-manual-detailpage-slug-expression`)
* :ref:`users-manual-backendtemplate_header_expression`


Usage
=====

Everytime you can define a Symfony expression in DCE you have the following variables available:

* ``dce`` (object)
* ``contentObject`` (array)
* All field variables you have defined

When you have e.g. a field with variable "title", you can simply enter ``title``.
Then the value of this field is used.

To concatenate multiple fields you can use the tilde sign (``~``) like this:

::

    firstName ~ ' ' ~ lastName

When you want to add some custom strings (like the space between firstname and lastname), those have to be wrapped with
single or double quotes.


The whole DCE object (``dce``) and the row of tt_content item (``contentObject``) are also available:

::

    dce.getTitle() ~ ' ' ~ contentObject['uid']


.. caution::
   Objects (like ``dce``) and Arrays (like ``contentObject``) got a different syntax to access sub-properties,
   as you can see in previous expression example.


Helpful Links
=============

* `Symfony Expression Syntax (Docs) <https://symfony.com/doc/current/components/expression_language/syntax.html>`_
