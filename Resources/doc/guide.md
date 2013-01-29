<!-- 100 Starter Guide

     Expected audience: Any user
-->


Starters Guide to PivotX 4
==========================

This is a PivotX 4 configuration guide.



### Introduction

This guide is the follow up of the install/README guide, so
you should already have a working -but unconfigured- PivotX installation.
We will start by setting up the sites' basic configuration.
You will need to answer the following questions:

*   On which domains will it run?
*   Will you have a different mobile site and domain?
*   In which languages will the site be?

Next we will explain how you can add the entities (sometimes called *content-types*)
which will drive the content of your website. 


### Basic configuration

Start by navigating to [Development] -> [Site]. I will explain each of the
fields you are required to fill. Most of the fields you cannot change
without having to configure a lot of things, so it's best not to rush this
process.

1.  Site identifier

    Every site you will define will have it's own identifier. The identifier
    is used to group the sites' configuration, translations and possibly other
    things together. So while it's allowed to change this, you will lose this
    information unless you move all the settings over.
    If you have only one site, you can just enter 'main-site' or maybe the
    customer or project name.
    For practical reasons we will only allow normal lowercase characters and
    no spaces.

2.  Primary domain

    You can enter the primary domain for the site here or enter 'any'.
    If you leave it at 'any' the site will respond to whatever host you
    configure it on.

3.  Theme / URL schemes (internally named **targets**)

    If you want to serve the same HTML and same URL's to every device (or have only minor
    differences) you will only need to add one target, choose *Responsive*.
    If you need to serve either different HTML and/or different URL's to different
    devices you can add a new 'target' for each combination. You can choose the
    default *Desktop* and *Mobile* or just choose *new* and add your own description.
    Each target has 4 inputs:

    1.  Internal identifier. Just for our convenience.
    2.  Description. Just for your convenience.
    3.  Bundle. This will be the default bundle for this target.
    4.  Theme configuration. The *theme.json* file inside the bundle.
        If you leave it empty, we will use the placeholder value.

4.  Languages for this site

    Here you define in which languages your site will be available.
    Like a lot of things on this screen, it's not simple to change the
    language identifier later. Enter a correct identifier and never
    change it. The language has 3 inputs:

    1.  Internal identifier. Choose it wisely.
    2.  Locale. Enter a locale which works for the server you are on.
        This can easily be changed/fixed later.
    3.  Description. Just for your convenience.

5.  Hit [Continue]

6.  For each combination of *target* and *language* there will be an input
    where you can define all the routing ***prefixes***. The first line
    in each input is assumed to be the canonical prefix.
    If you leave a box empty, there will be no routing for this particular
    combination.
    A **routeprefix** is simply a *protocol + host + path* that will be
    used in the routing system. Usually it will be something like these:

    *   http://example.org/
    *   http://m.example.org/
    *   http://example.org/en/



### Entities

An entity in Doctrine (and thus in PivotX) is defined as an object inside 
an object-relational model. In more simple terms, an entity is the definition
of a table in a database.
In PivotX we have a few default entities and you are free to define your
own entities. You can either define them directly in Symfony/Doctrine,
but PivotX also contains an ***entity-editor*** inside the backend where
you can define the entity and PivotX will write the correct Symfony/Doctrine
files. We will explain how you can use the backend to add your
own entities.

One of the default entities already provided is a **Resource** entity where
all of your' sites resources can be managed. You can actually also create your
own entity for this, but the one we provide is quite powerfull and should
suffice most of the time. The default Resource entity supports embeddable and
non-embeddable resources (images/videos/flash-files but also other documents such as pdf, doc, etc.), local and
remote resources (upload it to your new site or oembed them from another source).


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



### Theme.json file
