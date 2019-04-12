<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/12
 * Time: 17:38
 */
require_once '../../vendor/autoload.php';

$parser =new lywzx\epub\EpubParser('../alice.epub');
$parser->parse();

// get all dc info
echo '<pre>';
var_dump($parser->getDcItem());
echo '</pre>';

// get rights
echo "rights: ".$parser->getDcItem('rights');
echo '<br/>';

// get creator
echo "creator: ". $parser->getDcItem('creator');
echo '<br/>';


