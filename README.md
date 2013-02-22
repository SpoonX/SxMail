SxMail 1.3.3
=======================
[![Build Status](https://secure.travis-ci.org/RWOverdijk/SxMail.png?branch=master)](http://travis-ci.org/RWOverdijk/SxMail)

### BC-Break!
This version by-default adds an alternative body when using the `SxMail::compose()` method.
Please take a look at the [wiki](https://github.com/RWOverdijk/SxMail/wiki/Configuration-options#wiki-generate_alternative_body) to see how you can disable this.

Introduction
------------
This module is intended for usage with a default directory structure of a
[ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication/).
This module allows you to easily configure mailings, and send them.
After setting up minimal configuration, you can start sending out e-mails.

Installation
------------
```
./composer.phar require rwoverdijk/sxmail
#when asked for a version, type "1.*".
```

Features
----------
* Easily configurable. Both messages and transport.
* Use ViewModels
* Layout support
* Mimepart detection
* Easy header configuration
* Access to native classes
* Generates alternative body
* 100% code coverage
* Scratches your back after a long day of work
* Makes a good drinking buddy.

For usage and configuration examples, please take a look at the wiki.
The wiki can be found [here](https://github.com/RWOverdijk/SxMail/wiki).

Questions / support
------------
If you're having trouble with this module there are a couple of resources that might be of help.
* [The wiki](https://github.com/RWOverdijk/SxMail/wiki).
* [RWOverdijk at irc.freenode.net #zftalk.dev](http://webchat.freenode.net?channels=zftalk.dev%2Czftalk&uio=MTE9MTAz8d).
* [Issue tracker](https://github.com/RWOverdijk/SxMail/issues). (Please try to not submit unrelated issues).
* By [mail](mailto:r.w.overdijk@gmail.com?Subject=SxMail%20help)
