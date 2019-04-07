<?php

require '../src/EpubParser.php';

$parse = new \liuyang\epub\EpubParser('./大学语文（第2版）-978-7-900895-06-6.epub');
$parse->read();
echo sprintf('<pre>%s</pre>', print_r($parse, true));