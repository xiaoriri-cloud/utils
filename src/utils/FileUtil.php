<?php

namespace xiaoriri\utils;

class FileUtil
{

    /**
     * 格式化文件大小
     * 
     * @param int $size
     * @return string
     */
    public static function humanReadableSize($size): string
    {
        $list = ['KB', 'MB', 'GB'];
        $unit = 'bytes';
        if ($size < 1024) {
            return $size . ' ' . $unit;
        }
        $i = 0;
        while ($size >= 1024 && $i < count($list)) {
            $unit = $list[$i];
            $size /= 1024.0;
            $i++;
        }
        return sprintf("%.2f", $size) . " " . $unit;
    }
}
