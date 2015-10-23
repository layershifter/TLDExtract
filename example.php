<?php
/**
 * example.php
 *
 * @author Alexander Fedyashov <a@fedyashov.com>
 */


require 'vendor/autoload.php';

use LayerShifter\TLDExtract\Extract;

$result = Extract::extract('http://forums.news.cnn.com/');

var_dump($result->toJson());

var_dump($result['subdomain']);
var_dump($result->tld);