<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/12
 * Time: 17:58
 */
require_once '../../vendor/autoload.php';

$parser = new \lywzx\epub\EpubParser('../alice.epub');
$parser->parse();

$chapters = array_keys($parser->getManifestByType('application/xhtml+xml'));

echo $parser->getChapter($chapters[0]);
