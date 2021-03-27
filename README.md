# Deploying PHAR files via Composer

If you have a tool that behaves like a binary and want to distribute this tool as a
PHAR file instead of the source-code but don't want to give up the convenience
to install your tool via Composer you can use this library to create a Composer-Plugin
that deals with this in no time.

## Motivation

Tools like `PHPUnit`, `PHPStan`, `Psalm` and many others are best installed as PHAR to avoid
version conflicts between your dev tools and your own source code. But since so many developers
just want to use `Composer` to install all the tools this project was created to simplify the
deployment of PHAR files via `Composer`.

## Create your own

To create a Composer-Plugin that installs your PHAR files follow these 3 steps.

### Step 1 - Create your Composer-Plugin

The easiest way is to run the following command and follow the instructions.

```bash
composer create-project phar-io/mediator /path/to/your/directory/
```

This will create the plugin in the specified directory.
Make sure the information in your `distibutor.xml` configuration is correct.

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
[packagist.org](https://packagist.org) and set up a Composer package for your plugin.

⚠️ REMINDER ⚠️

Remember to tag your plugin repository as well whenever you release a new version.

### Step 3 - Enjoy your PHAR deployment

Now everybody can install your PHAR by running the following command.

```bash
composer require --dev yournamespace/yourpluginname
```

