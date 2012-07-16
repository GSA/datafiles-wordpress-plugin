Datafiles WordPress Plugin
==========================

Allows machine-readable files like JSON or XML file to be versioned inside WordPress as a custom post type, and be properly cached and served without directly uploading them to the server.

How it works
------------

The plugin registers a custom post type "datafile" which functions nearly identically to normally WordPress posts, except in how they are served to users. When a datafile is viewed, the proper content-type header is served to the user's browser, and the file content is served raw (without any header or footer) ensuring it renders properly. This allows WordPress sites to curate and host machine-readable static files, creating light-weight pseudo-APIs.

History
-------

Created as a proof-of-concept to show how a machine-readable files could be served by a CMS in conjunction with the President's Digital Strategy.

Note
----

This plugin allows users to serve unfiltered content to visitors. By default, only administrators have access to edit datafiles. Additional capabilities can be granted via third-party plugins such as the [Members plugin](http://wordpress.org/extend/plugins/members/).

License
-------

* Plugin - GPLv3 or later
* Icon - [CC0](https://creativecommons.org/about/cc0) via the [Noun Project](http://thenounproject.com/noun/source-code/#icon-No1171)