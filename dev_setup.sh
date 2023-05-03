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

# Global Vars
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

# Install and setup LAMP stack
install_lamp_stack () {
    uname=$1
    apt update
    apt install lamp-server^
    apt install curl php8.1-xml php8.1-mbstring php8.1-gd php8.1-curl unzip
    a2ensite default-ssl
    a2enmod ssl
    a2enmod rewrite
    a2enmod php8.1

    mkdir $(pwd)/$web_root/sites/default/files
    mkdir /var/www
    rm -r /var/www/drupal
    mkdir $(pwd)/$web_root
    ln -s $(pwd)/$web_root /var/www/drupal
    chmod 775 /var/www
    chmod 775 /var/www/drupal
    chown -R $uname:www-data /var/www/drupal
    chown -R $uname:www-data $web_root

    echo "" >> /etc/apache2/apache2.conf  >&2
    cat $(pwd)/$apache_dir/apache2-dev.conf >> /etc/apache2/apache2.conf

    echo ""  >&2
    echo "You are about to be prompted to create an SSL cert for localhost. Enter localhost as the host name, enter IP:127.0.0.1 in alternative name(s) field."  >&2
    read -n1 -s -r -p $'Press any key to continue:' key
    echo ""  >&2
    make-ssl-cert /usr/share/ssl-cert/ssleay.cnf /etc/ssl/certs/localhost.crt

    rm /etc/apache2/sites-available/000-default.conf
    rm /etc/apache2/sites-available/default-ssl.conf
    ln -s $(pwd)/$apache_dir/dev-site.conf /etc/apache2/sites-available/000-default.conf
    ln -s $(pwd)/$apache_dir/ssl-dev.conf /etc/apache2/sites-available/default-ssl.conf

    service apache2 restart
    apt-get install php-ldap
    service apache2 restart

    touch /home/$uname/autostart.sh
    printf '#!/bin/bash\nservice mysql start\nservice apache2 start\n' >> /home/$uname/autostart.sh
    chmod 777 /home/$uname/autostart.sh
    echo '# Allow apache2 and mysql to start without a password' >> /etc/sudoers
    echo '%sudo  ALL=(ALL) NOPASSWD: /usr/sbin/service apache2 *' >> /etc/sudoers
    echo '%sudo  ALL=(ALL) NOPASSWD: /usr/sbin/service mysql *' >> /etc/sudoers
}

# Composer Setup
setup_composer () {
    uname=$1
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
    then
        echo 'ERROR: Invalid installer checksum'  >&2
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
    chmod 770 drupal-src/web/sites/default/files
    chmod 750 drupal-src/web/modules
    chmod 640 drupal-src/web/sites/default/settings.php
}

# sudo chmod 640 drupal-src/web/sites/default/settings.php
# Putting this here for as a reminder for the future production setup run
# chmod 640 *.php
# chmod 440 drupal-src/web/sites/default/settings.php

# Install node package manager (npm) for scss stuff

initial_setup () {
    uname=$(get_username)
    install_lamp_stack $uname
    setup_composer $uname
    setup_permissions $uname
    echo "Setup is almost complete! You should check the README for further instructions, including updating cron and installing fnm."  >&2
}

update_drupal() {
    uname=$(get_username)
    sudo -u $uname php composer.phar update "drupal/core-*" --with-all-dependencies -d ./$drupal_dir
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
