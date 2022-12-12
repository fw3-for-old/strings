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

namespace fw3_for_old\strings\html\elements\traits;

use fw3_for_old\strings\html\HtmlTextNode;
use fw3_for_old\strings\html\traits\Htmlable;

/**
 * 簡易的なHTML構築ビルダインターフェースです。
 */
interface HtmlElementInterface extends Htmlable
{
    /**
     * 属性を設定・取得します。
     *
     * @param   string                      $attribute_name 属性名
     * @param   null|string|array           $value          属性値
     * @return  string|array|null|static    属性値またはこのインスタンス
     */
    public function attr($attribute_name, $value = null);

    /**
     * コンテキストを設定します。
     *
     * 設定済みの子要素が存在する場合、全て削除の上コンテキストに置き換えられます。
     *
     * @param   string  $context    コンテキスト
     * @return  static  このインスタンス
     */
    public function context($context);

    /**
     * コンテキストを追加します。
     *
     * @param   HtmlTextNode|string $context    子要素
     * @return  static  このインスタンス
     */
    public function appendContext($context);

    /**
     * 子要素を追加します。
     *
     * @param   Htmlable    $child_node 子要素
     * @return  static      このインスタンス
     */
    public function appendChildNode($child_node);

    /**
     * 子要素を設定・取得します。
     *
     * @param   array   $children   子要素
     * @return  static  このインスタンス
     */
    public function children($children = null);
}
