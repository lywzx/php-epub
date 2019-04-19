<?php
/**
 * Created by PhpStorm.
 * User: LiuYang
 * Date: 2019/4/19
 * Time: 17:36
 */

namespace lywzx\epub;


class Util
{
    /**
     * two directory concat
     * @param string $base
     * @param string $relativeFile
     * @param bool $baseIsFile
     * @return string
     */
    static function directoryConcat(string $base, string $relativeFile, bool $baseIsFile = false): string {
        if ($baseIsFile) {
            $base = preg_replace('#(?:\/[^/]*)$#', '', $base);
        }
        $filter = function($item) {
            return !($item === '' || $item === '.');
        };
        $base = array_filter(explode('/', $base), $filter);
        $relativeFile = array_filter(explode('/', $relativeFile), $filter);

        $retPath = array_merge($base, $relativeFile);
        $resultPath = [];
        $backCount  = [];
        while ( !is_null($current = array_pop($retPath)) ) {
            if ($current === '.') {
                continue;
            }
            if ($current === '..') {
                $backCount[] = '..';
                continue;
            }
            if (count($backCount)) {
                array_pop($backCount);
                continue;
            }
            $resultPath[] = $current;
        }

        return implode('/', array_merge($backCount, array_reverse($resultPath)));
    }
}
