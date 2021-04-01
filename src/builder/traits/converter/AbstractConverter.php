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

namespace fw3_for_old\strings\builder\traits\converter;

/**
 * コンバータインターフェース抽象クラス
 */
abstract class AbstractConverter implements ConverterInterface
{
    /**
     * 現在の変数名を元に値を返します。
     *
     * @param   string      $name   現在の変数名
     * @param   string      $search 変数名の元の文字列
     * @param   array       $values 変数
     * @return  string|null 値
     */
    public function __invoke($name, $search, array $values)
    {
        return static::convert($name, $search, $values);
    }
}
