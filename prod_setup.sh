#!/bin/bash

# This script sets up the development site for the YTU on your local machine.
# It does so by installing the LAMP stack and using composer to setup drupal.
# It is only tested on Ubuntu 22.04 at the time of this comment
# It will directly modify the following files:
# - /etc/apache2/apache2.conf
# - /etc/sudoers
# - ~/autostart.sh

if [ $(id -u) != 0 ]; then
   echo "This script requires root permissions"
   exit
fi

# intial setup
drupal_dir=drupal-src
web_root=$drupal_dir/web
apache_dir=setup/apache

get_username () {
    echo ""  >&2
    echo ""  >&2
    echo "Hey there! I need your username (non-root) for some crontab and composer installation shenanigans. If you would be so kind as to enter it below, that'd be great! Or you can watch the script crash and burn, that's cool too. I don't judge, fire is fun :)"  >&2
    read -p "Username: " uname
    echo ""  >&2
    echo $uname
}

get_domain () {
    echo "" >&2
    echo "" >&2
    echo "What is the domain you are building for, without www. (e.g. test.com)?" >&2
    read -p "Domain: " domain
    echo "" >&2
}

# Install and setup LAMP stack
install_lamp_stack () {
    uname=$1
    domain=$2
    apt update

    apt install -y lamp-server^
    apt install -y curl php8.1-xml php8.1-mbstring php8.1-gd php8.1-curl
    a2enmod rewrite
    a2enmod php8.1

    # Install Certbot
    apt install -y python3 unzip
    apt install -y certbot python3-certbot-apache

    # Folder Structure Setup
    mkdir /var/www
    rm -r /var/www/drupal
    mkdir $(pwd)/$web_root
    ln -s $(pwd)/$web_root /var/www/drupal
    chmod 775 /var/www
    chmod 775 /var/www/drupal
    chown -R $uname:www-data /var/www/drupal
    chown -R $uname:www-data $web_root

    # Apache configuration
    echo "" >> /etc/apache2/apache2.conf
    cp $(pwd)/$apache_dir/apache2-prod.conf $(pwd)/apache2-prod.conf
    sed -i -e "s/{{domain}}/$domain/g" $(pwd)/apache2-prod.conf
    cat $(pwd)/apache2-prod.conf >> /etc/apache2/apache2.conf
    rm $(pwd)/apache2-prod.conf

    cp /etc/apache2/sites-available/$domain.conf
    sed -i -e "s/{{domain}}/$domain/g" /etc/apache2/sites-available/$domain.conf

    service httpd restart
    service apache2 restart
    apt-get install -y php-ldap
    service apache2 restart

    setup_https

    # sudoers config for auto start
    touch /home/$uname/autostart.sh
    printf '#!/bin/bash\nservice mysql start\nservice apache2 start\n' >> /home/$uname/autostart.sh
    chmod 777 /home/$uname/autostart.sh
    echo '# Allow apache2 and mysql to start without a password' >> /etc/sudoers
    echo '%sudo  ALL=(ALL) NOPASSWD: /usr/sbin/service apache2 *' >> /etc/sudoers
    echo '%sudo  ALL=(ALL) NOPASSWD: /usr/sbin/service mysql *' >> /etc/sudoers
}

# HTTPS setup with certbot
setup_https () {
    uname=$1
    ufw allow 'Apache Full'
    ufw delete allow 'Apache'
    echo ""  >&2
    echo "Printing firewall status:\n"  >&2
    echo ""  >&2
    ufw status

    echo ""  >&2
    echo "You are about to be prompted by certbot in this next section to setup HTTPS. Just a quick pause/alertness check before starting."  >&2
    read -n1 -s -r -p $'Press any key to continue:' key
    echo ""  >&2

    certbot --apache
}

# Composer Setup
setup_composer () {
    uname=$1
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
    then
        echo 'ERROR: Invalid installer checksum' >&2
        rm composer-setup.php
        exit 1
    fi

    php composer-setup.php
    RESULT=$?
    rm composer-setup.php

    if [ $RESULT = 1 ]
    then
        echo "ERROR: Failed to install composer."  >&2
        exit 1
    fi

    sudo -u $uname php composer.phar install -d ./$drupal_dir
}

setup_permissions () {
    uname=$1
    chown -R $uname:www-data $webroot
    chmod -R 770 drupal-src/web/sites/default/files
    chmod -R 750 drupal-src/web/modules
    chmod 750 drupal-src/web/index.php
    chmod 640 *.php
    chmod 440 drupal-src/web/sites/default/settings.php
}

initial_setup () {
    uname=$(get_username)
    domain=$(get_domain)
    install_lamp_stack $uname $domain
    setup_composer $uname
    setup_permissions $uname
    echo "Setup is almost complete! You should check the README for further instructions, including updating cron and installing fnm."  >&2
}

update_drupal() {
    uname=$(get_username)
    sudo -u $uname php composer.phar install --no-dev -d ./$drupal_dir
    echo "Go to [YOUR_DOMAIN]/update.php to complete updates." >&2
    echo "NOTE: Sometimes it will tell you there are no updates, don't stress, you're fine, there are just no DB updates."  >&2
}

if [ $# -eq 0 ]; then
    initial_setup
elif [ "$1" == "--install" ]; then
    initial_setup
elif [ "$1" == "--update" ]; then
    update_drupal
else
    echo "Improper argument(s) provided. Accepts --install or --update (defaults to --install with no args)"
fi
