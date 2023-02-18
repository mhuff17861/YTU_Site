#!/bin/bash
if [ $(id -u) != 0 ]; then
   echo "This script requires root permissions"
   exit 
fi


# intial setup
apt update

# Install and setup LAMP stack
apt install lamp-server^
a2ensite default-ssl
a2enmod ssl
a2enmod rewrite

mkdir /var/www
rm -r /var/www/drupal
ln -s $(pwd)/webroot /var/www/drupal

echo "" >> /etc/apache2/apach2.conf
cat $(pwd)/setup/apache/apache2-dev.conf >> /etc/apache2/apache2.conf

echo ""
echo "You are about to be prompted to create an SSL cert for localhost. Enter localhost as the host name, enter IP:127.0.0.1 in alternative name(s) field."
read -n1 -s -r -p $'Press any key to continue:' key
echo ""
make-ssl-cert /usr/share/ssl-cert/ssleay.cnf /etc/ssl/certs/localhost.crt

rm /etc/apache2/sites-available/000-default.conf
rm /etc/apache2/sites-available/default-ssl.conf
ln -s $(pwd)/setup/apache/dev-site.conf /etc/apache2/sites-available/000-default.conf
ln -s $(pwd)/setup/apache/ssl-dev.conf /etc/apache2/sites-available/default-ssl.conf

service apache2 restart
apt-get install php-ldap
service apache2 restart

touch ~/autostart.sh
printf '#!/bin/bash\nservice mysql start\nservice apache2 start\n' >> ~/autostart.sh
chmod 777 ~/autostart.sh
echo '# Allow apache2, mysql, and sendmail to start without a password' >> /etc/sudoers
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

echo ""
echo ""
echo "Hey there! I need your username (non-root) for some composer installation shenanigans later. If you would be so kind as to enter it below, that'd be great! Or you can watch the script crash and burn, that's cool too. I don't judge, fire is fun :)"
read -p "Username: " uname
echo ""

php composer-setup.php
RESULT=$?
rm composer-setup.php

if [ $RESULT = 1 ]
then
	echo "ERROR: Failed to install composer."
	exit 1
fi


ln -s $(pwd)/setup/drupal/composer.json $(pwd)/webroot/composer.json
ln -s $(pwd)/setup/drupal/composer.lock $(pwd)/webroot/composer.lock
sudo -u $uname php composer.phar install -d ./webroot
