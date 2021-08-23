<?php
namespace xiaoriri\utils;

class StringUtil
{

    public const TRIM_CHARACTERS = " \t\n\r\0\x0B\u{A0}";


    /**
     * 转小写
     * StringUtil::lower('Hello world'); // 'hello world'
     */
    public static function lower(string $s): string
    {
        return mb_strtolower($s, 'UTF-8');
    }


    /**
     * 首字母小写
     * StringUtil::firstLower('Hello world'); // 'hello world'
     */
    public static function firstLower(string $s): string
    {
        return self::lower(self::substring($s, 0, 1)) . self::substring($s, 1);
    }


    /**
     * 转大写
     * StringUtil::upper('Hello world'); // 'HELLO WORLD'
     */
    public static function upper(string $s): string
    {
        return mb_strtoupper($s, 'UTF-8');
    }


    /**
     * 首字母大写
     * StringUtil::firstUpper('hello world'); // 'Hello world'
     */
    public static function firstUpper(string $s): string
    {
        return self::upper(self::substring($s, 0, 1)) . self::substring($s, 1);
    }


    /**
     * 所有单词首字母大写
     * StringUtil::capitalize('Hello world'); // 'Hello World'
     */
    public static function capitalize(string $s): string
    {
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }


    public static function underlineToCamel(string $s)
    {
        $text = mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
        return str_replace( '_', '',$text);
    }


    /**
     * 规范化
     * 删除控制字符，将换行符规范化为“\n”，删除前导和尾随空行，修剪行上的结尾空格，将UTF-8规范化为NFC的正常形式。
     */
    public static function normalize(string $s): string
    {
        // convert to compressed normal form (NFC)
        if (class_exists('Normalizer', false) && ($n = \Normalizer::normalize($s, \Normalizer::FORM_C)) !== false) {
            $s = $n;
        }

        $s = self::normalizeNewLines($s);
        $s = preg_replace('#[\x00-\x08\x0B-\x1F\x7F-\x9F]+#u', '', $s);
        // remove control characters; leave \t + \n
        // right trim
        $s = preg_replace('#[\t ]+$#m', '', $s);
        // leading and trailing blank lines
        $s = trim($s, "\n");

        return $s;
    }


    /**
     * 规范化 换行符号
     * $unixLikeLines = StringUtil::normalizeNewLines($string);
     */
    public static function normalizeNewLines(string $s): string
    {
        return str_replace(["\r\n", "\r"], "\n", $s);
    }

    /**
     * 转换为网络url 可以用的字符
     * StringUtil::webalize('10. image_id', '._'); // '10.-image_id'
     * StringUtil::webalize('Hello world', null, false); // 'Hello-world'
     */
    public static function webalize(string $s, string $charlist = null, bool $lower = true): string
    {
        $s = self::toAscii($s);
        if ($lower) {
            $s = strtolower($s);
        }
        $s = preg_replace('#[^a-z0-9' . ($charlist !== null ? preg_quote($charlist, '#') : '') . ']+#i', '-', $s);
        $s = trim($s, '-');
        return $s;
    }


    /**
     * 去掉留白
     * StringUtil::trim('  Hello  '); // 'Hello'
     */
    public static function trim(string $s, string $charlist = self::TRIM_CHARACTERS): string
    {
        $charlist = preg_quote($charlist, '#');
        return self::replace($s, '#^[' . $charlist . ']+|[' . $charlist . ']+$#Du', '');
    }


    /**
     * 截取指定长度多了的省略
     * $text = 'Hello, how are you today?';
     * StringUtil::truncate($text, 5);       // 'Hell…'
     * StringUtil::truncate($text, 20);      // 'Hello, how are you…'
     * StringUtil::truncate($text, 30);      // 'Hello, how are you today?'
     * StringUtil::truncate($text, 20, '~'); // 'Hello, how are you~'
     */
    public static function truncate(string $s, int $maxLen, string $append = "\u{2026}"): string
    {
        if (self::length($s) > $maxLen) {
            $maxLen -= self::length($append);
            if ($maxLen < 1) {
                return $append;

            } elseif ($matches = self::match($s, '#^.{1,' . $maxLen . '}(?=[\s\x00-/:-@\[-`{-~])#us')) {
                return $matches[0] . $append;

            } else {
                return self::substring($s, 0, $maxLen) . $append;
            }
        }
        return $s;
    }


