# TODO Compatibility v12

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
* [ ] Migrate "makeSearchStringConstraints" hook to `\TYPO3\CMS\Backend\View\Event\ModifyDatabaseQueryForRecordListingEvent`
* [ ] Migrate "LiveSearch" XClass to `\TYPO3\CMS\Backend\Search\Event\ModifyQueryForLiveSearchEvent`
* [ ] Check Code Snippets (ViewHelpers & Field Configurations)
* [ ] Test EXT:container integration
* [X] Test LinkAnalyser (+ EventListener)
* [ ] Adjust documentation
* [ ] Check TODOs
* [X] Remove deprecations
* [ ] PHP 8.1 adjustments

### Deprecations

* [ ] Deprecation: getContentObject


## v3.1

* [ ] Clear DCE Cache, when editing DCE (?)
* [ ] DCE Config Integration (which allows to provide DCE's as YAML files)
* [ ] Implement CodeMirror Editor (ES6) using EXT:t3editor
