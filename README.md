# DCE-Extension for TYPO3 #

## What is DCE? ##
DCE is an extension for the CMS TYPO3, which creates easily and fast *dynamic content elements*. It is an alternative to flexible content elements (FCE) but without need of TemplaVoila (TV). Based on Extbase and Fluid.

## How to contrubite? ##
Just fork this repository and create a pull request to the **master** branch. Please also describe why you've submitted you patch, if it regards to a ticket, a link would be great. If you have any questions feel free to contact me.

## DCE Vagrant setup  ##
The DCE extension provides a vagrant setup which builds a local virtual machine, using the tools VirtualBox and Vagrant.
You can download the latest version here: https://bitbucket.org/ArminVieweg/dce/downloads

### Pre-requirements and installation ###
First you need to download and install [VirtualBox](https://www.virtualbox.org/wiki/Downloads). Windows 10 users should take the most recent test build (5.0.1+). Also [Vagrant](https://www.vagrantup.com/downloads.html) need to be downloaded and installed. After (just to be save) reboot your command line should listen to the command `vagrant`.

The DCE vagrant setup provides hostnames and modifies your local _hosts_ file automatically, if plugin is installed. To install plugin, just enter this to command line:

```
vagrant plugin install vagrant-hostmanager
```

Now you just need to extract the contents of the downloaded zip in a folder of your choice, inside of the DCE extenion. I am always calling this folder *_vagrant/*. Exclude this folder from versioning and from being processed by your IDE.

After you've `vagrant up` first time, it may take 20-30 minutes to create the VM.

The provisioning script is creating the following folder syntax:
```
/var/www
| dce/
| t3sources/
  |_ 7/
  |_ 62/
  |_ devmaster/
| vhosts/
  |_ 7/
  |_ 62/
  |_ devmaster/
```
When you develop locally you need to map the the `/var/www/dce` folder to the local version of the DCE extension. The folder is symlinked from all TYPO3 installations.

Three local domains are shipped and pointing to their targets in */var/www/vhosts*:

* http://7.dce.vagrant | [/typo3](http://7.dce.vagrant/typo3) |  [/typo3/install](http://7.dce.vagrant/typo3/install)
* http://devmaster.dce.vagrant | [/typo3](http://devmaster.dce.vagrant/typo3) |  [/typo3/install](http://devmaster.dce.vagrant/typo3/install)
 
On every boot of VM the provisioner scripts will check the latest version of TYPO3 for each branch. 

The TYPO3 login information is:

* Username: `admin`
* Password: `dceisawesome` (same as in Install Tool)

To connect to local VM via SSH you can enter `vagrant ssh` inside the extracted *_vagrant* folder on command line, or use ssh client with these credentials to connect to:

* Host: `dce.vagrant`
* Key: `/path/to/dce/_vagrant/puphpet/files/dot/ssh/root_id_rsa`

The key is generated when booting VM first time.

## Build status ##

|master|develop|
|---------|----------|
|[![dce Master branch](http://ci.v.ieweg.de/build-status/image/5?branch=master)](http://ci.v.ieweg.de/build-status/view/5?branch=master)|[![dce Develop Branch](http://ci.v.ieweg.de/build-status/image/5?branch=develop)](http://ci.v.ieweg.de/build-status/view/5?branch=develop)|
