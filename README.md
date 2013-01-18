PivotX 4
========

Welcome to the PivotX Readme file.



1) Installing PivotX using Composer
-----------------------------------

### Installing Composer and Symfony

Composer:

    curl -s http://getcomposer.org/installer | php

Symfony Standard Edition:

    php composer.phar create-project symfony/framework-standard-edition path/to/install

Next go to your new Symfony root:

    cd path/to/install


### Installing PivotX CoreBundle and BackendBundle From Computer

    composer.phar config repositories.pxcore vcs /2kdata/git/002/pivotx4_corebundle/
    composer.phar config repositories.pxback vcs /2kdata/git/002/pivotx4_backendbundle/
    composer.phar require -v --dev pivotx/corebundle:dev-master pivotx/backendbundle:dev-master


### Installing PivotX CoreBundle and BackendBundle 

#### From local directory

    cp -rp /home/marcel/public_html/px4/src/PivotX/ src/

#### From GitHub

Execute these commands:

    cd src/
    git clone https://github.com/PivotX/CoreBundle.git CoreBundle
    git clone https://github.com/PivotX/BackendBundle.git BackendBundle


### Adding Bundles

Edit app/AppKernel.php
Add the following to lines to the $bundles array:

    new PivotX\CoreBundle\CoreBundle(),
    new PivotX\BackendBundle\BackendBundle() 

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

Start your browser and go to http://YOURSITE/pivotx/en/


### Installing PivotX CoreBundle and BackendBundle from Github

(next step before full composer compatibility)




## Ideal situation (@todo)

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
