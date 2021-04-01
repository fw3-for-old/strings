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
use InvalidArgumentException;

/**
 * String Builder: Date Modifier
 */
class DateModifier extends AbstractModifier
{
    /**
     * @const   string  デフォルトフォーマット
     */
    const DEFAULT_FORMAT    = 'Y/m/d H:i:s';

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
        $format = isset($parameters['format']) ? $parameters['format'] : (
            isset($parameters[0]) ? $parameters[0] : static::DEFAULT_FORMAT
        );

        if (!is_string($format)) {
            throw new InvalidArgumentException('date関数の第一引数 format: が文字列ではありません。');
        }

        return date($format, (int) $replace);
    }
}
