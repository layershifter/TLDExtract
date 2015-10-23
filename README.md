# TLDExtract

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

    $result = Extract::extract('http://forums.news.cnn.com/');
    var_dump($result);
    
    object(LayerShifter\TLDExtract\Result)#34 (3) {
      ["_subdomain":"LayerShifter\TLDExtract\Result":private]=>
      string(11) "forums.news"
      ["_domain":"LayerShifter\TLDExtract\Result":private]=>
      string(3) "cnn"
      ["_tld":"LayerShifter\TLDExtract\Result":private]=>
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

## Installation

Latest release via Composer:

    $ composer require layershifter/tld-extract

## Note About Caching & Advanced Usage


License
-------

This project is open-sourced software licensed under the MIT License.

See the LICENSE file for more information.