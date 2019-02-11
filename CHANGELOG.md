# Changelog

## 1.2.4 - 2019-02-11

Docs:
* Update supported php versions (#40)
* Fix getSubdomains function description example (#41)

Improvements:
* PHP 7.3 support
* Use `symfony/polyfill-intl-idn` instead of `true/punycode` (#39)

## 1.2.3 - 2017-11-18

Improvements:
* PHP 7.2 support

Fixes:
* use INTL_IDNA_VARIANT_UTS46 for idn_* functions (#20)

## 1.2.2 - 2017-10-17

Fixes:
* fix typo in class constant (#18)

## 1.2.1 - 2017-04-17

Fixes:
* incorrect parsing domains with number sign (#16)

## 1.2.0 - 2016-11-17

New features:
* remove dependency on `intl` extension (#8)

## 1.1.1 - 2016-08-03

Fixes:
* issue #5 with handling query part of URL

## 1.1.0 - 2016-06-29

New features:
* `tld_extract()` function for simple usage;
* `setExtractionMode()` method on `Extract` class for setting extract options.

## 1.0.0 - 2016-06-20

New release with following features:
* IDN support;
* Database in separate weekly updatable package;
* Full test coverage.
