Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require aescarcha/business "~1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Install Requirements
-------------------------

    composer require friendsofsymfony/rest-bundle
    composer require jms/serializer-bundle
    composer require nelmio/api-doc-bundle
    composer require friendsofsymfony/user-bundle
    composer require aescarcha/user-bundle dev-master
    composer require league/fractal


Step 3: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Aescarcha\UserBundle\AescarchaUserBundle(),
            new Aescarcha\BusinessBundle\AescarchaBusinessBundle(),
        );

        // ...
    }

    // ...
}
```

Step 4: Configure the Bundle
-------------------------

Enable the routes in `app/config/routing.yml`

    aescarcha_business:
        resource: "@AescarchaBusinessBundle/Resources/config/routing.yml"
        prefix:   /


Configure the bundles in `app/config/config.yml`

    # app/config/config.yml
    nelmio_api_doc: ~

    fos_rest:
        routing_loader:
            default_format: json                            # All responses should be JSON formated
            include_format: false                           # We do not include format in request, so that all responses
                                                            # will eventually be JSON formated

    body_listener:                                          # Decode full json bodies
        decoders:
             son: fos_rest.decoder.json


    fos_user:
        db_driver: orm
        firewall_name: api                                  # Seems to be used when registering user/reseting password,
                                                            # but since there is no "login", as so it seems to be useless in
                                                            # our particular context, but still required by "FOSUserBundle"
        user_class: FOS\UserBundle\Model\User

Add this to `app/config/security.yml`

    # app/config/security.yml

    security:
        encoders:
            FOS\UserBundle\Model\UserInterface: sha512

        providers:
            fos_userbundle:
                id: fos_user.user_provider.username        # fos_user.user_provider.username_email does not seem to work (OAuth-spec related ("username + password") ?)
        firewalls:
            api:
                pattern: ^/                                # All URLs are protected
                fos_oauth: true                            # OAuth2 protected resource
                stateless: true                            # Do no set session cookies
                anonymous: false                           # Anonymous access is not allowed


Add the following to `app/config/routing.yml` to be able to use the docs :

    # app/config/routing.yml
    NelmioApiDocBundle:
        resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
        prefix:   /api/doc




Testing
============

To test this bundle, you need to download and enable the following bundles

    "doctrine/doctrine-fixtures-bundle": "^2.3",
    "liip/functional-test-bundle": "^1.6"

    $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
    $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();

And I recommend using this config in `config_test.yml` to use sqlite

    doctrine:
        dbal:
            default_connection: default
            connections:
                default:
                    driver:   pdo_sqlite
                    path:     %kernel.cache_dir%/test.db

    liip_functional_test:
        cache_sqlite_db: true

