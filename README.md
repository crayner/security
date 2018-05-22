# Security
Symfony 4 Security Bundle

Development ONLY
----------------

This package is currently under development and is also a learning tool for myself.  Please use with EXTREME caution.   
I will remove this warning when I am satisfied it is ready for release.

FLEX IS NOT IMPLEMENTED FOR THIS PACKAGE.

Version
-------
0.0.27

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require hillrange/security
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require hillrange/security "~0.0"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/Bundles.php` file of your project:

```php
<?php

return [
    //...
    //
    Hillrange\Security\HillrangeSecurityBundle::class => ['all' => true],
];

```
Features
--------
### User Tracking
Allows you to use the bundle add creation and modification details on every table row by adding
an interface and a trait to your entity file(s).
### Idle Timeout
You need to add a script file to the master template of your app.  The script looks for the idleTimeout parameter to have a value > zero (0).  This value is in munites.
### Group and Role Management
Group and Role management is defined as parameters, allowing huge flexibility.
### Mailer and Security Logger integration
Uses forgotten password email system to manage password management.
### Security Route Flexibility
Routes used in the system are defined as parameters, made available to the system to allow your app to use a different route for any function within the system.
### Basic Forms
Forms are supplied but do not contain any css.  They are not pretty, as it is expected that you will overwrite the twig temples to match your app's look and feel. 

ToDo
----


