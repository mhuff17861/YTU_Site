# Yspilanti Tenant's Union Website

Very early workings of a drupal project. Just wanted the backup/version control for now.

If you want to learn more about Drupal, I highly recommend a read through the [user guide](https://www.drupal.org/docs/user_guide/en/index.html). It's sometimes a bit out of date, but will give you good direction.

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

### Help! Something went wrong!

Check out the file `docs/setup/dev_setup_help.md`, it contains help for common issues that occur during development setup.
