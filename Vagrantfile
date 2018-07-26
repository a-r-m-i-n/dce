# -*- mode: ruby -*-
# vi: set ft=ruby :

if Vagrant::Util::Platform.windows? then
    unless Vagrant.has_plugin?("vagrant-winnfsd")
        system "vagrant plugin install vagrant-winnfsd"
    end
end
unless Vagrant.has_plugin?("vagrant-bindfs")
    system "vagrant plugin install vagrant-bindfs"
end
unless Vagrant.has_plugin?("vagrant-hostmanager")
    system "vagrant plugin install vagrant-hostmanager"
end

Vagrant.configure("2") do |config|
    # Base configuration
    config.vm.box = "ArminVieweg/ubuntu-xenial64-lamp"

    staticIpAddress = "192.168.13.37"
    httpPortForwardingHost = "8080"
    config.vm.hostname = "dce.vagrant"

	config.ssh.insert_key = false

    config.vm.network "private_network", type: "dhcp"
    config.vm.provider "virtualbox" do |vb|
        vb.memory = 4096
        vb.cpus = 2
    end

    # Synchronization
    config.vm.synced_folder ".", "/vagrant", disabled: true
    config.vm.synced_folder ".", "/var/nfs", type: "nfs"

    config.bindfs.bind_folder "/var/nfs", "/vagrant",
        perms: "u=rwX:g=rwX:o=rD"
    config.bindfs.bind_folder "/var/nfs", "/var/www/dce",
        perms: "u=rwX:g=rwX:o=rD", force_user: "vagrant", force_group: "www-data"

    # Hostmanager
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.manage_guest = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = true

    config.vm.define "default" do |node|
        node.vm.network :private_network, ip: staticIpAddress
        node.vm.network :forwarded_port, guest: 80, host: httpPortForwardingHost
    end

    # Provider Scripts
    # PHP Helper
    addComposerRequirement = 'php -r \'$f=json_decode(file_get_contents($argv[1]),true);$f["require"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");\' composer.json'
    addComposerAutoloader = 'php -r \'$f=json_decode(file_get_contents($argv[1]),true);$f["autoload"]["psr-4"][$argv[2]]=$argv[3];file_put_contents($argv[1],json_encode($f,448)."\n");\' composer.json'
    addComposerRepo = 'php -r \'$f=json_decode(file_get_contents($argv[1]),true);$f["repositories"][]=["type"=>$argv[2],"url" =>$argv[3]];file_put_contents($argv[1], json_encode($f,448)."\n");\' composer.json'

    # Run once (install TYPO3 after composer install)
    config.vm.provision "shell", run: "once", name: "install-typo3", inline: <<-SHELL
        cd /var/www/html

        echo "Creating /var/www/html/composer.json..."
        echo "{}" > composer.json

        #{addComposerRepo} "path" "../dce"

        #{addComposerRequirement} "typo3/minimal" "^8.7"
        #{addComposerRequirement} "typo3/cms-belog" "^8.7"
        #{addComposerRequirement} "typo3/cms-beuser" "^8.7"
        #{addComposerRequirement} "typo3/cms-fluid-styled-content" "^8.7"
        #{addComposerRequirement} "typo3/cms-info" "^8.7"
        #{addComposerRequirement} "typo3/cms-info-pagetsconfig" "^8.7"
        #{addComposerRequirement} "typo3/cms-lowlevel" "^8.7"
        #{addComposerRequirement} "typo3/cms-rte-ckeditor" "^8.7"
        #{addComposerRequirement} "typo3/cms-setup" "^8.7"
        #{addComposerRequirement} "typo3/cms-tstemplate" "^8.7"
        #{addComposerRequirement} "typo3/cms-linkvalidator" "^8.7"
        #{addComposerRequirement} "helhum/typo3-console" "^5.2"

        #{addComposerRequirement} "arminvieweg/dce" "*@dev"

        echo "Installing TYPO3 CMS 8 with composer..."

        composer install --no-progress

        vendor/bin/typo3cms install:setup --force --database-user-name "root" --database-user-password "root" --database-host-name "localhost" --database-name "typo3" --database-port "3306" --database-socket "" --admin-user-name "admin" --admin-password "password" --site-name "DCE Dev Environment" --site-setup-type "site" --use-existing-database 0
        vendor/bin/typo3cms cache:flush

        echo "Fixing permissions..."
        chmod 2775 . ./typo3conf ./typo3conf/ext
        chown -R vagrant .
        chgrp -R www-data .

        printf "\ncd /var/www/html" >> /home/vagrant/.bashrc

        echo "Done. Happy coding!"
    SHELL

    # Run once (Add /adminer alias)
    config.vm.provision "shell", run: "once", name:"install-adminer", inline: <<-SHELL
        echo "Installing adminer..."
        composer require vrana/adminer -d /home/vagrant/.composer/ -o --no-progress
        ln -s /home/vagrant/.composer/vendor/vrana/adminer/adminer /var/www/adminer

        echo -e "Alias /adminer \"/var/www/adminer/\"\n<Directory \"/var/www/adminer/\">\nOrder allow,deny\nAllow from all\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/adminer.conf
        a2enconf adminer
        echo "Restarting apache2..."
        service apache2 restart
    SHELL

    # Run always
    config.vm.provision "shell", run: "always", name: "startup", inline: <<-SHELL
        cd ~
        composer self-update --no-progress
        echo "DCE Dev Environment is ready to use: http://dce.vagrant"
    SHELL

end
