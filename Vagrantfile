# -*- mode: ruby -*-
# vi: set ft=ruby :

# Requires to perform this first: `vagrant plugin install vagrant-winnfsd`

Vagrant.configure("2") do |config|
  config.vm.box = "ArminVieweg/trusty64-lamp-typo3"

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 443, host: 44300
  # config.vm.network "forwarded_port", guest: 3306, host: 33060
  config.vm.network "forwarded_port", guest: 80, host: 8080

  # Share an additional folder to the guest VM.
  # config.vm.synced_folder ".", "/var/www"
  config.vm.synced_folder ".", "/var/www/html/typo3conf/ext/dce", type: "nfs"
  config.vm.synced_folder ".", "/var/www/html76/typo3conf/ext/dce", type: "nfs"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  # config.vm.network "private_network", ip: "190.168.0.244"
  config.vm.network "private_network", type: "dhcp"

  # Provider-specific configuration so you can fine-tune various
  config.vm.provider "virtualbox" do |vb|
    vb.memory = 4096
    vb.cpus = 2
  end

  # Run once (install DCE in /var/www/html)
  config.vm.provision "shell", inline: <<-SHELL
    cd /var/www/html
    chmod 2775 . ./typo3conf ./typo3conf/ext

    php -r '$f=json_decode(file_get_contents($argv[1]),true);$f["autoload"]["psr-4"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");' composer.json "ArminVieweg\\\\Dce\\\\" typo3conf/ext/dce/Classes/
    composer dump -o
    php typo3/cli_dispatch.phpsh extbase extension:install dce
  SHELL

  # Run once (add TYPO3 7.6 in /var/www/html76)
  config.vm.provision "shell", inline: <<-SHELL
    mkdir /var/www/html76

    echo -e "Alias /76/ \"/var/www/html76/\"\n<Directory \"/var/www/html76/\">\nOrder allow,deny\nAllow from all\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/76-alias.conf
    a2enconf 76-alias
    service apache2 restart

    cd /var/www/html76
    echo "{}" > composer.json

    php -r '$f=json_decode(file_get_contents($argv[1]),true);$f["require"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");' composer.json "typo3/cms" "^7.6"
    php -r '$f=json_decode(file_get_contents($argv[1]),true);$f["require"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");' composer.json "helhum/typo3-console" "^4.5"
    composer update --no-progress -n -q

    vendor/bin/typo3cms  install:setup --force --database-user-name "root" --database-user-password "" --database-host-name "localhost" --database-name "typo3_76" --database-port "3306" --database-socket "" --admin-user-name "admin" --admin-password "password" --site-name "T3ddy Dev Environment" --site-setup-type "site" --use-existing-database 0
    vendor/bin/typo3cms cache:flush

    php -r '$f=json_decode(file_get_contents($argv[1]),true);$f["autoload"]["psr-4"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");' composer.json "ArminVieweg\\\\Dce\\\\" typo3conf/ext/dce/Classes/
    composer dump -o
    php typo3/cli_dispatch.phpsh extbase extension:install dce

    chmod 2775 . ./typo3conf ./typo3conf/ext
    chown -R vagrant .
    chgrp -R www-data .
  SHELL

  # Run always
  config.vm.provision "shell", run: "always", inline: <<-SHELL
    cd ~
  	sudo composer self-update --no-progress
  SHELL

end
