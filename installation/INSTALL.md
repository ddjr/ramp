# Ramp/Smart Installation #

(under construction!)

Ramp is built on top of an AMP stack (Apache, MySQL, PHP) and the
Zend Framework.

## Preparation ##

### AMP ###
These installation instructions assume that you have a working AMP
implementation, which may come bundled with your operating system,
may be created by downloading and installing the Apache, MySQL, and
PHP components separately, or may be downloaded as a bundled,
integrated unit (for example, MAMP, LAMP, XAMPP, or WAMP). If you
do not have one of these, see the following websites for installation
and tutorials:

>   Apache HTTP Server: [...] [apache]  
>   MySQL:  [http://www.mysql.com/] [mysql]  
>   PHP:  [http://www.php.net/] [php]  
>   XAMPP: http://www.apachefriends.org/en/xampp.html  
>       (Works with Mac, Windows & Linux)
>   MAMP: http://www.mamp.info/en/index.html  
>       (Works with Mac)
>   WAMP: http://www.wampserver.com/  
>       (Works with Windows)
>   LAMP: *****
>       (Works with Linux)

Depending on the system you are using, you may need to update your `httpd`
configuration (e.g., `/etc/apache2/httpd.conf`) to load the
Rewrite module, the PHP module, turn on virtual hosting, and allow
overrides to all in your `DocumentRoot` directory.

Ramp also includes dependencies on HTML 5, so the browsers used to
interact with Ramp on your server must support HTML 5.

[TODO: Will need directions about setting up virtual hosts somewhere.]

Ramp requires at least PHP 5.3, because it uses the modified ternary
operator ( `? :` with the middle part left out).  The PHP Markdown
Library also depends on PHP 5.3.  In order to get better encryption,
PHP recommends 5.3.7 or later.

If you make a link from your server's document root to folders or
directories in personal space, make sure the permissions on the link and
target directory will allow your web server to follow the link.

### Other dependencies ###
Ramp is built upon Zend Framework 1, a framework of PHP classes for
developing web applications.  Its style attributes extend
Twitter Bootstrap, using the `twitter-bootstrap-zf1` library to
integrate Twitter Bootstrap with Zend Framework 1.  Finally, Ramp uses
Michel Fortin's PHP Markdown Lib 1.3 to provide support for Markdown in
documents and activities files, which is then converted to HTML.

The current version of Ramp includes bundled versions of
Zend Framework 1, the Markdown library, the `twitter-bootstrap-zf1`
library, and a subset of Twitter Bootstrap 2.  (See the Ramp [README]
[readme] file for more information about these software dependencies and
for license information about these components.)

If you want to use Git to download Ramp (and future updates), 
read this online [Getting Started - Installing Git][git] documentation.
The [Set Up Git][git-setup] document is also helpful.

For development, should also download PHP unit tests (e.g., phpunit34).


## Installing Ramp ##
Once you have a server and a database, you must download the most
recent version of RAMP from https://github.com/AlyceBrady/ramp.  You can
just download the zip file from there, or you can clone it.
If you have Git installed, you can do this (I think) using
    git://github.com/AlyceBrady/ramp.git
(Is this different from a git clone?  Does it download the equivalent of
a zip file?)
If you want to do Git development, I believe you want to fork it first
(to create your own repository) and then clone.

Once you have downloaded the software, you may set up one of the two
provided demo programs, set up a Ramp/Smart development environment, or
create a new a custom application by defining new activities and table
settings.  Even if you plan to create a custom application, you may wish
to set up one of the three predefined applications first, to test your
installation and to become familiar with some of the basics.

... three steps, which are briefly described below and detailed in
[INSTALL_DB.md] [installdb].

1. Plan: The first step is to decide which application you will set up
and then plan your customizations.  If you choose to set up one of the
three predefined applications, this step may just consist of determining
the MySQL accounts and passwords to use.  [Also where the application is
going to go, including what vhost to set up.]

2. Address Security Concerns: INSTALL_DB guides you through several
basic steps for protecting files with database access information from
prying eyes.

3. Create an initial set of tables and users to get started.  If you
plan to install one of the three pre-defined applications, this step
will merely consist of editing a script provided in the appropriate
subdirectory to change the MySQL account names and passwords, 
running a MySQL script from the appropriate directory, and creating a
configuration file from a template and editing it to refer to your own
specific accounts and passwords.  If you are creating a customized
application, you will need to define the database schemas for your
tables and create activity files and table settings for your
application.

INSTALL_DB guides you through all three of these steps.

The pre-defined applications available to you are:

 * RAMP Demo:  A small demo consisting of just a few tables.  

 * SMART Demo:

 * Development Environment: Based on Smart Demo but gives the
   pre-defined "users" authorization to make changes to the tables in
   the development environment.

Ramp expects to find a file called `application.ini` in its
`application/configs/` directory.  This file should
contain Ramp configuration information, some of which you may have to
customize for your environment.  To prevent the possibility
that local customizations in an `application.ini` file might be
overwritten by future Ramp updates, the Ramp download contains
a set of small files containing uncustomized sections of a possible
`application.ini` file, which can be put together in different ways
depending on whether you are planning to set up a demo, the Smart
development environment, a customized Smart application, or a customized
application built on Ramp.  The [Customizing
application.ini][custom_build] document provides more information on 
building your `application.ini` file from the provided components.

Later, when installing a Ramp update,
you will need to compare your `application.ini` file against any
updated component pieces and incorporate new changes as appropriate.

### Setting up Ramp on Your Server ###
[TODO: The following section is still under construction...]

- Note that the instructions below are to some extent machine-dependent.
  On Debian-based systems, for example, the instructions and file names
  are differnt.
- [Write instructions for setting up vhosts.] … See the vhostExamples.conf
- On some machines, you may need to first enable virtual hosting within
  `/etc/apache2/httpd.conf` by uncommenting the `Include` line for
  `httpd-vhosts.conf`.
- Define the virtual host(s) you are going to use.  Exactly how this is
  done is machine-dependent.  For example, on some machines you will
  edit a file in the apache directory structure (e.g.,
  `/etc/apache2/extra/httpd-vhosts.conf) and add however many virtual
  host definitions you want to that file.  On others (Debian, for
  example), you will need to provide a separate file in a directory such
  as /etc/apache2/sites-available for each virtual host.  The
  `.../ramp/installation/vhost-configs` directory contains a set of
  example files that could be used as templates for individual files on
  Debian-based machines, or whose contents could be added to an
  `httpd-vhosts.conf` file on other architectures.
- Reload or restart the Apache server.  (On Debian, you first enable the
  new virtual hosts using a2ensite and then reload apache.)

- Unless the new server name is being served by DNS, you will need to
  make changes on the client machines to use the new virtual hosts.
  For example, this might be a matter of editing `/etc/hosts` on the
  client machine and adding lines that resolve the virtual server names
  from the appropriate machine.  For example,
        123.45.0.67     rampdemo

[__Notes that need to be updated__]  
If the server should be accessible to a limited, pre-defined set
of machines, edit `/etc/hosts` files on those machines to resolve the
virtual hostnames to the machine servicing Ramp.  If Ramp should
be visible beyond that, you probably need to register the server name.
(If you can’t, or don’t want to, get `vhosts` to work, you may be able
to get the same results with `.htaccess`.  Uncomment the last line and
specify the correct `APPLICATION_ENV` (production, development, testing,
rampdemo, smartdemo, etc.).  Without `vhosts`, though, it’s not
as easy to have multiple Ramp/Smart database environments running on
one server.)

[readme]: /document/index/document/..%252F..%252README.md
[installdb]: /document/index/document/..%252F..%252Finstallation%252FINSTALL_DB.md
[custom]:  /document/index/document/..%252F..%252Finstallation%252FApplication_Ini.md
[custom_build]: /document/index/document/..%252F..%252Finstallation%252FApplication_Ini.md#build
[git]: http://git-scm.com/book/en/Getting-Started-Installing-Git
[git-setup]: https://help.github.com/articles/set-up-git#platform-all
