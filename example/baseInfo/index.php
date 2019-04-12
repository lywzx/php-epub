<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/12
 * Time: 11:20
 */
require_once '../../vendor/autoload.php';

$parse = new \lywzx\epub\EpubParser('../alice.epub');
$parse->parse();

echo sprintf('<pre>%s</pre>', print_r($parse, true));

