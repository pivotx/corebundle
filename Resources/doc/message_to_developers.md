

Message to the Symfony 2, Doctrine and other developers
=======================================================


Whilst we tried using as much of the features of the existing frameworks. We found we either had too little time to implement them properly,
we were not smart enough to implement it or we had very specific feature set in mind which was easier to re-invent for the moment.
As this CMS matures we would like to contribute more to all the projects so that we can integrate them properly in a future version.


To all developers
-----------------

// @todo we could also describe how we expect a team to work with PivotX //

PivotX has always been a CMS for editors and (front-end) developers. This hasn't changed for this new version.
For the front-end designer we want a CMS that relatively easy to set-up, minimal command-line usage and everything configurable from the backend.
For developers we want a good and solid framework (Symfony), to have less boilerplate and easy integration of features that are available to front-end designers
and into the default backend for editors.

To achieve these goals we basically wanted two things:

1.  Minimise command-line usage. Especially during development.

    We have a backend entity editor (no need to learn the entity YAML) and resource packagemanagement with dependencies.
    The former still requires a minimum of command-line usage, but the latter is fully automatic.
    For example if 'jquery-ui' is a package, the developer just enables it and it should automatically also enable 'jquery' and add them both in the
    proper order into the html. Another example would be 'google-analytics', which should ask 'enter the UA-code' and then enable itself in the HTML (if not
    in develop mode of course).

2.  Code generation and views that integrate into the Twig templating.

    Although Doctrine already provides code generation at a basic level, we wanted certain 'features' to automatically generate repository methods and
    wanted those new methods to also have their own view in our views-system.
    For instance a field with 'timestampable.update' should automatically updated it's time when changed. But also automatically have a repository
    method `->findLatestDateField()` and a "view" a front-end designer can use to integrate those entities into the site. Having these features
    saves a lot of boilerplate code, also saves a lot of code across projects.

   


Assetic
-------

We struggled to use this, but ultimately we stopped using it and build something we required. It's still on our wish list to actually use.
We wanted to build a 'web resource package system', where all dependencies between the resources are managed and the ultimate
control of the packages is in the backend. Our system is not as fast or as smart as assetic at this time.


Doctrine
--------

The Doctrine extensions were not used, although some existing extensions on the internet provide the same functionality.
We wanted a tight integration of our model and repository code generation, automatic "view" generation and other implementation details.
For example a 'timestampable' field automatically automatically adds `->findLatest*` repository methods.
Another example is our 'versionable' feature which stores versions of record automatically when the record changes.
This is tightly integrated into our ActivityLog entity and our default CRUD has an interface to retrieve those records (@todo untrue at the moment).


Symfony 2
---------

The default routing is hardly used. We wanted a flexible and non-hardcoded way to route requests. Considering multi-lingual and mobile routes we needed a lot of flexibility that
was configurable in the backend.
We of course made lots of use of the DependencyInjection and Service Architecture. However we did take some shortcuts which we aim to improve in future versions.
Also in the beta we dind't make use of CacheWarmers but this is high on our priority list to add.

