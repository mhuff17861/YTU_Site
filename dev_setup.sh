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

echo ""
echo ""
echo "Hey there! I need your username (non-root) for some crontab and composer installation shenanigans. If you would be so kind as to enter it below, that'd be great! Or you can watch the script crash and burn, that's cool too. I don't judge, fire is fun :)"
read -p "Username: " uname
echo ""

apt update

# Install and setup LAMP stack
apt install lamp-server^
apt install php8.1-xml php8.1-mbstring php8.1-gd php8.1-curl 
a2ensite default-ssl
a2enmod ssl
a2enmod rewrite
a2enmod php8.1

mkdir /var/www
rm -r /var/www/drupal
mkdir $(pwd)/$web_root
ln -s $(pwd)/$web_root /var/www/drupal
chmod 775 /var/www
chmod 775 /var/www/drupal
chown -R $uname:www-data /var/www/drupal
chown -R $uname:www-data $web_root

echo "" >> /etc/apache2/apache2.conf
cat $(pwd)/$apache_dir/apache2-dev.conf >> /etc/apache2/apache2.conf

echo ""
echo "You are about to be prompted to create an SSL cert for localhost. Enter localhost as the host name, enter IP:127.0.0.1 in alternative name(s) field."
read -n1 -s -r -p $'Press any key to continue:' key
echo ""
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

# Composer Setup

EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php
RESULT=$?
rm composer-setup.php

if [ $RESULT = 1 ]
then
	echo "ERROR: Failed to install composer."
	exit 1
fi

sudo -u $uname php composer.phar install -d ./$drupal_dir

chown -R $uname:www-data $webroot
# sudo chmod 640 drupal-src/web/sites/default/settings.php
# Putting this here for as a reminder for the future production setup run
# chmod 640 *.php
# chmod 440 drupal-src/web/sites/default/settings.php
# chmod 770 drupal-src/web/sites/default/files
# chmod 750 drupal-src/web/modules
# chmod 640 drupal-src/web/sites/default/settings.php

# Install node package manager (npm) for scss stuff

curl -fsSL https://fnm.vercel.app/install | bash
source /home/$uname/.bashrc
# Use fnm for install since apt is super out of date
fnm install
fpm install

echo "Setup complete. You should check the README for further instructions, including updating cron and where to put you settings.php file."
