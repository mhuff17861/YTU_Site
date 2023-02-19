# Yspilanti Tenant's Union Website

Very early workings of a drupal project. Just wanted the backup/version control for now.

If you want to learn more about Drupal, I highly recommend a read through the [user guide](https://www.drupal.org/docs/user_guide/en/index.html). It's sometimes a bit out of date, but will give you good direction.

## Development Setup

In the root directory, run the following command and respond to its prompts:

`sudo ./dev_setup.sh`

After the setup script is run, run the command below:

`crontab -e`

In the file it opens, add the following line:

`@reboot sh /home/your_username/autostart.sh`

### Help! Something went wrong!

Check out the file `docs/setup/dev_setup_help.md`, it contains help for common issues that occur during development setup.
