<?php
/**    _______       _______
 *    / ____/ |     / /__  /
 *   / /_   | | /| / / /_ <
 *  / __/   | |/ |/ /___/ /
 * /_/      |__/|__//____/
 *
 * Flywheel3: the inertia php framework for old php versions
 *
 * @category    Flywheel3
 * @package     strings
 * @author      akira wakaba <wakabadou@gmail.com>
 * @copyright   Copyright (c) @2020  Wakabadou (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/). All rights reserved.
 * @license     http://opensource.org/licenses/MIT The MIT License.
 *              This software is released under the MIT License.
 * @varsion     1.0.0
 */

namespace fw3_for_old\strings\converter;

use InvalidArgumentException;
use ReflectionObject;

/**
 * 変数の文字列変換メソッド群です。
 */
class Convert
{
    //==============================================
    // constants
    //==============================================
    // escape
    //----------------------------------------------
    /**
     * @const   string  エスケープタイプ：HTML
     */
    const ESCAPE_TYPE_HTML          = 'html';

    /**
     * @const   string  エスケープタイプ：JavaScript
     */
    const ESCAPE_TYPE_JAVASCRIPT    = 'javascript';

    /**
     * @const   string  エスケープタイプ：JavaScript (省略形)
     */
    const ESCAPE_TYPE_JS            = 'javascript';

    /**
     * @const   int     基底となるエスケープフラグ
     */
    const BASE_ESCAPE_FLAGS         =  ENT_QUOTES;

    /**
     * @const   int     HTML関連のエスケープフラグ
     */
    public static $HTML_ESCAPE_FLAGS    = array(
    );

    /**
     * @const   array   デフォルトでの文字エンコーディング検出順序
     */
    public static $DETECT_ENCODING_ORDER    = array(
        'eucJP-win',
        'SJIS-win',
        'JIS',
        'ISO-2022-JP',
        'UTF-8',
        'ASCII',
    );

    /**
     * @const   string  JavaScript用エンコーディング
     */
    const JAVASCRIPT_ENCODING    = 'UTF-8';

    //==============================================
    // methods
    //==============================================
    // case conversion
    //----------------------------------------------
    /**
     * 文字列をスネークケースに変換します。
     *
     * @param   string              $subject    スネークケースに変換する文字列
     * @param   bool                $trim       変換後に先頭の"_"をトリムするかどうか trueの場合はトリムする
     * @param   string|array|null   $separator  単語の閾に用いる文字
     * @return  string  スネークケースに変換された文字列
     */
    public static function toSnakeCase($subject, $trim = true, $separator = array(' ', '-'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '_', $subject);
        }

        $subject    = preg_replace(mb_internal_encoding() === 'UTF-8' ? '/_*([A-Z])/u' : '/_*([A-Z])/', '_${1}', $subject);

