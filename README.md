# TLDExtract

[![Latest Stable Version](https://poser.pugx.org/layershifter/tld-extract/v/stable)](https://packagist.org/packages/layershifter/tld-extract)
[![Build Status](https://travis-ci.org/layershifter/TLDExtract.svg)](https://travis-ci.org/layershifter/TLDExtract)
[![Total Downloads](https://poser.pugx.org/layershifter/tld-extract/downloads)](https://packagist.org/packages/layershifter/tld-extract)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/layershifter/TLDExtract/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/layershifter/TLDExtract/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/layershifter/TLDExtract/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/layershifter/TLDExtract/?branch=master)

`TLDExtract` accurately separates the gTLD or ccTLD (generic or country code
top-level domain) from the registered domain and subdomains of a URL. For
example, say you want just the 'google' part of 'http://www.google.com'.

*Everybody gets this wrong.* Splitting on the '.' and taking the last 2
elements goes a long way only if you're thinking of simple e.g. .com
domains. Think parsing
[http://forums.bbc.co.uk](http://forums.bbc.co.uk) for example: the naive
splitting method above will give you 'co' as the domain and 'uk' as the TLD,
instead of 'bbc' and 'co.uk' respectively.

`Extract` on the other hand knows what all gTLDs and ccTLDs look like by
looking up the currently living ones according to
[the Public Suffix List](http://www.publicsuffix.org). So,
given a URL, it knows its subdomain from its domain, and its domain from its
country code.

    $result = Extract::get('http://forums.news.cnn.com/');
    var_dump($result);
    
    object(LayerShifter\TLDExtract\Result)#34 (3) {
      ["subdomain":"LayerShifter\TLDExtract\Result":private]=>
      string(11) "forums.news"
      ["domain":"LayerShifter\TLDExtract\Result":private]=>
      string(3) "cnn"
      ["tld":"LayerShifter\TLDExtract\Result":private]=>
      string(3) "com"
    }

`Result` implements ArrayAccess interface, so you simple can access to its result.

    var_dump($result['subdomain']);
    string(11) "forums.news"
    
    var_dump($result['domain']);
    string(3) "cnn"
    
    var_dump($result['tld']);
    string(3) "com"
    
Also you can simply convert result to JSON.
    
    var_dump($result->toJson());
    string(54) "{"subdomain":"forums.news","domain":"cnn","tld":"com"}"

This package based on code from [w-shadow](http://w-shadow.com/blog/2012/08/28/tldextract/)
which is port of [Python module](https://github.com/john-kurkowski/tldextract).

## Compatible PHP versions
- PHP 5.5
- PHP 5.6
- PHP 7
- HHVM

## Installation

Latest release via Composer:

    $ composer require layershifter/tld-extract

## Note About Advanced Usage & Caching

- [Advanced usage](#note-advanced)
- [Caching](#note-caching)

### <a name="note-advanced"></a> Advanced usage

For overriding object that will be returned in result you can create own class that implements `\LayerShifter\TLDExtract\Interfaces\ResultInterface`.

For example:

    class OwnResult implements \LayerShifter\TLDExtract\Interfaces\ResultInterface {
    }
    
    Extract::setResultClass('OwnResult');

### <a name="note-caching"></a> Caching

By default `TLDExtract` downloads TLD list from publicsuffix.org, caches it and never update.

You can override this behavior via setting $fetch to `true`:

    Extract::setFetch(true);

---

Also, you can manually update TLD cache by calling method (recommended):

    Extract::updateCache();
    
This method returns boolean that indicates processes result.

---

By default cache file will be stored in `/path/to/TLDExtract/cache/.tld_set`, you can set file for cache by calling:

    Extract::setCacheFile('/path/to/your/dir/cache.file');

License
-------

This project is open-sourced software licensed under the MIT License.

See the LICENSE file for more information.