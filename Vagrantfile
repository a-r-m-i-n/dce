# -*- mode: ruby -*-
# vi: set ft=ruby :

# The following vagrant plugins are required:
# - vagrant-bindfs
# - vagrant-hostmanager
# - vagrant-winnfsd (Windows only)
#
# More infos: http://bit.ly/bionic64-lamp

Vagrant.configure("2") do |config|

    # Extension
    extensionKey = "dce"
    packageName = "arminvieweg/dce"

    # Base configuration
    config.vm.box = "ArminVieweg/ubuntu-bionic64-lamp"

    hostname = extensionKey.gsub("_", "-")
    staticIpAddress = "192.168.103.50"

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
    # Run once
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
        service apache2 restart
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
        vendor/bin/typo3cms cache:flush
    SHELL
    config.vm.provision "shell", run: "once", privileged: true, name: "setup-typo3-8-root", inline: <<-SHELL
        cd /var/www/typo3_8
        chmod 2776 . ./public/typo3conf ./public/typo3conf/ext
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
        vendor/bin/typo3cms cache:flush
    SHELL
    config.vm.provision "shell", run: "once", privileged: true, name: "setup-typo3-9-root", inline: <<-SHELL
        cd /var/www/typo3_9
        chmod 2776 . ./public/typo3conf ./public/typo3conf/ext
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
    SHELL

    # Run always
    config.vm.provision "shell", run: "always", privileged: false, name: "booting", inline: <<-SHELL
        touch /var/www/typo3_8/public/typo3conf/ENABLE_INSTALL_TOOL
        touch /var/www/typo3_9/public/typo3conf/ENABLE_INSTALL_TOOL

        echo "TYPO3 8: http://#{config.vm.hostname}/8  |  http://#{config.vm.hostname}/8/typo3 (BE Login: admin/password)"
        echo "TYPO3 9: http://#{config.vm.hostname}/9  |  http://#{config.vm.hostname}/9/typo3 (Install Tool: password)"
        echo "Adminer: http://#{config.vm.hostname}/adminer (root/root)"
        echo "Happy Coding!"
    SHELL

end
