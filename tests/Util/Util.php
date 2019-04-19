<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/19
 * Time: 18:07
 */
declare(strict_types=1);

use \lywzx\epub\Util;

class UtilTest extends \PHPUnit\Framework\TestCase
{

    public function testDirectoryConcat() {
        //
        $this->assertEquals('../a/b/c', Util::directoryConcat('./', '../a/b/c'));
    }

    public function testDirectoryConcatNoSense() {
        //
        $this->assertEquals('../a/b/e/fg/gh', Util::directoryConcat('../a/./b', 'e/fg/gh'));
    }

    public function testDirectoryConcatWithOneBack() {
        $this->assertEquals('a/b/a/b/c', Util::directoryConcat('a/b/c', '../a/b/c'));
    }


    public function testDirectoryConcatWithTwoBack() {
        $this->assertEquals('a/b/f/a', Util::directoryConcat('a/b/c', 'a/../../f/a'));

    }

    public function testDirectoryConcatWithDeepBack() {
        $this->assertEquals('a/b/f/a', Util::directoryConcat('a/b/c', './a/../../f/a'));
    }


    public function testDirectoryConcatWithDeepBackTwo() {
        $this->assertEquals('../../../../../..', Util::directoryConcat('./../../..', './../../..'));
    }

    public function testDirectoryConcatWithDeepBackAnd() {
        $this->assertEquals('OEBPS/Images/1.24.png', Util::directoryConcat('OEBPS/Text/1.2.xhtml', '../Images/1.24.png', true));
    }
}