    /**
     * 缩进
     * 从左侧缩进多行文字。第二个参数设置应该使用多少缩进字符，
     * 而缩进本身是第三个参数（*tab*（默认情况下））。
     * StringUtil::indent('Nette');         // "\tNette"
     * StringUtil::indent('Nette', 2, '+'); // '++Nette'
     */
    public static function indent(string $s, int $level = 1, string $chars = "\t"): string
    {
        if ($level > 0) {
            $s = self::replace($s, '#(?:^|[\r\n]+)(?=[^\r\n])#', '$0' . str_repeat($chars, $level));
        }
        return $s;
    }


    /**
     * 指定长度不够左边留白
     * StringUtil::padLeft('Nette', 6);        // ' Nette'
     * StringUtil::padLeft('Nette', 8, '+*');  // '+*+Nette'
     */
    public static function padLeft(string $s, int $length, string $pad = ' '): string
    {
        $length = max(0, $length - self::length($s));
        $padLen = self::length($pad);
        return str_repeat($pad, (int)($length / $padLen)) . self::substring($pad, 0, $length % $padLen) . $s;
    }


    /**
     *指定长度不够右边留白
     * StringUtil::padRight('Nette', 6);       // 'Nette '
     * StringUtil::padRight('Nette', 8, '+*'); // 'Nette+*+'
     */
    public static function padRight(string $s, int $length, string $pad = ' '): string
    {
        $length = max(0, $length - self::length($s));
        $padLen = self::length($pad);
        return $s . str_repeat($pad, (int)($length / $padLen)) . self::substring($pad, 0, $length % $padLen);
    }


