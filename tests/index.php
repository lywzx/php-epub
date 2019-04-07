<?php

require '../src/EpubParser.php';

$parse = new \liuyang\epub\EpubParser('./大学语文（第2版）-978-7-900895-06-6.epub');
$parse->read();

var_dump($parse->getTOC());


var_dump($parse->getManifestByType('text/css'));
var_dump($parse->getManifest('main-css'));
//var_dump($parse->getDcItem());
echo sprintf('<pre>%s</pre>', print_r($parse, true));