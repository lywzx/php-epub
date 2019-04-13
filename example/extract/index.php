<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/12
 * Time: 22:39
 */

require_once '../../vendor/autoload.php';

if (is_dir('./dist') && !file_exists('./dist')) {
    mkdir('./dist');
}
$parser = new \lywzx\epub\EpubParser('../alice.epub', 'http://cdn.baidu.com/txt/dic', 'http://cdn.baidu.com/txt/dic');
$parser->extract('./dist');
