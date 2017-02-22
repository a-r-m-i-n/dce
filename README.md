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
A Vagrantfile is shipped with DCE. Open http://192.168.0.100 (default IP address) in your browser after you've performed a 
```
vagrant up
```

It uses the [ArminVieweg/trusty64-lamp](https://atlas.hashicorp.com/ArminVieweg/boxes/trusty64-lamp) box 
and has preinstalled:

- Apache2
- PHP 7 & 5.6 *(need to switch manually by changing symlinks in Apache2's mods dir)* 
- mysql-server & mysql-client
- Imagemagick
- Git
- Composer (with auto self-update on vagrant up)
- TYPO3 8.x
- DCE dev-master
