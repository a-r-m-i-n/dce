# DCE #

## Build status ##

|master|develop|
|---------|----------|
|[![dce Master branch](http://ci.v.ieweg.de/build-status/image/5?branch=master)](http://ci.v.ieweg.de/build-status/view/5?branch=master)|[![dce Develop Branch](http://ci.v.ieweg.de/build-status/image/5?branch=develop)](http://ci.v.ieweg.de/build-status/view/5?branch=develop)|


## Vagrant setup ##

Vagrant is a great way to set up a development machine locally. First you need to install [VirtualBox](https://www.virtualbox.org/wiki/Downloads) and [Vagrant](http://www.vagrantup.com/downloads.html).


### Clone DCE repository ###
First we need the DCE git repository from Bitbucket:

`git clone https://ArminVieweg@bitbucket.org/ArminVieweg/dce.git`

The folder where you clone DCE to, is our *project root*.


### Download vagrant_dce.zip ###

Vagrant and Puphpet is not part of DCE repository. You need to download [vagrant_dce.zip](https://bitbucket.org/ArminVieweg/dce/downloads/vagrant_dce.zip) and extract it to the project root.


### Vagrant up and hosts ###

Perform `vagrant up` on console in project root.

This may take a while. Meanwhile you can add the following ip addresses to your hosts configuration:

```
62.dce.vagrant 137.137.137.137
master.dce.vagrant 137.137.137.137
```


### That's it ###

When console shows `Starting watcher...` the set up is complete. Now you can access the local VM with these urls:

* http://62.dce.vagrant/
* http://master.dce.vagrant/

This VM contains TYPO3 6.2.x and the latest published stable of TYPO3. On each vagrant up the TYPO3 sources will be updated.

The project root will be synced automatically with 62 and master branch. You don't need to set up any deployment stuff in your IDE.

Besides the DCE extension also the following extensions are installed, which help you during development:

* t3deploy
* t3adminer


### Credentials ###

#### TYPO3 ####

To login to TYPO3 use the following credentials:

* Username: **admin**
* Password: **dceisawesome**

Install tool password equals admin password.


#### SSH ####

To connect to VM via SSH, just use the shipped key:

* Username: **root**
* Path to key: **puphpet/files/dot/ssh/id_rsa**