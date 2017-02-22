# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ArminVieweg/trusty64-lamp"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  config.vm.network "forwarded_port", guest: 80, host: 8080
  #config.vm.network "forwarded_port", guest: 443, host: 44300
  #config.vm.network "forwarded_port", guest: 3306, host: 33060

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.56.150"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  config.vm.network "public_network", type: "dhcp"

  # Share an additional folder to the guest VM.
  # config.vm.synced_folder ".", "/var/www"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end

  config.vm.provision "shell", run: "always", inline: <<-SHELL
  	sudo composer self-update
  SHELL


  config.vm.provision "shell", inline: <<-SHELL
    sudo cd /var/www/html
    sudo rm -Rf *
    composer require typo3/cms:"^8.6"
    composer require arminvieweg/dce:"dev-master"

    sudo chown -R vagrant /var/www/html
    sudo chgrp -R www-data /var/www/html
    sudo chmod -R 0755 /var/www/html
    sudo chmod -R g+w /var/www/html

    touch FIRST_INSTALL
  SHELL


# Initial setup
# sudo LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php > /dev/null  2>&1
# sudo apt-get -qq update
# sudo apt-get -qq install apache2
# sudo apt-get -qq install python-software-properties software-properties-common
# sudo apt-get -qq install php7.0
# sudo apt-get -qq install php7.0-mysql php7.0-zip php7.0-gd php7.0-common php7.0-mcrypt php7.0-curl php7.0-xml php7.0-soap
# export DEBIAN_FRONTEND=noninteractive
# sudo -E apt-get -y -q install mysql-server
# sudo apt-get -qq install mysql-client
# sudo apt-get -qq install imagemagick
# sudo apt-get -qq install curl
# sudo apt-get -qq install git
# curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
#
# sudo echo "max_execution_time = 360" >> /etc/php/7.0/apache2/php.ini
# sudo echo "max_input_vars = 1500" >> /etc/php/7.0/apache2/php.ini
# sudo echo "max_execution_time = 360" >> /etc/php/7.0/cli/php.ini
# sudo echo "max_input_vars = 1500" >> /etc/php/7.0/cli/php.ini
# sudo service apache2 restart


end
