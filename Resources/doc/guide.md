Guide to PivotX 4
=================

This is PivotX 4 configuration guide.



### Introduction

Since you already installed PivotX we will assume you already have
some form of technical documentation which describes which entities
and which templates you will need.

To continue your setup of PivotX we will begin by explaining
how you can add your own entities and continue by setting up your
routing and templating.


### Entities

An entity in Doctrine (and thus in PivotX) is defined as an object inside 
an object-relational model. In more simple terms, an entity is the definition
of a table in a database.
In PivotX we have a few default tables and you are free to define you're
own tables. You can either define them directly in Symfony/Doctrine,
but PivotX also contains an "entity-editor" inside the backend where
you can define the entity and PivotX will write the correct Symfony/Doctrine
files. This guide explains how you can use the backend to add your
own entities.


#### Adding entities

Before we add entities there are a few important details:

-   Backups. While you can easily add and remove an entities you are advised 
    to make backups before you do so. The database and PHP code will be modified
    and it is *quite* possible to leave the CMS in an unworkable state. For now
    don't worry too much, but be thoughtful about it.

-   Choose the name carefully. It's not possible to have duplicate names
    and entities can't be renamed. The format is upper camel case (Doctrine
    standard) and use singular form.

Instructions:

1.  To add an entity go to the [Development] -> [Entities] menu and hit the
    [Add new entity] button. A pop-over should appear.

2.  Enter a name in upper camel case format. For instance "Page" or "MovieReview".

3.  Choose Bundle. This is the Bundle where the configuration and code
    should be generated in. If possible you shouldn't choose the PivotX
    CoreBundle or BackendBundle.

4.  Choose pre-defined type. PivotX comes with a few presets to easily add
    a few common entity-types. If unsure, just choose "id" and add all the
    fields yourself.

5.  Hit [Save]. On top of the page appears a message "Check configuration".
    Ignore this until you are finished adding entities.

6.  Configure entity. Now you can add fields and features to your entity.
    On the bottom of the screen you have various inputs where you need to
    define the plural and singular forms to describe the entity.
    A description of the field types and features will be added soon (@todo).
    The 'slug' is the name which will be used as a prefix in the routing.
    Usually this is a lowercase 'safe for URL' version of the name. For
    instance "Page" becomes "page", "MovieReview" becomes "moviereview".

7.  Repeat these steps until every entity has been added.

8.  Go to the link as suggested by the "Check configuration" message.

9.  Run the command-line as suggested. Read the output of the command
    because sometimes it will suggest another command to run and in
    this case it will most likely be suggested.


#### Entities

Now that you have defined the entities, you will have the following
automatically available.

1.  You're entity now has an editor in the backend where you can add,
    edit and remove records. See the [Content] menu.

2.  There has been PHP-code generated for the entites' models and
    repositories.
    For instance if you have configured a datetime-field with 
    feature 'timesliceable' you can now call:
    <code>$pages = $page_repository->findLatestByModified(null, null, 5)</code>

    To get the last 5 modified pages.

    Browse the code in your bundles [Bundle]/Entity and [Bundle]/Model.

3.  There are views added which you can use inside your Twig-templates.
    These views are directly related to the added repository methods.
    For instance:
    <code>{% loadView 'Page/findLatestByModified' as pages limit 5 %}</code>

    Gets the same pages as before but now inside your template.

    See [Development] -> [Views] for all available views.

4.  Default routes have been added for your entities. You always get
    an 'id route' and if you added a ***slug***-field you also
    have a 'slug route'.


#### Routing

// @todo move this //
By default all added entities get their own routes. This can later be
customized or even disabled if so required.

The routing in PivotX is a bit more complex to set-up but once done
the rest is pretty simple (at least we think so).

There are 3 variables that determine which hostname/domain and
path prefix get used for certain routes.

1.  Sites. You can run multiple sites from one installation.
2.  Languages. Support for multiple languages is built in.
3.  Targets. You can have different sites for different devices.

For each combination of sites, languages and targets you can have
different URL's if you want to.

Domain/url's aliases are supported and can even be automatically
corrected if you want that (@todo correction is on the wishlist).

It's possible to automatically redirect URL's to the proper
canonical URL's. All outgoing URL's are also normalized.

Just like in Symfony, routes have requirements and defaults. The 
difference in PivotX-routing is the way URL's are build for developers.
PivotX has a concept of a 'Reference'.

To view all the routes for the currently selected site go to:
[Development] -> [Routing]

##### Reference

A reference is an internal link to another page. Just like named routes 
in Symfony, by using only the references you can completely seperate
internal routes from public URL's. Only references are a bit smarter
than that. For instance in Twig:

    {{ ref('_page/frontpage') }}                  creates a public url to the frontpage in the current language
    {{ ref('(language=nl)?_page/frontpage') }}    creates a public url to the Dutch frontpage
    {{ ref('(target=mobile)?_page/frontpage') }}  creates a public url to the mobile frontpage in current language
    {{ ref('(t=mobile&l=nl)?_page/frontpage') }}  creates a public url to the Dutch mobile frontpage using shorthands

