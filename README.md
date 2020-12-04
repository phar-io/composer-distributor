# Composer-Distributor

![Build](https://github.com/phar-io/composer-distributor/workflows/Build/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phar-io/composer-distributor/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/phar-io/composer-distributor/?branch=main)

The base-code to create a ComposerPlugin that installs PHAR files
instead of the whole source code of a project.

You have a tool that behaves like a binary? A tool that can be distributed as
PHAR instead of the source-code? A tool that should install the PHAR via
composer?

But you don't want to commit your PHAR into your VCS?

Then create and deploy your signed PHAR file and create a composer plugin using
this base-code and distribute your PHAR file via composer.

## Usage

Create your own Composer-Plugin via

```bash
composer create-project phar-io/mediator /path/to/your/directory/
```

Then you can replace the placeholders within `src/Plugin.php` as well as in the
`composer.json` files and submit it to the VCS of your choice and submit the
plugin to packagist.org.

The last step is to tag and deploy the plugin each time you tag and deploy your
PHAR file. Use the same tag for the plugin that you use for the PHAR-file to be
able to use composers versioning contraints.

Find more information in the README of the base-plugin.
