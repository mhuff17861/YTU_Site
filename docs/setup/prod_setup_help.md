# Help! Something went Wrong!

## Error Message `Could not infer shell type. Please set up manually.` on curl command

Sometimes spun up servers don't like to automatically put you in a NPM compatible
environment. It is likely that either your SHELL or BASH_VERSION environment
variable are not properly set. Check with the following commands

- `echo $SHELL` Should return /bin/bash (or your preferred shell)
- `echo ${SHELL_NAME}_VERSION` Should return anything

If shell is not set and you know your preferred shell, using the following command:

`export SHELL={SHELL_NAME}`

If the shell version is not set, you likely need to install it.

## My CSS/JS is not loading on the live site

If I may take a moment to make the editor present... **DRUPAL WHY!?**

Drupal likes to aggregate CSS and JS files by default, which while faster can cause *all sorts of bugs*. Login as an admin and go to
configuration->performance and uncheck the boxes that that say "Aggregate CSS
files" and "Aggregate JS files" respectively. If you want to know how to keep
the aggregation, there *is not* currently an answer for that in these docs. You
will have to look elsewhere.
