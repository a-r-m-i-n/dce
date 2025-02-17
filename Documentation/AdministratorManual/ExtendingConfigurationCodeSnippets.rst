.. include:: ../Includes.txt

.. _administrator-manual-extending-configuration-code-snippets:


Extending configuration code snippets
-------------------------------------

Since DCE 3.1 you can extend the code snippets, provided in DCE field configuration field, using event listeners.


Writing the event listener
^^^^^^^^^^^^^^^^^^^^^^^^^^

First, you need to create a new event listener class. The following example is located in your custom extension,
under the path ``Classes/EventListener/ModifyConfigurationTemplatesEventListener.php``.

This example adds one new snippet group (at the beginning) and adds two field configuration code snippets:

.. code-block:: php

    <?php

    namespace Vendor\Extension\EventListener;

    use T3\Dce\Event\ModifyConfigurationTemplateCodeSnippetsEvent;

    class ModifyConfigurationTemplatesEventListener
    {
        public function modify(ModifyConfigurationTemplateCodeSnippetsEvent $event): void
        {
            $templates = $event->getTemplates();

            // Adds new group "Custom templates" to the beginning of template array
            $templates = ['My custom templates' => []] + $templates;

            // Adds new example snippet
            $templates['My custom templates']['My first example'] = <<<XML
    <config>
        <type>input</type>
        <default>Example</default>
    </config>
    XML;

            // Adds another example to existing group
            $templates['TYPE: text']['Another example'] = <<<XML
    <config>
        <type>text</type>
        <!-- Your custom config goes here -->
    </config>
    XML;

            // Sets the modified templates array
            $event->setTemplates($templates);
        }
    }


Register the event listener
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Next, you need to register the event listener.

Like any other event listener, you can register it in the ``Configuration/Services.yaml`` file, inside your extension:

.. code-block:: yaml

    services:
        # ...

        Vendor\Extension\EventListener\ModifyConfigurationTemplatesEventListener:
            tags:
                -   name: event.listener
                    identifier: 'dce-modify-configuration-templates-event-listener'
                    event: T3\Dce\Event\ModifyConfigurationTemplateCodeSnippetsEvent
                    method: 'modify'

.. important::
   You need to flush caches from the install tool, in order to apply your changes to the service container.


Screenshot
^^^^^^^^^^

The example above adds these two new code snippets (one in a new group, one in an existing one):

.. image:: Images/extended-configuration-templates.png
   :alt: Extended Configuration Template Code Snippets
