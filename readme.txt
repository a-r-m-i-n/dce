This is the very first beta version of the extension "DCE".

If you found bugs or got some feature requests, write me a mail to: armin@v.ieweg.de
or report it to http://forge.typo3.org/projects/extension-dce


Quick start:
------------
To create a new DCE go to the root page (id=0) in TYPO3 backend (needs administrator privileges) and
create a new "DCE" record.

Enter title and create some fields. Save and goto template tab, you'll get a list of available variables.

After saving your new content element, it is ready to use (even for content editors). If you enabled the
wizard integration you'll find the new element there, otherwise it is located in the CTYPE-dropdown box.

Have fun!



Open tasks:
-----------
[ ] Write the documentation (which will be located in wiki of forge project)
[ ] Add more configuration templates (located in EXT:dce/Resource/Public/CodeSnippets/ConfigurationTemplates/)
[ ] Add a validator, which checks unique variable names for each DCE (not for pid or whole pagetree)
[ ] Make some code improvements/refactorings