    /**
     * 字符串截取
     * StringUtil::substring('Nette Framework', 0, 5); // 'Nette'
     * StringUtil::substring('Nette Framework', 6);    // 'Framework'
     * StringUtil::substring('Nette Framework', -4);   // 'work'
     */
    public static function substring(string $s, int $start, int $length = null): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($s, $start, $length, 'UTF-8'); // MB is much faster
        } elseif (!extension_loaded('iconv')) {
            throw new \LogicException(__METHOD__ . '() requires extension ICONV or MBSTRING, neither is loaded.');
        } elseif ($length === null) {
            $length = self::length($s);
        } elseif ($start < 0 && $length < 0) {
            $start += self::length($s); // unifies iconv_substr behavior with mb_substr
        }
        return iconv_substr($s, $start, $length, 'UTF-8');
    }


    /**
     * 字符串反向显示
     * StringUtil::reverse('Nette'); // 'etteN'
     */
    public static function reverse(string $s): string
    {
        if (!extension_loaded('iconv')) {
            throw new \LogicException(__METHOD__ . '() requires ICONV extension that is not loaded.');
        }
        return iconv('UTF-32LE', 'UTF-8', strrev(iconv('UTF-8', 'UTF-32BE', $s)));
    }


    /**
     * 返回字符串的长度
     * StringUtil::length('Nette'); // 5
     */
    public static function length(string $s): int
    {
        return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen(utf8_decode($s));
    }

    /**
     * 判断开头
     * $haystack = 'Begins';
     * $needle = 'Be';
     * StringUtil::startsWith($haystack, $needle); // true
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }


    /**
     * 判断结尾
     * $haystack = 'Ends';
     * $needle = 'ds';
     * StringUtil::endsWith($haystack, $needle); // true
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }


    /**
     * 判断包含
     * $haystack = 'Contains';
     * $needle = 'tai';
     * StringUtil::contains($haystack, $needle); // true
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }


    /**
     * 比较指定长度的字符串是够相等,忽略大小写
     * StringUtil::compare('Nette', 'nette');     // true
     * StringUtil::compare('Nette', 'next', 2);   // true - two first characters match
     * StringUtil::compare('Nette', 'Latte', -2); // true - two last characters match
     */
    public static function compare(string $left, string $right, int $length = null): bool
    {
        if (class_exists('Normalizer', false)) {
            $left = \Normalizer::normalize($left, \Normalizer::FORM_D); // form NFD is faster
            $right = \Normalizer::normalize($right, \Normalizer::FORM_D); // form NFD is faster
        }

        if ($length < 0) {
            $left = self::substring($left, $length, -$length);
            $right = self::substring($right, $length, -$length);
        } elseif ($length !== null) {
            $left = self::substring($left, 0, $length);
            $right = self::substring($right, 0, $length);
        }
        return self::lower($left) === self::lower($right);
    }


    /**
     * 返回字符串数组的共同前缀
     * StringUtil::findPrefix('prefix-a', 'prefix-bb', 'prefix-c');   // 'prefix-'
     * StringUtil::findPrefix(['prefix-a', 'prefix-bb', 'prefix-c']); // 'prefix-'
     * StringUtil::findPrefix('Nette', 'is', 'great');
     *
     * @param  string[] $strings
     *
     * @return  string
     */
    public static function findPrefix(array $strings): string
    {
        $first = array_shift($strings);
        for ($i = 0; $i < strlen($first); $i++) {
            foreach ($strings as $s) {
                if (!isset($s[$i]) || $first[$i] !== $s[$i]) {
                    while ($i && $first[$i - 1] >= "\x80" && $first[$i] >= "\x80" && $first[$i] < "\xC0") {
                        $i--;
                    }
                    return substr($first, 0, $i);
                }
            }
        }
        return $first;
    }


    /**
     * 找到字符串第几次出现之前的字符串
     * StringUtil::before('Nette_is_great', '_', 1);  // 'Nette'
     * StringUtil::before('Nette_is_great', '_', -2); // 'Nette'
     * StringUtil::before('Nette_is_great', ' ');     // null
     * StringUtil::before('Nette_is_great', '_', 3);  // null
     */
    public static function before(string $haystack, string $needle, int $nth = 1): ?string
    {
        $pos = self::pos($haystack, $needle, $nth);
        return $pos === null ? null : substr($haystack, 0, $pos);
    }


    /**
     * 找到字符串第几次出现之后的字符串
     * StringUtil::after('Nette_is_great', '_', 2);  // 'great'
     * StringUtil::after('Nette_is_great', '_', -1); // 'great'
     * StringUtil::after('Nette_is_great', ' ');     // null
     * StringUtil::after('Nette_is_great', '_', 3);  // null
     */
    public static function after(string $haystack, string $needle, int $nth = 1): ?string
    {
        $pos = self::pos($haystack, $needle, $nth);
        return $pos === null ? null : substr($haystack, $pos + strlen($needle));
    }


    /**
     * 找到字符转第几次出现的位置
     * StringUtil::indexOf('abc abc abc', 'abc', 2);  // 4
     * StringUtil::indexOf('abc abc abc', 'abc', -1); // 8
     * StringUtil::indexOf('abc abc abc', 'd');       // null
     */
    public static function indexOf(string $haystack, string $needle, int $nth = 1): ?int
    {
        $pos = self::pos($haystack, $needle, $nth);
        return $pos === null ? null : self::length(substr($haystack, 0, $pos));
    }


    /**
     * 移除字符串中所有非utf8的字符
     */
    public static function fixUTF8(string $s): string
    {
        // removes xD800-xDFFF, x110000 and higher
        return htmlspecialchars_decode(htmlspecialchars($s, ENT_NOQUOTES | ENT_IGNORE, 'UTF-8'), ENT_NOQUOTES);
    }

    /**
     * 检查字符串是否是utf格式
     */
    public static function checkUTF8(string $s): bool
    {
        return $s === self::fixUTF8($s);
    }


    /**
     * 判断字符串是utf-8 还是gb2312
     * @param string $str
     * @param string $default
     * @return string
     */
    public static function utf8_gb2312($str, $default = 'gb2312')
    {
        $str = preg_replace("/[\x01-\x7F]+/", "", $str);
        if (empty($str)) {
            return $default;
        }

        $preg = array(
            "gb2312" => "/^([\xA1-\xF7][\xA0-\xFE])+$/", //正则判断是否是gb2312
            "utf-8" => "/^[\x{4E00}-\x{9FA5}]+$/u",      //正则判断是否是汉字(utf8编码的条件了)，这个范围实际上已经包含了繁体中文字了
        );

        if ($default == 'gb2312') {
            $option = 'utf-8';
        } else {
            $option = 'gb2312';
        }

        if (!preg_match($preg[$default], $str)) {
            return $option;
        }
        $str = @iconv($default, $option, $str);

        //不能转成 $option, 说明原来的不是 $default
        if (empty($str)) {
            return $option;
        }
        return $default;
    }

    /**
     * utf-8和gb2312自动转化
     * @param string $string
     * @param string $outEncoding
     * @return string
     */
    public static function safeEncoding($string, $outEncoding = 'UTF-8')
    {
        $encoding = "UTF-8";
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string{$i}) < 128) {
                continue;
            }

            if ((ord($string{$i}) & 224) == 224) {
                // 第一个字节判断通过
                $char = $string{++$i};
                if ((ord($char) & 128) == 128) {
                    // 第二个字节判断通过
                    $char = $string{++$i};
                    if ((ord($char) & 128) == 128) {
                        $encoding = "UTF-8";
                        break;
                    }
                }
            }
            if ((ord($string{$i}) & 192) == 192) {
                // 第一个字节判断通过
                $char = $string{++$i};
                if ((ord($char) & 128) == 128) {
                    // 第二个字节判断通过
                    $encoding = "GB2312";
                    break;
                }
            }
        }

        if (strtoupper($encoding) == strtoupper($outEncoding)) {
            return $string;
        } else {
            return @iconv($encoding, $outEncoding, $string);
        }
    }

    /**
     * utf8转ASCII
     */
    public static function toAscii(string $s): string
    {
        $iconv = defined('ICONV_IMPL') ? trim(ICONV_IMPL, '"\'') : null;
        static $transliterator = null;
        if ($transliterator === null) {
            if (class_exists('Transliterator', false)) {
                $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
            } else {
                trigger_error(__METHOD__ . "(): it is recommended to enable PHP extensions 'intl'.", E_USER_NOTICE);
                $transliterator = false;
            }
        }

        // remove control characters and check UTF-8 validity
        $s = preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]#u', '', $s);
        // transliteration (by Transliterator and iconv) is not optimal, replace some characters directly
        $s = strtr($s, [
            "\u{201E}" => '"',
            "\u{201C}" => '"',
            "\u{201D}" => '"',
            "\u{201A}" => "'",
            "\u{2018}" => "'",
            "\u{2019}" => "'",
            "\u{B0}" => '^',
            "\u{42F}" => 'Ya',
            "\u{44F}" => 'ya',
            "\u{42E}" => 'Yu',
            "\u{44E}" => 'yu',
            "\u{c4}" => 'Ae',
            "\u{d6}" => 'Oe',
            "\u{dc}" => 'Ue',
            "\u{1e9e}" => 'Ss',
            "\u{e4}" => 'ae',
            "\u{f6}" => 'oe',
            "\u{fc}" => 'ue',
            "\u{df}" => 'ss'
        ]); // „ “ ” ‚ ‘ ’ ° Я я Ю ю Ä Ö Ü ẞ ä ö ü ß
        if ($iconv !== 'libiconv') {
            $s = strtr($s, [
                "\u{AE}" => '(R)',
                "\u{A9}" => '(c)',
                "\u{2026}" => '...',
                "\u{AB}" => '<<',
                "\u{BB}" => '>>',
                "\u{A3}" => 'lb',
                "\u{A5}" => 'yen',
                "\u{B2}" => '^2',
                "\u{B3}" => '^3',
                "\u{B5}" => 'u',
                "\u{B9}" => '^1',
                "\u{BA}" => 'o',
                "\u{BF}" => '?',
                "\u{2CA}" => "'",
                "\u{2CD}" => '_',
                "\u{2DD}" => '"',
                "\u{1FEF}" => '',
                "\u{20AC}" => 'EUR',
                "\u{2122}" => 'TM',
                "\u{212E}" => 'e',
                "\u{2190}" => '<-',
                "\u{2191}" => '^',
                "\u{2192}" => '->',
                "\u{2193}" => 'V',
                "\u{2194}" => '<->'
            ]); // ® © … « » £ ¥ ² ³ µ ¹ º ¿ ˊ ˍ ˝ ` € ™ ℮ ← ↑ → ↓ ↔
        }

        if ($transliterator) {
            $s = $transliterator->transliterate($s);
            // use iconv because The transliterator leaves some characters out of ASCII, eg → ʾ
            if ($iconv === 'glibc') {
                $s = strtr($s, '?',
                    "\x01"); // temporarily hide ? to distinguish them from the garbage that iconv creates
                $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
                $s = str_replace(['?', "\x01"], ['', '?'], $s); // remove garbage and restore ? characters
            } elseif ($iconv === 'libiconv') {
                $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            } else { // null or 'unknown' (#216)
                $s = preg_replace('#[^\x00-\x7F]++#', '', $s);
            }
        } elseif ($iconv === 'glibc' || $iconv === 'libiconv') {
            // temporarily hide these characters to distinguish them from the garbage that iconv creates
            $s = strtr($s, '`\'"^~?', "\x01\x02\x03\x04\x05\x06");
            if ($iconv === 'glibc') {
                // glibc implementation is very limited. transliterate into Windows-1250 and then into ASCII, so most Eastern European characters are preserved
                $s = iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $s);
                $s = strtr($s,
                    "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe\x96\xa0\x8b\x97\x9b\xa6\xad\xb7",
                    'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.');
                $s = preg_replace('#[^\x00-\x7F]++#', '', $s);
            } else {
                $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            }
            // remove garbage that iconv creates during transliteration (eg Ý -> Y')
            $s = str_replace(['`', "'", '"', '^', '~', '?'], '', $s);
            // restore temporarily hidden characters
            $s = strtr($s, "\x01\x02\x03\x04\x05\x06", '`\'"^~?');
        } else {
            $s = preg_replace('#[^\x00-\x7F]++#', '', $s);
        }
        return $s;
    }

    /**
     * 返回特殊utf字符编码  输入范围  0x0000..D7FF or 0xE000..10FFFF
     * Returns a specific character in UTF-8 from code point (number in range 0x0000..D7FF or 0xE000..10FFFF).
     * StringUtil::chr(0xA9); // '©'
     */
    public static function chr(int $code): string
    {
        if ($code < 0 || ($code >= 0xD800 && $code <= 0xDFFF) || $code > 0x10FFFF) {
            throw new \InvalidArgumentException('Code point must be in range 0x0 to 0xD7FF or 0xE000 to 0x10FFFF.');
        } elseif (!extension_loaded('iconv')) {
            throw new \LogicException(__METHOD__ . '() requires ICONV extension that is not loaded.');
        }
        return iconv('UTF-32BE', 'UTF-8//IGNORE', pack('N', $code));
    }


    /**
     * 正则切割
     * $res = StringUtil::split('One,  two,three', '~,\s*~');
     * // ['One', 'two', 'three']
     * $res = StringUtil::split('One,  two,three', '~(,)\s*~');
     * // ['One', ',', 'two', ',', 'three']
     */
    public static function split(string $subject, string $pattern, int $flags = 0): array
    {
        return preg_split($pattern, $subject, -1, $flags | PREG_SPLIT_DELIM_CAPTURE);
    }


    /**
     * 正则匹配
     * list($res) = StringUtil::match('One,  two,three', '~[a-z]+~i'); // 'One'
     * list($res) = StringUtil::match('One,  two,three', '~\d+~'); // null
     */
    public static function match(string $subject, string $pattern, int $flags = 0, int $offset = 0): ?array
    {
        if ($offset > strlen($subject)) {
            return null;
        }
        $m = null;
        return preg_match($pattern, $subject, $m, $flags, $offset) ? $m : null;
    }


    /**
     * 正则匹配全部
     * $res = StringUtil::matchAll('One,  two,tree', '~[a-z]+~i');
     * /*
     * [
     *      0 => ['One'],
     *      1 => ['two'],
     *      2 => ['three'],
     * ]
     * $res = StringUtil::matchAll('One,  two,three', '~\d+~'); // []
     */
    public static function matchAll(string $subject, string $pattern, int $flags = 0, int $offset = 0): array
    {
        if ($offset > strlen($subject)) {
            return [];
        }
        $m = null;
        preg_match_all($pattern, $subject, $m, ($flags & PREG_PATTERN_ORDER) ? $flags : ($flags | PREG_SET_ORDER),
            $offset);
        return $m;
    }


    /**
     * 正则替换
     * StringUtil::replace('One, two,three', '~[a-z]+~i', '*');
     * // '*,  *,*'
     * StringUtil::replace('One,  two,three', [
     * '~[a-z]+~i' => '*',
     * '~\s+~' => '+',
     * ]);
     * // '*,+*,*'
     * StringUtil::replace('One,  two,three', '~[a-z]+~i', function (array $m): string {
     * return strrev($m[0]);
     * });
     * // 'enO,  owt,eerht'
     */
    public static function replace(string $subject, $pattern, $replacement = '', int $limit = -1): string
    {
        if (is_object($replacement) || is_array($replacement)) {
            if (!is_callable($replacement, false, $textual)) {
                throw new \InvalidArgumentException("Callback '$textual' is not callable.");
            }
            return preg_replace_callback($pattern, $replacement, $subject, $limit);
        } elseif (is_array($pattern) && is_string(key($pattern))) {
            $replacement = array_values($pattern);
            $pattern = array_keys($pattern);
        }

        $text= preg_replace($pattern, $replacement, $subject, $limit);
        echo $text;
        return $text;

    }


    /**
     * Returns position in bytes of $nth occurence of $needle in $haystack or null if the needle was not found.
     */
    private static function pos(string $haystack, string $needle, int $nth = 1): ?int
    {
        if (!$nth) {
            return null;
        } elseif ($nth > 0) {
            if ($needle === '') {
                return 0;
            }
            $pos = 0;
            while (($pos = strpos($haystack, $needle, $pos)) !== false && --$nth) {
                $pos++;
            }
        } else {
            $len = strlen($haystack);
            if ($needle === '') {
                return $len;
            }
            $pos = $len - 1;
            while (($pos = strrpos($haystack, $needle, $pos - $len)) !== false && ++$nth) {
                $pos--;
            }
        }
        return $pos === false ? null : $pos;
    }
}