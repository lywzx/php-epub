<?php

require '../src/EpubParser.php';

//$parse = new \liuyang\epub\EpubParser('./alice-with-pwd.epub');
$parse = new \liuyang\epub\EpubParser('./alice.epub');
$parse->parse();

//var_dump($parse->getTOC());


//var_dump($parse->getManifestByType('text/css'));
//var_dump($parse->getManifest());
//var_dump($parse->getChapter('item32'));
//var_dump($parse->getDcItem());
echo $parse->getImage('item25');
//echo sprintf('<pre>%s</pre>', print_r($parse, true));