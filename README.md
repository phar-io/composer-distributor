# Deploying PHAR files via Composer

![Build](https://github.com/phar-io/composer-distributor/workflows/Build/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phar-io/composer-distributor/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/phar-io/composer-distributor/?branch=main)

If you have a tool that behaves like a binary and want to distribute this tool as a
PHAR file instead of the source-code, but you don't want to give up the convenience
to install your tool via Composer you can use this library to create a Composer-Plugin
that deals with this in no time.

## Motivation

Tools like `PHPUnit`, `PHPStan`, `Psalm` and many others should be installed as PHAR to avoid
version conflicts between your dev tools and your own source code. But since so many developers
just want to use `Composer` to install stuff this project was created to simplify the deployment
of a PHAR file via `Composer`.

## Create your own

To create a Composer-Plugin that installs your PHAR files follow these 3 steps.

### Step 1 - Create your Composer-Plugin repository

```bash
composer create-project phar-io/mediator /path/to/your/directory/
```

The only thing you have to edit is the `distributor.xml` configuration file.
Just change the `packageName` and `<phar>` configuration and you are good to go.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<distributor xmlns="https://phar.io/composer-distributor"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="https://phar.io/xml/distributor/1.0/schema.xsd"
packageName="yournamespace/pluginname"
keyDirectory="keys">

<phar name="pharname"
file="https://github.com/yournamespace/pluginname/releases/download/{{version}}/pharname.phar"
signature="https://github.com/yournamespace/pluginname/releases/download/{{version}}/pharname.phar.asc"/>

</distributor>
```

### Step 2 - Create a Composer package for your plugin on packagist

Create a git repository for your plugin on the platform of your choosing. Then head over to
packagist.org and set up a Composer package for your plugin.

Make sure you add the same tags to your plugin repository as on your source code repository.

### Step 3 - Change your installation instructions

Now everybody can install your PHAR by running the following command.

```bash
composer require --dev yournamespace/yourpluginname
```