        return $trim ? ltrim($subject, '_') : $subject;
    }

    /**
     * 文字列をアッパースネークケースに変換します。
     *
     * @param   string              $subject    アッパースネークケースに変換する文字列
     * @param   bool                $trim       変換後に先頭の"_"をトリムするかどうか trueの場合はトリムする
     * @param   string|array|null   $separator  単語の閾に用いる文字
     * @return  string              アッパースネークケースに変換された文字列
     */
    public static function toUpperSnakeCase($subject, $trim = true, $separator = array(' ', '-'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '_', $subject);
        }

        $subject    = preg_replace(mb_internal_encoding() === 'UTF-8' ? '/_*([A-Z])/u' : '/_*([A-Z])/', '_${1}', $subject);

        return strtoupper($trim ? ltrim($subject, '_') : $subject);
    }

    /**
     * 文字列をロウアースネークケースに変換します。
     *
     * @param   string              $subject    スネークケースに変換する文字列
     * @param   bool                $trim       変換後に先頭の"_"をトリムするかどうか trueの場合はトリムする
     * @param   string|array|null   $separator  単語の閾に用いる文字
     * @return  string              ロウアースネークケースに変換された文字列
     */
    public static function toLowerSnakeCase($subject, $trim = true, $separator = array(' ', '-'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '_', $subject);
        }

        $subject    = preg_replace(mb_internal_encoding() === 'UTF-8' ? '/_*([A-Z])/u' : '/_*([A-Z])/', '_${1}', $subject);

        return strtolower($trim ? ltrim($subject, '_') : $subject);
    }

    /**
     * 文字列をチェインケースに変換します。
     *
     * @param   string      $subject        チェインケースに変換する文字列
     * @param   bool        $trim           変換後に先頭の"-"をトリムするかどうか trueの場合はトリムする
     * @param   string|null $separator  単語の閾に用いる文字
     * @return  string      チェインケースに変換された文字列
     */
    public static function toChainCase($subject, $trim = true, $separator = array(' ', '_'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '-', $subject);
        }

        $subject    = preg_replace(mb_internal_encoding() === 'UTF-8' ? '/\-*([A-Z])/u' : '/\-*([A-Z])/', '-${1}', $subject);

        return $trim ? ltrim($subject, '-') : $subject;
    }

    /**
     * 文字列をアッパーチェインケースに変換します。
     *
     * @param   string      $subject    チェインケースに変換する文字列
     * @param   bool        $trim       変換後に先頭の"-"をトリムするかどうか trueの場合はトリムする
     * @param   string|null $separator  単語の閾に用いる文字
     * @return  string      アッパーチェインケースに変換された文字列
     */
    public static function toUpperChainCase($subject, $trim = true, $separator = array(' ', '_'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '-', $subject);
        }

        $subject    = preg_replace(mb_internal_encoding() === 'UTF-8' ? '/\-*([A-Z])/u' : '/\-*([A-Z])/', '-${1}', $subject);

        return strtoupper($trim ? ltrim($subject, '-') : $subject);
    }

    /**
     * 文字列をロウアーチェインケースに変換します。
     *
     * @param   string      $subject    チェインケースに変換する文字列
     * @param   bool        $trim       変換後に先頭の"-"をトリムするかどうか trueの場合はトリムする
     * @param   string|null $separator  単語の閾に用いる文字
     * @return  string      ロウアーチェインケースに変換された文字列
     */
    public static function toLowerChainCase($subject, $trim = true, $separator = array(' ', '_'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '-', $subject);
        }

        $subject    = preg_replace(mb_internal_encoding() === 'UTF-8' ? '/\-*([A-Z])/u' : '/\-*([A-Z])/', '-${1}', $subject);

        return strtolower($trim ? ltrim($subject, '-') : $subject);
    }

    /**
     * 文字列をキャメルケースに変換します。
     *
     * @param   string      $subject    キャメルケースに変換する文字列
     * @param   string|null $separator  単語の閾に用いる文字
     * @return  string      キャメルケースに変換された文字列
     */
    public static function toCamelCase($subject, $separator = array(' ', '-'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '_', $subject);
        }

        $subject    = ltrim(strtr($subject, array('_' => ' ')), ' ');
        return strtr(mb_substr($subject, 0, 1) . mb_substr(ucwords($subject), 1), array(' ' => ''));
    }

    /**
     * 文字列をスネークケースからアッパーキャメルケースに変換します。
     *
     * @param   string      $subject    アッパーキャメルケースに変換する文字列
     * @param   string|null $separator  単語の閾に用いる文字
     * @return  string      アッパーキャメルケースに変換された文字列
     */
    public static function toUpperCamelCase($subject, $separator = array(' ', '-'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '_', $subject);
        }

        return ucfirst(strtr(ucwords(strtr($subject, array('_' => ' '))), array(' ' => '')));
    }

    /**
     * 文字列をスネークケースからロウアーキャメルケースに変換します。
     *
     * @param   string      $subject    ロウアーキャメルケースに変換する文字列
     * @param   string|null $separator  単語の閾に用いる文字
     * @return  string      ロウアーキャメルケースに変換された文字列
     */
    public static function toLowerCamelCase($subject, $separator = array(' ', '-'))
    {
        if ($separator !== null) {
            $subject    = str_replace($separator, '_', $subject);
        }

        return lcfirst(strtr(ucwords(strtr($subject, array('_' => ' '))), array(' ' => '')));
    }

    //----------------------------------------------
    // escape
    //----------------------------------------------
    /**
     * 文字列のエスケープを行います。
     *
     * @param   string      $value      エスケープする文字列
     * @param   string      $type       エスケープタイプ
     * @param   array       $options    オプション
     * @param   string|null $encoding   エンコーディング
     * @return  string  エスケープされた文字列
     */
    public static function escape($value, $type = self::ESCAPE_TYPE_HTML, array $options = array(), $encoding = null)
    {
        if ($type === static::ESCAPE_TYPE_HTML) {
            return static::htmlEscape($value, $options, $encoding);
        } elseif ($type === static::ESCAPE_TYPE_JAVASCRIPT) {
            return static::jsEscape($value, $options, $encoding);
        } elseif ($type === static::ESCAPE_TYPE_JS) {
            return static::jsEscape($value, $options, $encoding);
        }

        return $value;
    }

    /**
     * HTML文字列のエスケープを行います。
     *
     * @param   string      $value      エスケープするHTML文字列
     * @param   array       $options    オプション
     *  [
     *      'flags' => array htmlspecialcharsに与えるフラグ
     *  ]
     * @param   string|null $encoding   エンコーディング
     * @return  string  エスケープされたHTML文字列
     */
    public static function htmlEscape($value, array $options = array(), $encoding = null)
    {
        $encoding   = isset($encoding) ? $encoding : mb_internal_encoding();

        if (!mb_check_encoding($value, $encoding)) {
            throw new InvalidArgumentException(sprintf('不正なエンコーディングが検出されました。encoding:%s, value_encoding:%s', Convert::toDebugString(isset($encoding) ? $encoding : mb_internal_encoding()), Convert::toDebugString(mb_detect_encoding($value, static::$DETECT_ENCODING_ORDER, true))));
        }

        $flags  = static::BASE_ESCAPE_FLAGS;
        foreach (isset($options['flags']) ? (array) $options['flags'] : array() as $flag) {
            $flags  |= $flag;
        }

        $enable_html_escape_flag    = false;
        foreach (static::$HTML_ESCAPE_FLAGS as $html_flag) {
            if ($enable_html_escape_flag = (0 !== $flags & $html_flag)) {
                break;
            }
        }

        if (!$enable_html_escape_flag) {
            $flags  |= \ENT_QUOTES;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            switch (strtoupper($encoding)) {
                case 'SJIS-WIN':
                case 'CP932':
                case 'EUCJP-WIN':
                    throw new \InvalidArgumentException(sprintf('PHP5.4.0未満ではhtmlspecialcharsに次のエンコーディングは使用できません。encoding:%s', $encoding));
            }
        }

        return htmlspecialchars($value, $flags, $encoding);
    }

    /**
     * JavaScript文字列のエスケープを行います。
     *
     * @param   string      $value      エスケープするJavaScript文字列
     * @param   array       $options    オプション
     * @return  string      エスケープされたJavaScript文字列
     * @see https://blog.ohgaki.net/javascript-string-escape
     */
    public static function jsEscape($value, array $options = array())
    {
        if (!mb_check_encoding($value, self::JAVASCRIPT_ENCODING)) {
            throw new InvalidArgumentException(sprintf('不正なエンコーディングが検出されました。JavaScriptエスケープ対象の文字列はUTF-8である必要があります。value_encoding:%s', Convert::toDebugString(mb_detect_encoding($value, static::$DETECT_ENCODING_ORDER, true))));
        }

        $map = array(
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,0,0, // 49
            0,0,0,0,0,0,0,0,1,1,
            1,1,1,1,1,0,0,0,0,0,
            0,0,0,0,0,0,0,0,0,0,
            0,0,0,0,0,0,0,0,0,0,
            0,1,1,1,1,1,1,0,0,0, // 99
            0,0,0,0,0,0,0,0,0,0,
            0,0,0,0,0,0,0,0,0,0,
            0,0,0,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1, // 149
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1, // 199
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1, // 249
            1,1,1,1,1,1,1, // 255
        );

        // 文字エンコーディングはUTF-8
        $mblen = mb_strlen($value, self::JAVASCRIPT_ENCODING);
        $utf32 = mb_convert_encoding($value, 'UTF-32', self::JAVASCRIPT_ENCODING);
        $convmap = array(0x0, 0xffffff, 0, 0xffffff);
        for ($i=0, $encoded=''; $i < $mblen; $i++) {
            // Unicodeの仕様上、最初のバイトは無視してもOK
            $c =  (ord($utf32[$i*4+1]) << 16 ) + (ord($utf32[$i*4+2]) << 8) + ord($utf32[$i*4+3]);
            if ($c < 256 && $map[$c]) {
                if ($c < 0x10) {
                    $encoded .= '\\x0'.base_convert((string) $c, 10, 16);
                } else {
                    $encoded .= '\\x'.base_convert((string) $c, 10, 16);
                }
            } else if ($c == 0x2028) {
                $encoded .= '\\u2028';
            } else if ($c == 0x2029) {
                $encoded .= '\\u2029';
            } else {
                $encoded .= mb_decode_numericentity('&#'.$c.';', $convmap, self::JAVASCRIPT_ENCODING);
            }
        }
        return $encoded;
    }

    //----------------------------------------------
    // json
    //----------------------------------------------
    /**
     * HTML上のJavaScriptとして評価される中で安全なJSON文字列を返します。
     *
     * @param   mixed   $value  JSON化する値
     * @param   int     $depth  最大の深さを設定します。正の数でなければいけません。
     * @return  string  JSON化された値
     */
    public static function toJson($value, $depth = 512)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    //----------------------------------------------
    // variable
    //----------------------------------------------
    /**
     * 変数に関する情報を文字列にして返します。
     *
     * @param   mixed   $var    変数に関する情報を文字列にしたい変数
     * @param   int     $depth  変数に関する情報を文字列にする階層の深さ
     * @return  string  変数に関する情報
     */
    public static function toDebugString($var, $depth = 0)
    {
        $type = gettype($var);

        switch ($type) {
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'integer':
                return (string) $var;
            case 'double':
                if (false === mb_strpos((string) $var, '.')) {
                    return sprintf('%s.0', $var);
                }
                return (string) $var;
            case 'string':
                return sprintf('\'%s\'', $var);
            case 'array':
                if ($depth < 1) {
                    return 'Array';
                }
                --$depth;
                $ret = array();
                foreach ($var as $key => $value) {
                    $ret[] = sprintf('%s => %s', static::toDebugString($key), static::toDebugString($value, $depth));
                }
                return sprintf('[%s]', implode(', ', $ret));
            case 'object':
                ob_start();
                var_dump($var);
                $object_status = ob_get_clean();

                $object_status = substr($object_status, 0, strpos($object_status, ' ('));
                $object_status = sprintf('object(%s)', substr($object_status, 6));

                if ($depth < 1) {
                    return $object_status;
                }

                --$depth;

                $ro = new ReflectionObject($var);

                $tmp_properties = array();
                foreach ($ro->getProperties() as $property) {
                    $state      = $property->isStatic() ? 'static' : 'dynamic';
                    $modifier   = $property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : ($property->isPrivate() ? 'private' : 'unkown modifier'));
                    $tmp_properties[$state][$modifier][] = $property;
                }

                $properties = array();
                foreach (array('static', 'dynamic') as $state) {
                    $state_text = $state === 'static' ? 'static ' : '';
                    foreach (array('public', 'protected', 'private', 'unkown modifier') as $modifier) {
                        foreach (isset($tmp_properties[$state][$modifier]) ? $tmp_properties[$state][$modifier] : array() as $property) {
                            $property->setAccessible(true);
                            $properties[] = sprintf('%s%s %s = %s', $state_text, $modifier, static::toDebugString($property->getName()), static::toDebugString($property->getValue($var), $depth));
                        }
                    }
                }

                return sprintf('%s {%s}', $object_status, implode(', ', $properties));
            case 'resource':
                return sprintf('%s %s', get_resource_type($var), $var);
            case 'resource (closed)':
                return sprintf('resource (closed) %s', $var);
            case 'NULL':
                return 'NULL';
            case 'unknown type':
            default:
                return 'unknown type';
        }
    }
}
