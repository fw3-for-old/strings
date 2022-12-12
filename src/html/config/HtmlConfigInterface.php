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

namespace fw3_for_old\strings\html\config;

use fw3_for_old\strings\converter\Convert;

/**
 * 簡易的なHTML構築ビルダ設定インターフェースです。
 */
interface HtmlConfigInterface
{
    /**
     * @const   string  エスケープタイプ：HTML
     */
    const ESCAPE_TYPE_HTML          = Convert::ESCAPE_TYPE_HTML;

    /**
     * @const   string  エスケープタイプ：JavaScript
     */
    const ESCAPE_TYPE_JAVASCRIPT    = Convert::ESCAPE_TYPE_JAVASCRIPT;

    /**
     * @const   string  エスケープタイプ：JavaScript (省略形)
     */
    const ESCAPE_TYPE_JS            = Convert::ESCAPE_TYPE_JS;

    /**
     * @const   string  エスケープタイプ：CSS
     */
    const ESCAPE_TYPE_CSS           = Convert::ESCAPE_TYPE_CSS;

    /**
     * @var string  エスケープタイプ
     */
    const DEFAULT_ESCAPE_TYPE   = self::ESCAPE_TYPE_HTML;

    /**
     * @var string  JS向けエンコーディング
     */
    const ENCODING_FOR_JS   = 'UTF-8';

    /**
     * @var string  エンコーディング
     */
    const DEFAULT_ENCODING  = 'UTF-8';

    /**
     * ファクトリ
     *
     * @param   array   $options    オプション
     * @return  static  このインスタンス
     */
    public static function factory($options = array());

    /**
     * エスケープタイプを取得・設定します。
     *
     * @param   null|string $escape_format  エスケープタイプ
     * @return  string  エスケープタイプまたはこのクラスパス
     */
    public function escapeType($escape_type = null);

    /**
     * エンコーディングを取得・設定します。
     *
     * @param   null|string $encoding   エンコーディング
     * @return  string  エンコーディングまたはこのクラスパス
     */
    public function encoding($encoding = null);
}
