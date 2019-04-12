<?php

require_once '../../vendor/autoload.php';

$parser = new lywzx\epub\EpubParser('../alice.epub');
$parser->parse();


$chapters = array_keys($parser->getManifestByType('application/xhtml+xml'));

echo $parser->getChapterRaw($chapters[0]);

//echo $parser->getChapter($chapters[0]);
