<?php

namespace LayerShifter\TLDExtract;

use ArrayAccess;
use LogicException;
use OutOfRangeException;

/**
 * This class holds the components of a domain name.
 *
 * @property string $subdomain The subdomain. For example, the subdomain of "a.b.google.com" is "a.b".
 * @property string $domain The registered domain. For example, in "a.b.google.com" the registered domain is "google".
 * @property string $tld The top-level domain / public suffix. For example: "com", "co.uk", "act.edu.au".
 *
 * You can access the components using either property syntax or array syntax. For example,
 * "echo $result->tld" and "echo $result['tld']" will both work and output the same string.
 *
 * All properties are read-only.
 */
class Result implements ArrayAccess
{
    private $fields;

    public function __construct($subdomain, $domain, $tld)
    {
        $this->fields = array(
            'subdomain' => $subdomain,
            'domain' => $domain,
            'tld' => $tld,
        );
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->fields);
    }

    public function __toString()
    {
        return sprintf('%s(subdomain=\'%s\', domain=\'%s\', tld=\'%s\')', __CLASS__, $this->subdomain, $this->domain, $this->tld);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->fields);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name];
        }
        throw new OutOfRangeException(sprintf('Unknown field "%s"', $name));
    }

    public function __set($name, $value)
    {
        throw new LogicException('Can\'t modify an immutable object.');
    }

    public function offsetSet($offset, $value)
    {
        throw new LogicException(sprintf('Can\'t modify an immutable object. You tried to set "%s".', $offset));
    }

    public function offsetUnset($offset)
    {
        throw new LogicException(sprintf('Can\'t modify an immutable object. You tried to unset "%s".', $offset));
    }

    /**
     * Get the domain name components as a native PHP array.
     * The returned array will contain these keys: 'subdomain', 'domain' and 'tld'.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->fields;
    }
}