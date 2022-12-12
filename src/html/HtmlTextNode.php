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
 * 簡易的なHTMLテキストノード構築ビルダです。
 */
class HtmlTextNode extends traits\AbstractHtmlable
{
    /**
     * @var string  値
     */
    protected $value;

    /**
     * constructor
     *
     * @param   string              $value      テキスト
     * @param   HtmlConfigInterface $htmlConfig コンフィグ
     * @return  static  このインスタンス
     */
    public static function factory($value, $htmlConfig = null)
    {
        return new static($value, $htmlConfig);
    }

    /**
     * constructor
     *
     * @param   string              $value      テキスト
     * @param   HtmlConfigInterface $htmlConfig コンフィグ
     */
    public function __construct($value, $htmlConfig = null)
    {
        $this->value        = $value;
        $this->htmlConfig   = $htmlConfig === null ? Html::htmlConfig() : $htmlConfig;
    }

    /**
     * 現在の状態を元にHTML文字列を構築し返します。
     *
     * @param   int     $indent_lv  インデントレベル
     * @return  string  構築したHTML文字列
     */
    public function toHtml($indent_lv = 0)
    {
        return Html::escape($this->value, $this->htmlConfig);
    }
}
