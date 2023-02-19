# Help! Something went wrong!

## Error 403

The setup uses [symlinks](https://en.wikipedia.org/wiki/Symbolic_link) to point apache to the right place, so if you run into trouble make sure the full path is accessible to the [user group](https://www.redhat.com/sysadmin/manage-permissions) www-data. For example, if I put the project in my home directory, apache will not have access unless I change it's permissions. To check if apache can get to the folder, use the following command:

`sudo -u www-data ls -l /var/www/drupal/`

## PHP is not compiled

If you try going to the dev website and it just shows you the PHP code but does not execute it, something went wrong with the installation of php. Try running the following commands.

`sudo apt purge libapache2-mod-php8.1`
`sudo apt install libapache2-mod-php8.1`
`sudo service apache2 restart`

