<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/12
 * Time: 22:39
 */

require_once '../../vendor/autoload.php';



$parser = new \lywzx\epub\EpubParser('../alice.epub', 'http://cdn.baidu.com/txt/dic', 'http://cdn.baidu.com/txt/dic');
$parser->parse();

// extract all to destination
if (!file_exists('./dist')) {
    mkdir('./dist');
}
$parser->extract('./dist');


// extract all image to destination
if (!file_exists('./dist1')) {
    mkdir('./dist1');
}
$parser->extract('./dist1', '/image\/\w+/');

// extract all file except image to destination
if (!file_exists('./dist2')) {
    mkdir('./dist2');
}
$parser->extract('./dist2', '/image\/\w+/', true);

// extract assign file to destination
if (!file_exists('./dist3')) {
    mkdir('./dist3');
}
$parser->extract('./dist3', ['19033/0.css']);
