Rhapsody
==========
Rhapsody is a lightweight web development framework built for PHP 8 and offers extreme performance, a modular architecture, elegant syntax and an easy to use philosophy.

### PROJECT STATUS
Rhapsody is currently in **ALPHA** stages of development. As such, it is not recommended to use the framework in a production environment yet - there are still bugs needing fixed and a few more features to be added.
A production-ready version 1.0.0 is scheduled for public release on September 30, 2022.

### PHILOSOPHY & GOALS
Like all frameworks, Rhapsody strives to simplify and speed up the web development process. Where Rhapsody deviates from most frameworks is in its emphasis on the *developer*, by creating an extraordinarily easy to learn and easy to use environment -- without sacrificing performance,
features or extensibility. A framework should help a developer by completing common tasks for the developer and providing options for other tasks, but still being perfectly capable of "getting out of the way" when needed. A framework cannot be all things to all people, so it is important to be
able to *safely* work outside the box with minimal fuss when needed.
We think that you'll find the blazing fast performance, the ultra-light footprint, the comprehensive feature set and the emphasis on ease of use 
to be an indispensable new tool in your web development repertoire.

### Documentation
Full and comprehensive documentation is currently in development, and is packaged along with the framework. Once the framework is installed, visit http://yoursite.com/documentation.
Documentation will also be available online at https://rhapsody.org/documentation soon.

### Requirements
- Apache Server 2.4+
- PHP 8.3 or newer
- Any PDO compatible database
- Composer package manager (https://getcomposer.org/)
- SSH access to your server (optional, but recommended)

### Installation
1. Unpack the Rhapsody-master zip file in your installation directory. Using command prompt (Windows) or terminal (OS X / Linux), navigate to the directory where you unpacked the framework. 
Example: ** cd /var/www/html ** 
Using Composer, run the command ** 'composer update' **. Get Composer here if you do not already have Composer installed (Composer is required in order to use the framework, and to keep everything up to date): https://getcomposer.org/download/
2. Locate the ** .env.example ** configuration file in the root of your installation directory. Rename the .env.example file to .env
3. Enter your database connection settings on lines 7 - 12
4. Enter your full site URL on line 27 **[site_url=""]**, including protocol (http / https), and append a trailing slash at the end
   *http://www.example.com/*
5. Edit your timezone, if applicable, on line 37.

That's it! If you are installing the framework into a subdirectory, you'll have one more step to complete:

##### IF YOU ARE INSTALLING IN A SUBDIRECTORY
To complete installation in subdirectory, you will need to also update the the `subdir` option on line 86 in your .env file.  Change `subdir="" /` to `subdir = "/name-of-your-subdirectory"`. Do not include a trailing slash.

The remaining settings are optional to complete, and are discussed in more detail in the documentation. Feel free to go through them and add/edit as necessary.
