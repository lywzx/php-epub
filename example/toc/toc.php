<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/12
 * Time: 13:33
 */
require_once '../../vendor/autoload.php';

$parse = new \lywzx\epub\EpubParser('../113933.epub');
$parse->parse();


// get all mainfest
echo '<pre>';
var_dump($parse->getTOC());
echo '</pre>';

