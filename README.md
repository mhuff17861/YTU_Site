# Ypsilanti Tenants' Union Website

*Still in the prototyping stage*

The repository for the Ypsilanti Tenants' Union Drupal website. We've done our
best to build things in modules (under the tenant union package name), so if
you want to fork this project and use it for yourself, you can easily decide what
modules to install or not.

If you want to learn more about Drupal, I highly recommend a read through the [user guide](https://www.drupal.org/docs/user_guide/en/index.html) and/or the
[module development course by Acquia](https://www.youtube.com/watch?v=FTIchVgL8TM&list=PLpVC00PAQQxFNDfiXn6LH1gOLllGS3hhl).

## Dependencies (These should be installed if you follow the installation instructions below)

- The Drupal core installation
- The Drupal Bootstrap 5 theme

## Development Setup

*These instructions are only for developing on a linux machine/vm and have not been tested on WSL.*

First, get your settings.php file and put it in drupal-src/web/sites/default/settings.php. The one for the YTU is not stored here for all the obvious security reasons, contact a web admin if you are a YTU member and need access.

In the project's root directory, run the following command and respond to its prompts:

`sudo ./dev_setup.sh`

After the setup script is run, run the commands below (**Note:** if you run zsh or another shell, I assume you are an advanced enough user to change the commands below to suit your needs):

- `curl -fsSL https://fnm.vercel.app/install | bash`
- `source /home/{your_username}/.bashrc`
- `fnm install`
- `source /home/{your_username}/.bashrc`
- `npm install`

Finally, after node package manager is setup, run the command below to setup cron (the thing that will start the webserver when your pc/vm starts).

`crontab -e`

In the file it opens, add the following line:

`@reboot sh /home/{your_username}/autostart.sh`

## Production Setup

*These instructions are not production-ready and assume you are deploying on a debian based machine.*

First, get your settings.php file and put it in drupal-src/web/sites/default/settings.php. The one for the YTU is not stored here for all the obvious security reasons, contact a web admin if you are a YTU member and need access.

In the project's root directory, run the following command and respond to its prompts:

`sudo ./prod_setup.sh`

After the setup script is run, run the commands below (**Note:** if you run zsh or another shell, I assume you are an advanced enough user to change the commands below to suit your needs):

- `curl -fsSL https://fnm.vercel.app/install | bash`
- `source /home/{your_username}/.bashrc`
- `fnm install`
- `source /home/{your_username}/.bashrc`
- `npm install`

Finally, after node package manager is setup, run the command below to setup cron (the thing that will start the webserver when your pc/vm starts).

`crontab -e`

In the file it opens, add the following line:

`@reboot sh /home/{your_username}/autostart.sh`

### Help! Something went wrong!

Check out the file `docs/setup/dev_setup_help.md` for dev setup and
`docs/setup/prod_setup_help.md` for production setup. They contain help for
common issues that occur during development setup.
