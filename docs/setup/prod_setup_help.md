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
