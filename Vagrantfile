# -*- mode: ruby -*-
# vi: set ft=ruby :

# TYPO3 Extension Box
# Generic Vagrantfile for TYPO3 extension development

# Author: Armin Vieweg <armin@v.ieweg.de>
# Version: 1.0.0
# Date: 2018-09-30

# Support: TYPO3 8, TYPO3 9, Adminer
# Link to this script: http://bit.ly/t3-extension-box

# The following vagrant plugins are required:
# - vagrant-bindfs
# - vagrant-hostmanager
# - vagrant-winnfsd (Windows only)
#
# More infos for VM: http://bit.ly/bionic64-lamp

Vagrant.configure("2") do |config|

    # Adapt these four settings for each new extension/box
    extensionKey = "dce"
    packageName = "t3/dce"
    staticIpAddress = "192.168.103.50"
    extensionRepo = "https://github.com/to/your/git/repo" # Only used as help link

    # Base configuration
    config.vm.box = "ArminVieweg/ubuntu-bionic64-lamp"
    hostname = extensionKey.gsub("_", "-")

    config.vm.hostname = "#{hostname}.local"
    config.hostmanager.aliases = ["www.#{hostname}.local"]

    config.vm.network "private_network", type: "dhcp"
    config.vm.provider "virtualbox" do |vb|
        vb.memory = 4096
        vb.cpus = 2
    end

    # Synchronization
    config.vm.synced_folder ".", "/vagrant", disabled: true
    config.vm.synced_folder ".", "/var/nfs", type: "nfs"

    config.bindfs.bind_folder "/var/nfs", "/var/www/#{extensionKey}",
        perms: "u=rwX:g=rwX:o=rD", force_user: "vagrant", force_group: "www-data"

    # Hostmanager
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.manage_guest = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = true

    config.vm.define "default" do |node|
        node.vm.network :private_network, ip: staticIpAddress
        node.vm.network :forwarded_port, guest: 80, host: 8080, auto_correct: true
    end

    # Provider Scripts
    config.vm.provision "shell", run: "once", privileged: true, name: "init", inline: <<-SHELL
        /home/vagrant/enable-php/7.2.sh
    SHELL

    # Set up SSL certificate for given hostname
    config.vm.provision "shell", run: "once", privileged: true, name: "update-ssl-certificate", inline: <<-SHELL
        openssl genrsa -des3 -passout pass:xxxx -out /tmp/server.pass.key 2048 2>/dev/null
        openssl rsa -passin pass:xxxx -in /tmp/server.pass.key -out /etc/apache2/ssl/apache.key 2>/dev/null
        rm /tmp/server.pass.key
        openssl req -new -key /etc/apache2/ssl/apache.key -out /tmp/server.csr \
        -reqexts SAN -config <(cat /etc/ssl/openssl.cnf \
                <(printf "\n[SAN]\nsubjectAltName=DNS:#{config.vm.hostname},DNS:www.#{config.vm.hostname}")) \
         -subj "/C=DE/ST=North Rhine Westphalia/L=Cologne/O=Dev/OU=Dev/CN=#{config.vm.hostname}" 2>/dev/null
        openssl x509 -req -days 1024 -in /tmp/server.csr -signkey /etc/apache2/ssl/apache.key \
         -out /etc/apache2/ssl/apache.crt 2>/dev/null
        rm /tmp/server.csr
        echo "Created new SSL certificate for Apache, based on hostname: #{config.vm.hostname}"
    SHELL

    # Install TYPO3 8
    config.vm.provision "shell", run: "once", privileged: false, name: "setup-typo3-8", inline: <<-SHELL
        mkdir /var/www/typo3_8
        cd /var/www/typo3_8

        echo {} > composer.json
        composer config repositories.#{extensionKey} path ../#{extensionKey}
        composer config extra.typo3/cms.web-dir public

        echo "Fetching TYPO3 CMS 8 using Composer..."
        composer require t3/cms:"^8.0" #{packageName}:"*@dev" --no-progress --no-suggest --no-interaction

        vendor/bin/typo3cms install:setup --force --database-user-name "root" --database-user-password "root" --database-host-name "localhost" --database-name "typo3_8" --database-port "3306" --database-socket "" --admin-user-name "admin" --admin-password "password" --site-name "EXT:#{extensionKey} Dev Environment" --site-setup-type "site"
        vendor/bin/typo3cms configuration:set BE/debug true
        vendor/bin/typo3cms configuration:set FE/debug true
        vendor/bin/typo3cms configuration:set SYS/displayErrors 1
        vendor/bin/typo3cms configuration:set SYS/systemLogLevel 0
        vendor/bin/typo3cms configuration:set SYS/exceptionalErrors 12290
        vendor/bin/typo3cms configuration:remove SYS/devIPmask
        vendor/bin/typo3cms configuration:set MAIL/transport smtp
        vendor/bin/typo3cms configuration:set MAIL/transport_smtp_server 127.0.0.1:1025
        vendor/bin/typo3cms cache:flush
    SHELL
    config.vm.provision "shell", run: "once", privileged: true, name: "setup-typo3-8-root", inline: <<-SHELL
        cd /var/www/typo3_8
        chmod 2775 . ./public/typo3conf ./public/typo3conf/ext
        chown -R vagrant .
        chgrp -R www-data .

        echo -e "Alias /8 \"/var/www/typo3_8/public/\"\n<Directory \"/var/www/typo3_8/public/\">\nOrder allow,deny\nAllow from all\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/typo3_8.conf
        a2enconf typo3_8
    SHELL

    # Install TYPO3 9
    config.vm.provision "shell", run: "once", privileged: false, name: "setup-typo3-9", inline: <<-SHELL
        mkdir /var/www/typo3_9
        cd /var/www/typo3_9

        echo {} > composer.json
        composer config repositories.#{extensionKey} path ../#{extensionKey}
        composer config extra.typo3/cms.web-dir public

        echo "Fetching TYPO3 CMS 9 using Composer..."
        composer require t3/cms:"^9.0" #{packageName}:"*@dev" --no-progress --no-suggest --no-interaction

        vendor/bin/typo3cms install:setup --force --database-user-name "root" --database-user-password "root" --database-host-name "localhost" --database-name "typo3_9" --database-port "3306" --database-socket "" --admin-user-name "admin" --admin-password "password" --site-name "EXT:#{extensionKey} Dev Environment" --site-setup-type "site"
        vendor/bin/typo3cms configuration:set BE/debug true
        vendor/bin/typo3cms configuration:set FE/debug true
        vendor/bin/typo3cms configuration:set SYS/displayErrors 1
        vendor/bin/typo3cms configuration:set SYS/systemLogLevel 0
        vendor/bin/typo3cms configuration:set SYS/exceptionalErrors 12290
        vendor/bin/typo3cms configuration:set MAIL/transport smtp
        vendor/bin/typo3cms configuration:set MAIL/transport_smtp_server 127.0.0.1:1025
        vendor/bin/typo3cms cache:flush
    SHELL
    config.vm.provision "shell", run: "once", privileged: true, name: "setup-typo3-9-root", inline: <<-SHELL
        cd /var/www/typo3_9
        chmod 2775 . ./public/typo3conf ./public/typo3conf/ext
        chown -R vagrant .
        chgrp -R www-data .

        echo -e "Alias /9 \"/var/www/typo3_9/public/\"\n<Directory \"/var/www/typo3_9/public/\">\nOrder allow,deny\nAllow from all\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/typo3_9.conf
        a2enconf typo3_9
    SHELL

    # Install Adminer
    config.vm.provision "shell", run: "once", privileged: false, name: "setup-adminer", inline: <<-SHELL
        mkdir /var/www/adminer
        cd /var/www/adminer

        echo "Fetching Adminer using Composer..."
        composer require vrana/adminer --no-progress --no-suggest --no-interaction
    SHELL
    config.vm.provision "shell", run: "once", privileged: true, name: "setup-adminer-root", inline: <<-SHELL
        echo -e "Alias /adminer \"/var/www/adminer/vendor/vrana/adminer/adminer/\"\n<Directory \"/var/www/adminer/vendor/vrana/adminer/adminer/\">\nOrder allow,deny\nAllow from all\nRequire all granted\n</Directory>" > /etc/apache2/conf-available/adminer.conf
        a2enconf adminer
    SHELL

    # Finish initial provisioning
    config.vm.provision "shell", run: "once", privileged: true, name: "finish", inline: <<-SHELL
        printf "\ncd /var/www" >> /home/vagrant/.bashrc
        service apache2 restart

        su vagrant
        printf '<!DOCTYPE html><html lang="en"><head> <meta charset="UTF-8"> <title>EXT:#{extensionKey} Dev Environment</title> <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous"></head><body><div class="container mt-5"> <div class="jumbotron mb-0"> <h1 class="display-4">EXT:#{extensionKey} <small class="lead text-nowrap">Dev Environment</small></h1> <hr class="my-5"> <div class="row mt-5"> ' > /var/www/html/index.html
        printf '<div class="col"><div class="card"><div class="card-body"> <h2 class="h5 card-title mb-4">TYPO3 CMS 8.7 LTS</h2> <a href="/8" class="btn btn-secondary">Frontend</a> <a href="/8/typo3" class="btn btn-primary">Backend</a> <a href="/8/typo3/install.php" class="btn btn-danger">Install Tool</a> </div></div></div>' >> /var/www/html/index.html
        printf '<div class="col"><div class="card"><div class="card-body"> <h2 class="h5 card-title mb-4">TYPO3 CMS 9.x</h2> <a href="/9" class="btn btn-secondary">Frontend</a> <a href="/9/typo3" class="btn btn-primary">Backend</a> <a href="/9/typo3/install.php" class="btn btn-danger">Install Tool</a> </div></div></div>' >> /var/www/html/index.html
        printf '<div class="col"><div class="card"><div class="card-body"> <h2 class="h5 card-title mb-4">Tools</h2> <a href="/adminer" class="btn btn-secondary" title="Database tool (root/root)">Adminer</a> <a href="http://deployable-records.vagrant:1080/" class="btn btn-primary" title="Fetches all mails send from inside the VM">Mailcatcher</a> </div></div></div>' >> /var/www/html/index.html
        printf '</div></div><div class="row mt-0"> <div class="col"> <h2 class="h4 mt-5 mb-2">Credentials</h2> <p> Username and password for TYPO3 is: <code>admin</code> / <code>password</code> (also for install tool).<br>For MySQL/MariaDB you can use <code>root</code> / <code>root</code> to login. </p><h2 class="h4 mt-5 mb-3">Links</h2> <ul class="nav flex-column"> <li class="nav-item"> <a class="nav-link" href="#{extensionRepo}">Extension repository</a> </li></ul> </div></div></div></body></html>' >> /var/www/html/index.html
        chown -R vagrant /var/www/html/index.html
        chgrp -R www-data /var/www/html/index.html
    SHELL

    # Run always
    config.vm.provision "shell", run: "always", privileged: false, name: "booting", inline: <<-SHELL
        touch /var/www/typo3_8/public/typo3conf/ENABLE_INSTALL_TOOL
        touch /var/www/typo3_9/public/typo3conf/ENABLE_INSTALL_TOOL

        echo "Overview: http://#{config.vm.hostname}"
        echo " TYPO3 8: http://#{config.vm.hostname}/8  |  http://#{config.vm.hostname}/8/typo3 (BE Login: admin/password)"
        echo " TYPO3 9: http://#{config.vm.hostname}/9  |  http://#{config.vm.hostname}/9/typo3 (Install Tool: password)"
        echo " Adminer: http://#{config.vm.hostname}/adminer (root/root)"
        echo "Happy Coding!"
    SHELL

end
