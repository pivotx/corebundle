PivotX 4
========

PivotX 4 is an open source content management system especially designed for high-performance and highly configurable websites.
It is build on top of Symfony 2 because it provides a rock solid foundation.

This release is very much a **PRE-ALPHA** release. Meaning it shouldn't be used to actually build websites yet.
What you do get is a preview how PivotX can be used to structure, build and manage websites.

There is additional documentation in the Resources/doc directory. You can view this online on github, but it is also available
in the main menu of your own PivotX install.

PivotX requires PHP and any relational database supported by [Doctrine](http://www.doctrine-project.org/).
PivotX will be released under the open source [MIT-license](http://opensource.org/licenses/mit-license.php).


Installing PivotX using Composer
--------------------------------

This is the recommended way to install PivotX.


### Installing Composer and Symfony

Composer:

    curl -s http://getcomposer.org/installer | php

Symfony Standard Edition (currently 2.1.8):

    php composer.phar create-project symfony/framework-standard-edition path/to/install 2.1.8

Next go to your new Symfony root:

    cd path/to/install


### Installing PivotX CoreBundle and BackendBundle from Github

    php [path-to-composer]/composer.phar config repositories.pxcore vcs https://github.com/pivotx/corebundle.git
    php [path-to-composer]/composer.phar config repositories.pxback vcs https://github.com/pivotx/backendbundle.git
    php [path-to-composer]/composer.phar require -v pivotx/corebundle:dev-master pivotx/backendbundle:dev-master


### Adding Bundles to the kernel

Edit app/AppKernel.php
Add the following to lines to the $bundles array:

    new PivotX\CoreBundle\CoreBundle(),
    new PivotX\BackendBundle\BackendBundle(),


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
generate a password for you. Remember and/or store it!

After this command has finished you will have finished the command-line
part of the setup.


### Web PivotX web-setup

You can now continue to the web-setup. After the login you are directed to
the next part of the setup.

http://YOURSITE/pivotx/setup

