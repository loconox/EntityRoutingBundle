LoconoxEntityRoutingBundle
==========================

[![Build Status](https://travis-ci.org/loconox/EntityRoutingBundle.svg?branch=master)](https://travis-ci.org/loconox/EntityRoutingBundle)

Topos
-----

### Problem


Lets say that you want these routes in your application:
```yaml
product-page:
    path: /{category}/{product}

user-page:
    path: /{group}/{user}
```

The Symfony router will build regex based on your route and try to match incoming request with these regex.

But regex for two different pages maybe the same, `/car/tesla` will match `product-page` route but `/scientist/tesla` will also match `product-page` route.

### Solution


This bundle allow you to map route parameters to entity slug. For instance, the router will request your database and will not found any `Category` with `scientist` as slug. So it will go to the `user-page` route and check if any `Group` with `scientist` as slug exists.


Installation
------------

### Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require loconox/entity-routing-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
<?php
// config/bundles.php

return [
    // ...
    \Loconox\EntityRoutingBundle\LoconoxEntityRoutingBundle::class => ['all' => true]
];
```

Requirements
------------

### PCRE

    $ ./configure --enable-utf --enable-pcre16 --enable-pcre32 --prefix=/opt/local --enable-unicode-properties

PCRE have to be compatible with UTF-8 encoding.
The command above should print at least "UTF-8 support" and "Unicode properties support".

    $ pcretest -C

Configuration
-------------

### Cmf Routing

Configure the chain routing:

```yaml
# config/packages/cmf_routing.yaml
cmf_routing:
  chain:
    routers_by_id:
      loconox_entity_routing.router: 100
      router.default: 75
```

### Routing bundle

Optionally, you can specify a different entity manager for your slug to be store and a specific class.

```yaml
# config/packages/loconox_entity_routing.yaml
loconox_entity_routing:
  entity_manager: default
  class:
    slug: 'Loconox\EntityRoutingBundle\Entity\Slug'
  router:
    resource: 'kernel::loadRoutes'
    type: 'service'

```

Usage
-----

See [usage guide](Resources/doc/Usage.md).
