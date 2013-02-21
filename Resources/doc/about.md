<!-- 100 About

     Expected audience:  Any user
-->


About PivotX 4
==============

PivotX 4 is an open source content management system especially designed for high-performance and highly configurable websites.
It is build on top of Symfony 2 because it provides a rock solid foundation.

PivotX has been designed from the ground up to deliver a great experience for the following group of users:

-   **Content editors** want a good overview and easy access to all of the content of the site(s).
  
    The default backend provides a configurable dashboard and easy access to all content.
    Multi-lingual, media management, content history and batch-editing are standard features.
    The backend for content editors has been designed to work on desktops, tablets and mobile phones.

-   **Front-end designers** want complete control over the HTML but don't want to hassle with too much boilerplate or with dependencies.

    On top of the Twig template language we have build various systems to enhance your development experience.
    The default backend provides developers with documentation and lots of ready-to-use snippets. Minimal command-line experience is
    required, but everything can be configured by the backend. There are standard content types to choose from and all of them
    can be modified to the websites' requirements. Content types automatically add useful snippets ready to be implemented into the website.

-   **Developers** want a clean and thought-through framework upon which to built on.

    Since PivotX can actually be added to an existing setup, you can use the full power of the Symfony 2 framework and still have the
    flexibility PivotX will give you. Of course PivotX comes to its own when configured from the start:

    - Manageable entities (content types) from the backend.
    - Code (model and repository) generation.
    - Automatic routing, crud editors and translations are all added by default and can be overwritten if needed.
    - PivotX adds many Symfony **services** which can be used by your own code or where you can add your own stuff into.



### Services and API

Every service has benefits for all user groups, but for clarity they are seperated by group.

#### for content editors

*   Activities.

    Activities keep track of all actions inside the CMS. If someone logs in, changes a record, it
    all gets collected. Depending on the importance or size the log gets automatically trimmed.

*   Translations.

    It's easy for the developers of the site to add every piece of text as a 'translatable' code
    inside the Twig templates (or PHP for that matter). This means that you can basically easily change
    every text on the site from the backend.

#### for front-end designers

*   Lists.

    **Lists** can be ordinary lists which can be used inside your templates, but you also use them
    to make the main menu or even the complete sitemap. You can create new lists, include
    other lists inside those lists, add dynamic lists portions (for instance the latest 3 news items)
    and add them easily inside your templates.

    For instance to load a list: <code>{% loadList 'mainmenu' as menu %}</code>

    @todo there is no backend interface yet

*   Twig Views.

    **Views** are basically pre-defined queries which have a very simple Twig-snippet and are very easy 
    to configure.
    
    For instance to load a view: <code>{% loadView 'Entry/findLatestByModified' as entries limit 5 %}</code>

    See [Development] -> [Views]

*   Twig Formats.

    **Formats** are Twig-filters that can be configured inside the backend.

    For instance: <code>{{ entry.datemodified|formatas('date/long') }}</code>

    @todo not yet

    See [Development] -> [Formats]

*   Web Debug Toolbar.

    By default we add a simple Collector to the standard Symfony toolbar. This shows exactly
    which templates get loaded.

*   Webresourcer.

    As developers we want total control over our stylesheets and scripts but we don't want the
    hassle of manually maintaining dependencies and/or proper loading order. We just want
    to use something, fill in some stuff (like api-keys or other config options) and then
    include it in our website. 
    This service can automatically minify code, include it in the right spot inside your
    HTML. Disable certain resources when developing (@todo not yet).

    @todo later we will also be able to update resources and even go back when the updates
    didn't work as expected

#### for developers

*   Outputter.

    This is the low-level version of webresources. You can use this to dynamically inject 
    code in the HTML in certain locations (headStart, afterTitle, headEnd, bodyStart, bodyEnd).

*   Siteoptions.

    We have a simple service to store and retrieve site options and values. You can store values
    of any type (text, json, xml, etc), mark them as human editable, auto-load them at every
    request and organise them into groups. PivotX uses them when something has to be dynamic
    or is dynamic by nature.

*   Doctrine features.

    Based on your configuration we add and maintain code inside your entities and models.
    You can fixate this or not use it at all if required, but its purpose is to decrease
    the amount of boilerplate code you would have to write.

*   Front-end services.

    Every service has been built as a PHP service first. So you can easily use and expand
    everything mentioned above with your own PHP code.
