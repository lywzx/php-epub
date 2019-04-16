<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/16
 * Time: 16:22
 */

require_once '../../vendor/autoload.php';

$parser = new \lywzx\epub\EpubParser('../alice.epub', 'https://cdn.lyblog.net', 'https://cdn.lyblog.net');
$parser->parse();

// extract all file except image to destination
if (!file_exists('./dist4')) {
    mkdir('./dist4');
}
$parser->extract('./dist4');
