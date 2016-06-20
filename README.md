# TLDExtract

`TLDExtract` accurately separates the gTLD or ccTLD (generic or country code top-level domain) from the registered domain and subdomains of a URL, e.g. domain parser. For example, say you want just the 'google' part of 'http://www.google.com'.

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-coverage]][link-coverage]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]
[![PHP 7 ready](icon-php7ready)][link-travis]

---

*Everybody gets this wrong.* Splitting on the '.' and taking the last 2 elements goes a long way only if you're thinking of simple e.g. .com domains. Think parsing [http://forums.bbc.co.uk](http://forums.bbc.co.uk) for example: the naive splitting method above will give you 'co' as the domain and 'uk' as the TLD, instead of 'bbc' and 'co.uk' respectively.

`TLDExtract` on the other hand knows what all gTLDs and ccTLDs look like by looking up the currently living ones according to [the Public Suffix List](http://www.publicsuffix.org). So, given a URL, it knows its subdomain from its domain, and its domain from its country code.

```php
$extract = new LayerShifter\TLDExtract\Extract();
$result = $extract->parse('http://forums.news.cnn.com/');
var_dump($result);
    
object(LayerShifter\TLDExtract\Result)#34 (3) {
  ["subdomain":"LayerShifter\TLDExtract\Result":private]=>
  string(11) "forums.news"
  ["hostname":"LayerShifter\TLDExtract\Result":private]=>
  string(3) "cnn"
  ["suffix":"LayerShifter\TLDExtract\Result":private]=>
  string(3) "com"
}
```
`Result` implements ArrayAccess interface, so you simple can access to its result.
```php
var_dump($result['subdomain']);
string(11) "forums.news"
    
var_dump($result['domain']);
string(3) "cnn"
    
var_dump($result['tld']);
string(3) "com"
```
Also you can simply convert result to JSON.
```php
var_dump($result->toJson());
string(54) "{"subdomain":"forums.news","domain":"cnn","tld":"com"}"
```
This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][]. If you notice compliance oversights, please send a patch via pull request.

### Does TLDExtract make requests to Public Suffix List website?

No. `TLDExtract` uses database from [TLDDatabase](https://github.com/layershifter/TLDDatabase) that generated from Public Suffix List and updated regularly. It does not make any HTTP requests to parse or validate a domain.

## Requirements

The following versions of PHP are supported.

* PHP 5.5
* PHP 5.6
* PHP 7.0
* HHVM

## Install

Via Composer

``` bash
$ composer require layershifter/tld-extract
```
## Additional result methods

Class `LayerShifter\TLDExtract\Result` has some usable methods:
```php
$extract = new LayerShifter\TLDExtract\Extract();

# For domain 'shop.github.com'

$result = $extract->parse('shop.github.com');
$result->getFullHost(); // will return (string) 'shop.github.com'
$result->getRegistrableDomain(); // will return (string) 'github.com'
$result->isValidDomain(); // will return (bool) true
$result->isIp(); // will return (bool) false

# For IP '192.168.0.1'

$result = $extract->parse('192.168.0.1');
$result->getFullHost(); // will return (string) '192.168.0.1'
$result->getRegistrableDomain(); // will return null
$result->isValidDomain(); // will return (bool) false
$result->isIp(); // will return (bool) true
```
## Custom database

By default package is using database from [TLDDatabase](https://github.com/layershifter/TLDDatabase) package, but you can override this behaviour simply:
```php
new LayerShifter\TLDExtract\Extract(__DIR__ . '/cache/mydatabase.php');
```
For more details and how keep database updated [TLDDatabase](https://github.com/layershifter/TLDDatabase).

## Implement own result

By default after parse you will receive object of `LayerShifter\TLDExtract\Result` class, but sometime you need own methods or additional functionality.

You can create own class that implements `LayerShifter\TLDExtract\ResultInterface` and use it as parse result.
```php
class CustomResult implements LayerShifter\TLDExtract\ResultInterface {}

new LayerShifter\TLDExtract\Extract(null, CustomResult::class);
```

## Parsing modes

Package has three modes of parsing:
* allow ICCAN suffixes (domains are those delegated by ICANN or part of the IANA root zone database);
* allow private domains (domains are amendments submitted to Public Suffix List by the domain holder, as an expression of how they operate their domain security policy);
* allow custom (domains that are not in list, but can be usable, for example: example, mycompany, etc).

For keeping compatibility with Public Suffix List ideas package runs in all these modes by default, but you can easily change this behavior:
```php
use LayerShifter\TLDExtract\Extract;

new Extract(null, null, Extract::MODE_ALLOW_ICCAN);
new Extract(null, null, Extract::MODE_ALLOW_PRIVATE);
new Extract(null, null, Extract::MODE_ALLOW_NOT_EXISTING_SUFFIXES);
new Extract(null, null, Extract::MODE_ALLOW_ICCAN | Extract::MODE_ALLOW_PRIVATE);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing
``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## License

This library is released under the Apache 2.0 license. Please see [License File](LICENSE) for more information.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

[ico-version]: https://img.shields.io/packagist/v/layershifter/tld-extract.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-Apache2-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/layershifter/TLDExtract/master.svg?style=flat-square
[ico-coverage]: https://codeclimate.com/github/layershifter/TLDExtract/badges/coverage.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/layershifter/TLDExtract.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/layershifter/tld-extract.svg?style=flat-square
[ico-php7ready]: http://php7ready.timesplinter.ch/layershifter/TLDExtract/master/badge.svg

[link-packagist]: https://packagist.org/packages/layershifter/tld-extract
[link-travis]: https://travis-ci.org/layershifter/TLDExtract
[link-coverage]: https://codeclimate.com/github/layershifter/TLDExtract/coverage
[link-code-quality]: https://scrutinizer-ci.com/g/layershifter/TLDExtract
[link-downloads]: https://packagist.org/packages/layershifter/tld-extract
