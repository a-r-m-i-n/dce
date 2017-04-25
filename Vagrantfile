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

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  # config.vm.network "private_network", ip: "190.168.0.244"
  config.vm.network "private_network", type: "dhcp"

  # Provider-specific configuration so you can fine-tune various
  config.vm.provider "virtualbox" do |vb|
    vb.memory = 4096
    vb.cpus = 2
  end

  # Run once
  config.vm.provision "shell", inline: <<-SHELL
    cd /var/www/html
    php -r '$f=json_decode(file_get_contents($argv[1]),true);$f["autoload"]["psr-4"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");' composer.json "ArminVieweg\\\\Dce\\\\" typo3conf/ext/dce/Classes/
    composer dump -o
    php typo3/cli_dispatch.phpsh extbase extension:install dce
  SHELL

  # Run always
  config.vm.provision "shell", run: "always", inline: <<-SHELL
    cd ~
  	sudo composer self-update --no-progress
  SHELL

end
