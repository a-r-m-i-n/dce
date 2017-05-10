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
A Vagrantfile is shipped with DCE. Open http://127.0.0.1:8080 in your browser after you've performed a 
```
vagrant up
```

On Windows you need to install the vagrant plugin WinNFSd before you can vagrant up:
```
vagrant plugin install vagrant-winnfsd
```

Your files are automatically uploaded to `/var/www/html/typo3conf/ext/dce`.
**Caution! Files are synched!** Deleting files in machine will also delete them on host machine.


The used box [ArminVieweg/trusty64-lamp](https://atlas.hashicorp.com/ArminVieweg/boxes/trusty64-lamp) contains:

- Apache2
- PHP 7.0 & 5.6 *(need to switch manually by changing symlinks in Apache2's mods dir)* 
- mysql-server & mysql-client
- Imagemagick
- Git
- Composer (with auto self-update on vagrant up)
- TYPO3 8.7 LTS
- jigal/**t3adminer** extension (as [composer package](https://packagist.org/packages/jigal/t3adminer))

### Credentials

**For TYPO3:** *admin* / *password* (also install tool password)
**For Database:** *root* / - (no password set)
**For SSH:** *vagrant* / *vagrant*

**TYPO3 path:** `/var/www/html/` (uses composer, you can update with `composer update`).

