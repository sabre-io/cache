ChangeLog
=========

2.0.1 (2023-02-09)
------------------

* #53 Check memcached get resultCode more closely (@phil-davis)

2.0.0 (2022-08-23)
------------------

* #43 Drop PHP before 7.4 and add type declarations (@phil-davis)

1.0.4 (2022-06-24)
------------------

* #39 check expiration time in Memory:has() (@Knochenmarc)
* #38 fix ctype_digit() warnings (@Knochenmarc)
* #36 Correct some minor typos (@phil-davis)
* #29 Minor spelling correction (@sdklab007)

1.0.3 (2020-10-03)
------------------

* #25: Add support for PHP 8.0 (@phil-davis)

1.0.2 (2020-10-03)
------------------

* #18 #19 #20 #21 #22: Refactor CI (@phil-davis)
* #14: Code refactor for phpstan and add support for PHP 7.4 (@phil-davis)

1.0.1 (2019-07-19)
------------------

* Memory::delete() must return bool (#11), thx to @webinarium

1.0.0 (2017-01-02)
------------------

* First version!
* Supports Memcached, APCu and an in-process Memory Cache.
* Supports PSR-16.
