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

use fw3_for_old\strings\html\config\HtmlConfigInterface;

/**
 * 簡易的なHTML構築ビルダです。
 */
class HtmlAttribute extends traits\AbstractHtmlable
{
    /**
     * @var string  属性名
     */
    protected $attributeName;

    /**
     * @var null|string 属性値
     */
    protected $value    = null;

    /**
     * constructor
     *
     * @param   string              $attribute_name 属性名
     * @param   null|string         $value          属税値
     * @param   HtmlConfigInterface $htmlConfig     コンフィグ
     * @return  HtmlAttribute   このインスタンス
     */
    public static function factory($attribute_name, $value = null, $htmlConfig = null)
    {
        return new static($attribute_name, $value, $htmlConfig);
    }

    /**
     * constructor
     *
     * @param   string              $attribute_name 属性名
     * @param   null|string         $value          属税値
     * @param   HtmlConfigInterface $htmlConfig     コンフィグ
     */
    public function __construct($attribute_name, $value = null, $htmlConfig = null)
    {
        if ($value instanceof HtmlAttribute) {
            $value  = $value->value();
        }

        $this->attributeName    = $attribute_name;
        $this->value            = $value;
        $this->htmlConfig       = $htmlConfig === null ? Html::htmlConfig() : $htmlConfig;
    }

    /**
     * 属性を返します。
     *
     * @param   string  $attribute_name 属性名
     * @param   array   $args           引数
     * @return  HtmlAttribute    属性
     */
    public static function __callstatic($attribute_name, $args)
    {
        $value      = isset($args[0]) ? $args[0] : null;
        $htmlConfig = isset($args[1]) ? $args[1] : null;

        return new static($attribute_name, $value, $htmlConfig);
    }

    /**
     * 属性名を返します。
     *
     * @return  string  属性名
     */
    public function getName()
    {
        return $this->attributeName;
    }

    /**
     * 属性値を設定・取得します。
     *
     * @param   null|mixed   $value          属性値
     * @return  null|mixed|static   属性値またはこのインスタンス
     */
    public function value($value = null)
    {
        if ($value === null && \func_num_args() === 0) {
            return $this->value;
        }

        if ($value instanceof HtmlAttribute) {
            $value  = $value->value();
        }

        $this->value    = $value;

        return $this;
    }

    /**
     * 現在の状態を元にHTML文字列を構築し返します。
     *
     * @param   int     $indent_lv  インデントレベル
     * @return  string  構築したHTML文字列
     */
    public function toHtml($indent_lv = 0)
    {
        $htmlConfig = $this->htmlConfig();

        $attribute_name = $this->attributeName;
        $attribute_name = Html::escape($attribute_name, HtmlConfigInterface::ESCAPE_TYPE_HTML, $htmlConfig->encoding());

        $value  = $this->value;

        if ($value === null) {
            return Html::escape($attribute_name, HtmlConfigInterface::ESCAPE_TYPE_HTML, $htmlConfig->encoding());
        }

        if (\is_array($value)) {
            if ($this->attributeName === 'style') {
                foreach ($value as $idx => $style) {
                    $value[$idx]    = \trim($style, ' ;');
                }
                $value  = \sprintf('%s;', \implode('; ', $value));
            } else {
                $value  = \implode(' ', $value);
            }
        }

        return \sprintf(
            '%s="%s"',
            $attribute_name,
            Html::escape($value, HtmlConfigInterface::ESCAPE_TYPE_HTML, $htmlConfig->encoding())
        );
    }
}
