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

namespace fw3_for_old\strings\html\traits;

use fw3_for_old\strings\converter\Convert;
use fw3_for_old\strings\html\config\HtmlConfigInterface;

/**
 * 簡易的なHTML構築ビルダインターフェースです。
 */
abstract class AbstractHtmlable implements Htmlable
{
    /**
     * @var HtmlConfigInterface 簡易的なHTML構築ビルダ設定
     */
    protected $htmlConfig   = null;

    /**
     * 簡易的なHTML構築ビルダ設定を取得・設定します。
     *
     * @param   null|HtmlConfigInterface    $html_config_class  簡易的なHTML構築ビルダ設定
     * @return  HtmlConfigInterface|static  簡易的なHTML構築ビルダ設定またはこのインスタンス
     */
    public function htmlConfig($htmlConfig = null)
    {
        if ($htmlConfig === null && \func_num_args() === 0) {
            return $this->htmlConfig;
        }

        if (!($htmlConfig instanceof HtmlConfigInterface)) {
            throw new \Exception(\sprintf('利用できない簡易的なHTML構築ビルダ設定を指定されました。escape_format:%s', Convert::toDebugString($htmlConfig)));
        }

        $this->htmlConfig   = $htmlConfig;

        return $this;
    }
}
