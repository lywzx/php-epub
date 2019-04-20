<?php

require_once '../../vendor/autoload.php';


$parser = new \lywzx\epub\EpubParser('../alice.epub');
$parser->parse();

var_dump($parser->getSpine());
