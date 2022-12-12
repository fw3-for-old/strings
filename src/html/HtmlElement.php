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
use fw3_for_old\strings\html\traits\Htmlable;

/**
 * 簡易的なHTML要素構築ビルダです。
 */
class HtmlElement extends elements\traits\AbstractHtmlElement
{
    /**
     * factory
     *
     * @param   string  $element_name   要素名
     * @param   array   $attributes     属性
     * @param   array   $children       子要素
     * @return  static  このインスタンス
     */
    public static function factory($element_name, $children = array(), $attributes = array(), $htmlConfig = null)
    {
        return new static($element_name, $children, $attributes, $htmlConfig);
    }

    /**
     * constructor
     *
     * @param   string  $element_name   要素名
     * @param   array   $attributes     属性
     * @param   array   $children       子要素
     * @return  static  このインスタンス
     */
    public function __construct($element_name, $children = array(), $attributes = array(), $htmlConfig = null)
    {
        $this->elementName  = $element_name;
        $this->children     = \is_array($children) ? $children : array($children);
        $this->attributes   = $attributes;
        $this->htmlConfig   = $htmlConfig === null ? Html::htmlConfig() : $htmlConfig;
    }

    /**
     * sugar factory
     *
     * @param   string  $element_name   要素名
     * @param   array   $args           引数
     * @return  static  このインスタンス
     */
    public static function __callstatic($element_name, $args)
    {
        $children   = isset($args[0]) ? $args[0] : array();
        $attributes = isset($args[1]) ? $args[1] : array();
        $htmlConfig = isset($args[2]) ? $args[2] : null;

        return new static($element_name, $children, $attributes, $htmlConfig);
    }

    /**
     * 現在の状態を元にHTML文字列を構築し返します。
     *
     * @param   int     $indent_lv  インデントレベル
     * @return  string  構築したHTML文字列
     */
    public function toHtml($indent_lv = 0)
    {
        $indent = str_repeat(' ', $indent_lv * 4);

        $element_name   = Html::escape($this->elementName, $this->htmlConfig);

        $attributes = array(
            ''
        );

        foreach ($this->attributes as $attribute_name => $value) {
            if (!($value instanceof HtmlAttribute)) {
                if ($value === null) {
                    $value  = Html::attribute($attribute_name);
                } else {
                    $value  = Html::attribute($attribute_name, $value);
                }
            }

            $attributes[]   = $value->toHtml();
        }
        $attribute  = \implode(' ', $attributes);

        if (!empty($this->children)) {
            $children   = array();

            if ($this->elementName === 'script') {
                return \sprintf(
                    '%s<script%s>%s%s%s</%script>',
                    $indent,
                    $attribute,
                    "\n",
                    Html::escape((string) $this->children, HtmlConfigInterface::ESCAPE_TYPE_JS, HtmlConfigInterface::ENCODING_FOR_JS),
                    "\n"
                );
            }

            if (\count($this->children) === 1) {
                $use_lf = false;

                foreach ($this->children as $child) {
                    if (!($child instanceof Htmlable)) {
                        $child  = Html::textNode($child);
                    }

                    $use_lf = !($child instanceof HtmlTextNode);
                }

                return \sprintf(
                    '%s<%s%s>%s%s%s%s</%s>',
                    $indent,
                    $element_name,
                    $attribute,
                    $use_lf ? "\n" : '',
                    $child->toHtml($indent_lv + 1),
                    $use_lf ? "\n" : '',
                    $use_lf ? $indent : '',
                    $element_name
                );
            }

            $before_element_html    = null;
            foreach ($this->children as $child) {
                if (!($child instanceof Htmlable)) {
                    $child  = Html::textNode($child);
                }

                if ($child instanceof HtmlTextNode) {
                    $children[] = Html::textNode($child)->toHtml($indent_lv + 1);
                    $before_element_html    = false;
                } else {
                    if ($before_element_html !== false) {
                        $children[] = "\n";
                    }
                    $children[] = $child->toHtml($indent_lv + 1);
                    $before_element_html    = true;
                }
            }

            $children[] = "\n";

            return \sprintf(
                '%s<%s%s>%s%s</%s>',
                $indent,
                $element_name,
                $attribute,
                \implode('', $children),
                $indent,
                $element_name
            );
        }

        return \sprintf('%s<%s%s>', $indent, $element_name, $attribute);
    }
}
