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

Adds stylesheet files in your template or assetic asset (with less filter):

- '%kernel.root_dir%/../vendor/sonatra_ivaynberg/select2/select2.css'
- '@SonatraFormExtensionsBundle/Resources/assetic/less/datetime-picker-build.less'

Adds javascript files in your template or assetic asset:

- '%kernel.root_dir%/../vendor/sonatra_jquery/jquery/jquery.js'
- '%kernel.root_dir%/../vendor/sonatra_ivaynberg/select2/select2.js'
- '%kernel.root_dir%/../vendor/sonatra_ivaynberg/select2/select2_locale_%locale%.js'
- '%kernel.root_dir%/../vendor/sonatra_eightmedia/hammer-js/hammer.js'
- '%kernel.root_dir%/../vendor/sonatra_aterrien/jquery-knob/js/jquery.knob.js'
- '%kernel.root_dir%/../vendor/sonatra_moment/moment/moment.js'
- '%kernel.root_dir%/../vendor/sonatra_moment/moment/lang/%locale%.js'
- '@SonatraFormExtensionsBundle/Resources/assetic/js/datetime-picker.js'
- '@SonatraFormExtensionsBundle/Resources/assetic/js/lang/datetime-picker-%locale%.js'

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
