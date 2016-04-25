<?php
/**
 * PHP version 5.
 *
 * @category Examples
 *
 * @author   Alexander Fedyashov <a@fedyashov.com>
 * @license  MIT https://opensource.org/licenses/MIT
 *
 * @link     https://github.com/layershifter/TLDExtract
 * @since    Version 0.2.0
 */

require __DIR__ . '/../vendor/autoload.php';

use LayerShifter\TLDExtract\Factory;

$result = Factory::get('http://forums.news.cnn.com/');

var_dump($result->toJson());

var_dump($result['subdomain']);
var_dump($result->tld);
