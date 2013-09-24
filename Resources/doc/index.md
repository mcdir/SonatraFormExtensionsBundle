Getting Started With Sonatra FormExtensionsBundle
=================================================

## Prerequisites

This version of the bundle requires Symfony 2.3+.

## Installation

Installation is a quick, 2 step process:

1. Download Sonatra FormExtensionsBundle using composer
2. Enable the bundle
3. Configure the bundle (optionnal)

### Step 1: Download Sonatra FormExtensionsBundle using composer

Add Sonatra FormExtensionsBundle in your composer.json:

``` js
{
    "require": {
        "sonatra/form-extensions-bundle": "~1.0"
    },
    "repositories" : [
        { "type" : "composer", "url" : "http://packager.sonatra.com" }
	]
}
```

Or tell composer to download the bundle by running the command:

Note: you must specify the package `ivaynberg/select2` in the composer's repositories section.

``` bash
$ php composer.phar update sonatra/form-extensions-bundle
```

Composer will install the bundle to your project's `vendor/sonatra` directory. 

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Sonatra\Bundle\FormExtensionsBundle\SonatraFormExtensionsBundle(),
    );
}
```

### Step 3: Configure the bundle (optionnal)

You can override the default configuration adding `sonatra_form_extensions` tree in `app/config/config.yml`.
For see the reference of Sonatra Form Extensions Configuration, execute command:

``` bash
$ php app/console config:dump-reference SonatraFormExtensionsBundle 
```

### Next Steps

Now that you have completed the basic installation and configuration of the
Sonatra FormExtensionsBundle, you are ready to learn about usages of the bundle.

The following documents are available:

- [Usage](usage.md)
