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

namespace fw3_for_old\strings\html;

use fw3_for_old\strings\converter\Convert;
use fw3_for_old\strings\html\config\HtmlConfig;
use fw3_for_old\strings\html\config\HtmlConfigInterface;
use fw3_for_old\strings\html\traits\Htmlable;

/**
 * 簡易的なHTML構築ビルダファクトリです。
 */
abstract class Html
{
    /**
     * @var HtmlConfigInterface 簡易的なHTML構築ビルダ設定
     */
    protected static $htmlConfig    = null;

    /**
     * constructor
     */
    private function __construct()
    {
    }

    /**
     * 要素を返します。
     *
     * @param   string  $element_name   要素名
     * @param   array   $args           引数
     * @return  HtmlElement 要素
     */
    public static function __callStatic($element_name, $args)
    {
        $children   = isset($args[0]) ? $args[0] : array();
        $attributes = isset($args[1]) ? $args[1] : array();
        $htmlConfig = isset($args[2]) ? $args[2] : null;

        return HtmlElement::factory($element_name, $children, $attributes, $htmlConfig === null ? self::htmlConfig() : $htmlConfig);
    }

    /**
     * 要素を返します。
     *
     * @param   string              $element_name   要素名
     * @param   array               $children       子要素
     * @param   array               $attributes     属性
     * @param   HtmlConfigInterface $htmlConfig     コンフィグ
     * @return  HtmlElement 要素
     */
    public static function element($element_name, $children = array(), $attributes = array(), $htmlConfig = null)
    {
        return HtmlElement::factory($element_name, $children, $attributes, $htmlConfig === null ? self::htmlConfig() : $htmlConfig);
    }

    /**
     * 属性を返します。
     *
     * @param   string              $attribute_name 属性名
     * @param   mixed               $value          属性値
     * @param   HtmlConfigInterface $htmlConfig     コンフィグ
     * @return  HtmlAttribute    属性
     */
    public static function attribute($attribute_name, $value = null, $htmlConfig = null)
    {
        return HtmlAttribute::factory($attribute_name, $value, $htmlConfig === null ? self::htmlConfig() : $htmlConfig);
    }

    /**
     * データ属性を返します。
     *
     * @param   string              $data_name  データ属性名
     * @param   mixed               $value      属性値
     * @param   HtmlConfigInterface $htmlConfig コンフィグ
     * @return  HtmlAttribute    属性
     */
    public static function data($data_name, $value = null, $htmlConfig = null)
    {
        return HtmlAttribute::factory(\sprintf('data-%s', $data_name), $value, $htmlConfig === null ? self::htmlConfig() : $htmlConfig);
    }

    /**
     * テキストノードを返します。
     *
     * @param   string              $value      テキスト
     * @param   HtmlConfigInterface $htmlConfig コンフィグ
     * @return  HtmlTextNode    テキストノード
     */
    public static function textNode($value, $htmlConfig = null)
    {
        return HtmlTextNode::factory($value, $htmlConfig === null ? self::htmlConfig() : $htmlConfig);
    }

    /**
     * 簡易的なHTML構築ビルダ設定を取得・設定します。
     *
     * @param   null|HtmlConfigInterface    $html_config_class  簡易的なHTML構築ビルダ設定
     * @return  HtmlConfigInterface|string  簡易的なHTML構築ビルダ設定またはこのクラスパス
     */
    public static function htmlConfig($htmlConfig = null)
    {
        if ($htmlConfig === null && \func_num_args() === 0) {
            if (self::$htmlConfig === null) {
                self::$htmlConfig   = HtmlConfig::factory();
            }

            return self::$htmlConfig;
        }

        if (!($htmlConfig instanceof HtmlConfigInterface)) {
            throw new \Exception(\sprintf('利用できない簡易的なHTML構築ビルダ設定を指定されました。escape_type:%s', Convert::toDebugString($htmlConfig)));
        }

        self::$htmlConfig   = $htmlConfig;

        return \get_called_class();
    }

    /**
     * エスケープを実施します。
     *
     * @param   mixed                       $value          値
     * @param   string|HtmlConfigInterface  $escape_type    エスケープタイプ
     * @param   string|null                 $encoding       エンコーディング
     * @return  string  エスケープ済みの値
     */
    public static function escape($value, $escape_type = null, $encoding = null)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        if ($escape_type instanceof HtmlConfigInterface) {
            $encoding       = $escape_type->encoding();
            $escape_type    = $escape_type->escapetype();
        } else {
            $encoding       = $encoding === null ? self::htmlConfig()->encoding() : $encoding;
            $escape_type    = $escape_type === null ? self::htmlConfig()->escapetype() : $escape_type;
        }

        return Convert::escape($value, $escape_type, array(), $encoding);
    }
}
