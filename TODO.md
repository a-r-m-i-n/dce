# TODO Compatibility v12

Kudos to

* **Stephan Bauer** for testing and supporting

## v3.0

* [X] ObjectManager usages
* [X] Refactor DoctrineDbal usages
* [X] Backend Module Template
* [X] Replace old CodeMirror implementation with Textarea substitute
  * [X] Fix new CodeEditor script inside IRRE (Configuration Dropdown not working)
* [X] Remove unused DCE Be-ViewHelpers
* [X] Remove DDEV v11 support
* [X] Remove compatibility layer
* [X] Remove ext_tables.php (and move contents to desired location)
* [X] Replace Edit DCE Button Hook with EventListener
* [X] Refactor: Move old Slots to EventListener
* [X] New FAL TCA configuration
  * [X] Remove "{$variable}" usage
  * [X] Fix FAL usage in DcePreviewRenderer
  * [X] Provide Update Wizard to migrate old type:"input" configuration to type:"file" (`\T3\Dce\UpdateWizards\InputFalToFileUpdateWizard`)
* [X] Custom field validators (TCA)
* [X] Custom Wizard Icons
* [X] tt_content Label
* [X] Migrate "makeSearchStringConstraints" hook to `\TYPO3\CMS\Backend\View\Event\ModifyDatabaseQueryForRecordListingEvent`
* [X] Migrate "LiveSearch" XClass to `\TYPO3\CMS\Backend\Search\Event\ModifyQueryForLiveSearchEvent`
* [X] Check Code Snippets (ViewHelpers & Field Configurations)
* [X] Test LinkAnalyser (+ EventListener)
* [X] Check TODOs
* [X] Remove deprecations
* [X] Test EXT:container integration
* [ ] Adjust documentation
* [X] PHP 8.1 adjustments

### Deprecations

* [X] Deprecation: getContentObject


## v3.1

* [ ] Clear DCE Cache, when editing DCE (?)
* [ ] DCE Config Integration (which allows to provide DCE's as YAML files)
* [ ] Implement CodeMirror Editor (ES6) using EXT:t3editor
