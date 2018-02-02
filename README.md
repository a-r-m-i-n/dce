# DCE-Extension for TYPO3

## What is DCE?
DCE is an extension for the CMS TYPO3, which creates easily and fast *dynamic content elements*.
It is an alternative to flexible content elements (FCE) but without need of TemplaVoila (TV).
Based on Extbase and Fluid.


## Documentation
The documentation can be found here: https://docs.typo3.org/typo3cms/extensions/dce/


## How to contrubite?
Just fork this repository and create a pull request to the **master** branch.
Please also describe why you've submitted you patch, if it regards to a ticket, a link would be great.
If you have any questions feel free to contact me.


## Vagrant setup
A Vagrantfile is shipped with DCE. Required plugins are installed on initial `vagrant up`.

Uses the vagrant box:
 [ArminVieweg/ubuntu-xenial64-lamp](https://app.vagrantup.com/ArminVieweg/boxes/ubuntu-xenial64-lamp)

Also the provider scripts, shipped in Vagrantfile, use [typo3_console](https://github.com/helhum/typo3_console)
to install a blank TYPO3 installation (with DCE already installs ;-)).  

Your files are automatically uploaded to `/var/www/html/typo3conf/ext/dce` and `/var/www/html76/typo3conf/ext/dce`.

**Caution! Files are synced!** Deleting files in machine will also delete them on host machine!

### Credentials

* **URL TYPO3 8.7:** http://localhost:8080/typo3
* **URL TYPO3 7.6:** http://localhost:8080/76/typo3

* **For TYPO3:** *admin* / *password* (also install tool password)
* **For Database:** *root* / - (no password set)
* **For SSH:** *vagrant* / *vagrant*

* **TYPO3 8.7 path:** `/var/www/html/` (uses composer, you can update with `composer update`).
* **TYPO3 7.6 path:** `/var/www/html/7.6` (uses composer, you can update with `composer update`).
