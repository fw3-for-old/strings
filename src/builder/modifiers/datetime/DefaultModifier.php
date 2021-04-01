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

namespace fw3_for_old\strings\builder\modifiers\datetime;

use fw3_for_old\strings\builder\modifiers\AbstractModifier;

/**
 * String Builder: Default Modifier
 */
class DefaultModifier extends AbstractModifier
{
    /**
     * 置き換え値を修飾して返します。
     *
     * @param   mixed   $replace    置き換え値
     * @param   array   $parameters パラメータ
     * @param   array   $context
     * @return  mixed   修飾した置き換え値
     */
    public static function modify($replace, array $parameters = array(), array $context = array())
    {
        if ($replace !== null) {
            return $replace;
        }

        return isset($parameters['default']) ? $parameters['default'] : (
            isset($parameters['0']) ? $parameters['0'] : null
        );
    }
}
