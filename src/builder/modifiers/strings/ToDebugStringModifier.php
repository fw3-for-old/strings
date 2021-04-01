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

namespace fw3_for_old\strings\builder\modifiers\strings;

use fw3_for_old\strings\builder\modifiers\AbstractModifier;
use fw3_for_old\strings\converter\Convert;

/**
 * String Builder: to string notation Modifier
 */
class ToDebugStringModifier extends AbstractModifier
{
    /**
     * 置き換え値を修飾して返します。
     *
     * @param   mixed   $replace    置き換え値
     * @param   array   $parameters パラメータ
     * @param   array   $context    コンテキスト
     * @return  mixed   修飾した置き換え値
     */
    public static function modify($replace, array $parameters = array(), array $context = array())
    {
        $depth  = isset($parameters['depth']) ? $parameters['depth'] : (
            isset($parameters[0]) ? $parameters[0] : 0
        );

        return Convert::toDebugString((string) $replace, $depth);
    }
}
