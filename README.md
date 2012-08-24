PivotX 4
========

Welcome to the PivotX installation.



1) Installing PivotX using Composer
-----------------------------------

### Installing Composer and Symfony

Composer:

    curl -s http://getcomposer.org/installer | php

Symfony Standard Edition:

    php composer.phar create-project symfony/framework-standard-edition path/to/install

Next go to your new Symfony root:

    cd path/to/install


### Installing PivotX CoreBundle and BackendBundle from local directory

    cp -rp /home/marcel/public_html/px4/src/PivotX/ src/

### Adding Bundles

Edit app/AppKernel.php
Add the following to lines to the $bundles array:

    new PivotX\CoreBundle\CoreBundle(),
    new PivotX\BackendBundle\BackendBundle() 

### Fixing LESS in Assetic (currently server-side required)

Edit app/config/config.yml
Add "BackendBundle" to assetic / bundles:

    bundles:        [ "BackendBundle" ]

Add the following lines after assetic / filters:

        less:
            node:       /usr/bin/node
            node_paths: [/usr/lib/nodejs, /usr/local/lib/node_modules]


### Fix login

Edit app/config/routing.yml
Add the following at the end of the file:

    login_check:
        pattern:    /pivotx/en/login_check


#### Install for Mysql/Pgsql

Modify app/config/parameters.yml and fill in your database configuration.


#### Install for SQlite

Edit app/config/config.yml
Add somewhere near doctrine / dbal:

        path:       %database_path%

Modify app/config/parameters.yml with the correct database configuration.


### Updating security.yml

Overwrite security.yml with the PivotX version:

    cp src/PivotX/CoreBundle/required-security.yml app/config/security.yml 


### Update composer.json

Edit composer.json
Change the following line:

    "psr-0": { "": "src/" }

To:

    "psr-0": { "": "src/", "PivotX": [ "src/PivotX/CoreBundle/src", "src/PivotX/BackendBundle/src" ] }

Run:

    php link-to/composer.phar update


### Run doctrine schema create and PivotX setup

Create database:

    php app/console doctrine:schema:create

If you use SQlite now make sure the file is writable by all:

    chmod 666 <sqlite-database-file>

And depending on your server, you might need give the directory access to

    chmod 777 .

Run PivotX setup:

    php app/console pivotx:setup

Run Assetic Dump

    php app/console assetic:dump


### Done!

Run your browser and go to http://YOURSITE/pivotx/en/


### Installing PivotX CoreBundle and BackendBundle from Github

(next step before full composer compatibility)



### Installing Composer and Symfony

Composer:

    curl -s http://getcomposer.org/installer | php

Symfony Standard Edition:

    php composer.phar create-project pivotx/standard-edition path/to/install

Next go to your new Symfony root:

    cd path/to/install

Configure database:

    (etc)

Run PivotX setup:

    php app/console pivotx:setup
