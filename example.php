<?php
/**
 * example.php
 *
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

require 'vendor/autoload.php';

use LayerShifter\TLDExtract\Extract;

Extract::extract('http://user@www.city.kawasaki.jp/search');
Extract::extract('http://user@www.www.ck/search');
Extract::extract('http://user@city.test.ck/search');
Extract::extract('http://user@city.ck/search');
Extract::extract('http://user@city.jp/search');
Extract::extract('nike.co.ck');
//Extract::extract('http://[1080:0:0:0:8:800:200C:417A]/index.html');
//Extract::extract('http://[1080:0:0:0:8:800:200C:417A]:90/index.html');
//Extract::extract('http://pikabu.ru:96/index.html');