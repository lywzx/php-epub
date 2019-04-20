<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/12
 * Time: 11:26
 */
require_once '../../vendor/autoload.php';

$parse = new \lywzx\epub\EpubParser('../alice.epub');
$parse->parse();

// get all mainfest
var_dump($parse->getManifest());

/*// get mainfest by id
var_dump($parse->getManifest('item24'));

// get mainfest by type
var_dump($parse->getManifestByType('application/xhtml+xml'));

// match regexp
var_dump($parse->getManifestByType('/image\/\w+/'));*/
