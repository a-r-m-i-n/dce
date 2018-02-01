# -*- mode: ruby -*-
# vi: set ft=ruby :

# Requires to perform this first once:
#  (windows only) `vagrant plugin install vagrant-winnfsd`
#  `vagrant plugin install vagrant-bindfs`

Vagrant.configure("2") do |config|
  # PHP Helper to modify composer.json
  addComposerRequirement = 'php -r \'$f=json_decode(file_get_contents($argv[1]),true);$f["require"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");\' composer.json'
  addComposerAutoloader = 'php -r \'$f=json_decode(file_get_contents($argv[1]),true);$f["autoload"]["psr-4"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");\' composer.json'

  # Base configuration
  config.vm.box = "ArminVieweg/ubuntu-xenial64-lamp"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", type: "dhcp"

  config.vm.synced_folder ".", "/vagrant", disabled: true
  config.vm.synced_folder ".", "/var/nfs", type: "nfs"

  config.bindfs.bind_folder "/var/nfs", "/vagrant"
  config.bindfs.bind_folder "/var/nfs", "/var/www/html/typo3conf/ext/dce"
  config.bindfs.bind_folder "/var/nfs", "/var/www/html76/typo3conf/ext/dce"

  config.bindfs.default_options = {
    force_user:   "vagrant",
    force_group:  "www-data",
    perms:        "u=rwX:g=rwX:o=rD"
  }

  config.vm.provider "virtualbox" do |vb|
    vb.memory = 4096
    vb.cpus = 2
  end

  # Provider Scripts
  # Run always
  config.vm.provision "shell", run: "always", inline: <<-SHELL
    cd ~
  	sudo composer self-update --no-progress
  SHELL

  config.vm.provision "shell", inline: <<-SHELL
    cd /home/vagrant
    ./enable-php/7.0.sh
  SHELL

  # Run once (install TYPO3 8.7 LTS in /var/www/html)
  config.vm.provision "shell", inline: <<-SHELL
    cd /var/www/html
    echo "{}" > composer.json

    #{addComposerRequirement} "typo3/cms" "^8.7"
    #{addComposerRequirement} "helhum/typo3-console" "^4.5"
    #{addComposerRequirement} "typo3/cms-about" "^8.7"
    #{addComposerRequirement} "typo3/cms-belog" "^8.7"
    #{addComposerRequirement} "typo3/cms-beuser" "^8.7"
    #{addComposerRequirement} "typo3/cms-fluid-styled-content" "^8.7"
    #{addComposerRequirement} "typo3/cms-filelist" "^8.7"
    #{addComposerRequirement} "typo3/cms-info" "^8.7"
    #{addComposerRequirement} "typo3/cms-info-pagetsconfig" "^8.7"
    #{addComposerRequirement} "typo3/cms-lang" "^8.7"
    #{addComposerRequirement} "typo3/cms-lowlevel" "^8.7"
    #{addComposerRequirement} "typo3/cms-reports" "^8.7"
    #{addComposerRequirement} "typo3/cms-rsaauth" "^8.7"
    #{addComposerRequirement} "typo3/cms-rte-ckeditor" "^8.7"
    #{addComposerRequirement} "typo3/cms-saltedpasswords" "^8.7"
    #{addComposerRequirement} "typo3/cms-scheduler" "^8.7"
    #{addComposerRequirement} "typo3/cms-setup" "^8.7"
    #{addComposerRequirement} "typo3/cms-t3editor" "^8.7"
    #{addComposerRequirement} "typo3/cms-tstemplate" "^8.7"
    #{addComposerRequirement} "typo3/cms-viewpage" "^8.7"

    echo "Fetching TYPO3 8.7 using composer..."
    composer update --no-progress -n -q

    echo "Installing TYPO3 8.7 on CLI..."
    vendor/bin/typo3cms install:setup --force --database-user-name "root" --database-user-password "" --database-host-name "localhost" --database-name "typo3" --database-port "3306" --database-socket "" --admin-user-name "admin" --admin-password "password" --site-name "DCE Dev Environment (8.7)" --site-setup-type "site" --use-existing-database 0
    vendor/bin/typo3cms cache:flush

    #{addComposerAutoloader} "ArminVieweg\\\\Dce\\\\" typo3conf/ext/dce/Classes/
    composer dump -o

    php typo3/cli_dispatch.phpsh extbase extension:install dce

    chmod 2775 . ./typo3conf ./typo3conf/ext
    chown -R vagrant .
    chgrp -R www-data .
  SHELL

  # Run once (install TYPO3 7.6 in /var/www/html76)
  config.vm.provision "shell", inline: <<-SHELL
    mkdir /var/www/html76

    echo -e "Alias /76/ \"/var/www/html76/\"\n<Directory \"/var/www/html76/\">\nOrder allow,deny\nAllow from all\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/76-alias.conf
    a2enconf 76-alias
    service apache2 restart

    cd /var/www/html76
    echo "{}" > composer.json

    #{addComposerRequirement} "typo3/cms" "^7.6"
    #{addComposerRequirement} "helhum/typo3-console" "^4.5"
    #{addComposerRequirement} "typo3/cms-about" "^7.6"
    #{addComposerRequirement} "typo3/cms-belog" "^7.6"
    #{addComposerRequirement} "typo3/cms-beuser" "^7.6"
    #{addComposerRequirement} "typo3/cms-fluid-styled-content" "^7.6"
    #{addComposerRequirement} "typo3/cms-filelist" "^7.6"
    #{addComposerRequirement} "typo3/cms-info" "^7.6"
    #{addComposerRequirement} "typo3/cms-info-pagetsconfig" "^7.6"
    #{addComposerRequirement} "typo3/cms-lang" "^7.6"
    #{addComposerRequirement} "typo3/cms-lowlevel" "^7.6"
    #{addComposerRequirement} "typo3/cms-reports" "^7.6"
    #{addComposerRequirement} "typo3/cms-rsaauth" "^7.6"
    #{addComposerRequirement} "typo3/cms-rtehtmlarea" "^7.6"
    #{addComposerRequirement} "typo3/cms-saltedpasswords" "^7.6"
    #{addComposerRequirement} "typo3/cms-scheduler" "^7.6"
    #{addComposerRequirement} "typo3/cms-setup" "^7.6"
    #{addComposerRequirement} "typo3/cms-t3editor" "^7.6"
    #{addComposerRequirement} "typo3/cms-tstemplate" "^7.6"
    #{addComposerRequirement} "typo3/cms-viewpage" "^7.6"
    
    echo "Fetching TYPO3 7.6 using composer..."
    composer update --no-progress -n -q

    echo "Installing TYPO3 7.6 on CLI..."
    vendor/bin/typo3cms  install:setup --force --database-user-name "root" --database-user-password "" --database-host-name "localhost" --database-name "typo3_76" --database-port "3306" --database-socket "" --admin-user-name "admin" --admin-password "password" --site-name "DCE Dev Environment (7.6)" --site-setup-type "site" --use-existing-database 0
    vendor/bin/typo3cms cache:flush

    #{addComposerAutoloader} "ArminVieweg\\\\Dce\\\\" typo3conf/ext/dce/Classes/
    composer dump -o

    php typo3/cli_dispatch.phpsh extbase extension:install dce

    chmod 2775 . ./typo3conf ./typo3conf/ext
    chown -R vagrant .
    chgrp -R www-data .
  SHELL

  # Run once (Add /adminer alias)
  config.vm.provision "shell", inline: <<-SHELL
    echo "Installing adminer..."
    composer require vrana/adminer -d /home/vagrant/.composer/ -o --no-progress
    ln -s /home/vagrant/.composer/vendor/vrana/adminer/adminer /var/www/adminer

    echo -e "Alias /adminer \"/var/www/adminer/\"\n<Directory \"/var/www/adminer/\">\nOrder allow,deny\nAllow from all\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/adminer.conf
    a2enconf adminer
    echo "Restarting apache2..."
    service apache2 restart
  SHELL

end
