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


class EpubParserExceptionTest extends TestCase
{

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Failed opening ebook:
     */
    public function testFileNotExistsException() {
        $epub = new EpubParser('./a.epub');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Error: can't get stream to epub file
     */
    public function testFileNotValidatedEpubFileException() {
        $epub = new EpubParser('./not_validated.epub');
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The epub file is not validated
     */
    public function testFileNotValidatedEpubFileMimeTypeException() {
        $epub = new EpubParser('./not_validated_mimetype.epub');
    }
}
