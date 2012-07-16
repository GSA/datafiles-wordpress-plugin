Datafiles WordPress Plugin
==========================

Allows machine-readable files like JSON or XML file to be versioned inside WordPress as a custom post type, and be properly cached and served without directly uploading them to the server.`

How it works
------------

The plugin registers a custom post type "datafile" which functions nearly identically to normally WordPress posts, except in how they are served to users. When a datafile is viewed, the proper content-type header is served to the users browser, and the file content is served raw (without any header or footer) ensuring it renders properly. This allows WordPress sites to curate and host machine-readable static files, creating light-weight pseudo-APIs.

History
-------

Created as a proof-of-concept to show how a machine-readable files could be served by a CMS in conjunction with the President's Digital Strategy.