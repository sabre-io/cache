sabre/cache
===========

This repository is a simple abstraction layer for key-value caches. It
implements [PSR-16][5].

If you need a super-simply way to support PSR-16, sabre/cache helps you get
started. It's as hands-off as possible.

It also comes with a test-suite that can be used by other PSR-16
implementations.

Installation
------------

Make sure you have [composer][1] installed, and then run:

    composer require sabre/cache


Usage
-----

Read [PSR-16][5] for the API. We follow it to the letter.


### In-memory cache

This is useful as a test-double or long running processes. The `Memory` cache
only lasts as long as the object does.

```php
$cache = new \Sabre\Cache\Memory();
```


### APCu cache

This object uses the [APCu][6] api for caching. It's a fast memory cache that's
shared by multiple PHP processes.

```php
$cache = new \Sabre\Cache\Apcu();
```


### Memcached cache

This object uses the [Memcached][6] extension for caching.

```php
$memcached = new \Memcached();
$memcached->addServer('127.0.0.1', 11211);
$cache = new \Sabre\Cache\Memcached($memcached);
```

You are responsible for configuring memcached, and you just pass a fully
instantiated objected to the `\Sabre\Cache\Memcached` constructor.


Build status
------------

| branch | status |
| ------ | ------ |
| master | [![Build Status](https://travis-ci.org/sabre-io/cache.svg?branch=master)](https://travis-ci.org/sabre-io/cache) |


Questions?
----------

Head over to the [sabre/dav mailinglist][2], or you can also just open a ticket
on [GitHub][3].


Made at fruux
-------------

This library is being developed by [fruux][4]. Drop us a line for commercial
services or enterprise support.

[1]: http://getcomposer.org/
[2]: http://groups.google.com/group/sabredav-discuss
[3]: https://github.com/fruux/sabre-cache/issues/
[4]: https://fruux.com/
[5]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-16-simple-cache.md
[6]: http://php.net/apcu
