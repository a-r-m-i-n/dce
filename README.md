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

## Deployer tasks
Also DCE provides a few deployer tasks which help to work on DCE within the vagrant machine.

To use these tasks install deployer and the package [jasonlewis/resource-watcher](https://packagist.org/packages/jasonlewis/resource-watcher)
globally using composer:

```
$ composer global require deployer/deployer jasonlewis/resource-watcher
Changed current directory to C:/Users/xxx/AppData/Roaming/Composer
```
Assuming you've added the *C:/Users/xxx/AppData/Roaming/Composer/vendor/bin* directory to your PATH variable, 
you can now use the tasks. Maybe you have to restart all opened CLIs. This is a windows example.

### Call a deployer task
To call a deployer task you just need to type
```
$ cd /path/to/dce
$ dep list
```
First you have to switch to a directory which contain the deploy.php file (vagrant machine is pre-configured).
This command shows you call available commands/tasks with short description.

The most important tasks are:

#### dep set-up
This is useful, when you want a clean fresh installation of DCE.
It executes the tasks **clear** and **upload**.

#### dep clear
Clears the configured `deploy_paths`, which means removing the whole directory.

#### dep upload
Uploads all files except the excluded ones (`exclude_from_upload`).

#### dep watch
File watcher registers every change of any file in your project and uploads them 
(also respecting `exclude_from_upload` option) to remote. Also deleting files. Very convenient.

Demo:

![Deployer File Watcher](https://i.imgur.com/sWOZndn.gif)
