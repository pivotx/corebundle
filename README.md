PivotX 4
========

Welcome to the PivotX Readme file.



Installing PivotX using Composer
--------------------------------

This is the recommended and official way to install PivotX.


### Installing Composer and Symfony

Composer:

    curl -s http://getcomposer.org/installer | php

Symfony Standard Edition:

    php composer.phar create-project symfony/framework-standard-edition path/to/install

Next go to your new Symfony root:

    cd path/to/install


### Installing PivotX CoreBundle and BackendBundle From Computer

    php composer.phar config repositories.pxcore vcs /2kdata/git/002/pivotx4_corebundle/
    php composer.phar config repositories.pxback vcs /2kdata/git/002/pivotx4_backendbundle/
    php composer.phar require -v pivotx/corebundle:dev-master pivotx/backendbundle:dev-master


#### Install for Mysql/Pgsql

Modify app/config/parameters.yml and fill in your database configuration.


#### Install for SQlite

Edit app/config/config.yml
Add somewhere near doctrine / dbal:

        path:       %database_path%

Modify app/config/parameters.yml with the correct database configuration. For instance:

        database_path: "%kernel.root_dir%/pivotx.sqlite"

After database creation (see below), change the file mode:

        chmod 666 app/pivotx.sqlite


### Updating security.yml

Overwrite security.yml with the PivotX version:

    cp vendor/pivotx/corebundle/required-security.yml  app/config/security.yml


### Run doctrine schema create

Create database:

    php app/console doctrine:schema:create

If you used an SQLite database make sure the file is writable:

    chmod 666 <sqlite-database-file>


### Run PivotX Setup

Run PivotX setup:

    php app/console pivotx:setup

The setup will require you to enter an administrator e-mailaddress and will
generate a password for you. Remember it!

After this command has finished you will have finished the command-line
part of the setup.


### Web PivotX web-setup

You can now continue to the web-setup. Follow the instruction on this page:

http://YOURSITE/pivotx/en/documentation/guide

