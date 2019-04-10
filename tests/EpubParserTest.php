<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2019/4/8
 * Time: 下午4:52
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use lywzx\epub\EpubParser;


class EpubParserTest extends TestCase
{
    public function testParse() {
        $epub = new EpubParser('./alice.epub');
        $epub->parse();

    }
}
