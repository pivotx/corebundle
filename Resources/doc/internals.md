<!-- 990 PivotX Internals

     Expection audience:   Developers
-->


PivotX Internals
================

PivotX is built on top of Symfony 2 and much of it's standard bundles.
At its core the system adds two bundles: CoreBundle and BackendBundle.
The CoreBundle **is** PivotX. The BackendBundle is an interface to all the features the CoreBundle provides.
The system is designed to be able to run on just a UserBundle and the CoreBundle. However most of the time
you need an interface to the content and the BackendBundle provides a nice version for that (we think).



CoreBundle
==========

The CoreBundle provides all the services that make PivotX.

1.  Routing. We have our own routing component that can route multiple sites, with multiple domains and multiple languages(/countries).
2.  SiteOptions. Settings stored in the database.
3.  Views. A "view" architecture, a bit like Drupal has, but with complete HTML freedom.
4.  Formats. Backend-configurable Twig filters.
5.  Translations. Backend-configurable language texts. 
6.  Activity. Automatic activity tracking in the backend (and front-end if applicable).
7.  Outputter. Code-controlled inserts html-code into the templates.
8.  Webresourcer. Web resource packagemanagement.
9.  Lists. Create and manage various lists, for menus or other things.
10. Twig. Integrate various services into the templating.

The bundle also provides various Doctrine helpers and code generators.



CoreBundle/Routing
------------------

The routing service introduces a concept that's central to the routing: ***Reference***
A Reference is an internal link to a page. The purpose of the Reference is to decouple a page link and the actual URL. In PivotX one should
only use references and never hard-links. For instance the Twig-reference `{{ ref('page/contact') }}` should work on any site and in any 
language (assuming the site/language has the page). That way the developers can build templates and not worry about what the actual URL
will be. Symfony has a similar feature but this PivotX feature also decouples routes from Controller/Actions.
Another example is `{{ ref('target=mobile@page/contact') }}`. This reference will always link to the contact page on the mobile site, even
if the domain is different. In this setup the domain configuration is also decoupled. Multiple sites and multiple language support is also
included.
