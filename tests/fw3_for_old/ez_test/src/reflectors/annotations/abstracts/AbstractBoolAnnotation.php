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
 * @package     ez_test
 * @author      wakaba <wakabadou@gmail.com>
 * @copyright   2020 - Wakabadou (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license     http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion     1.0.0
 */

namespace fw3_for_old\ez_test\reflectors\annotations\abstracts;

/**
 * Abstract Bool Annotation
 */
abstract class AbstractBoolAnnotation extends AbstractAnnotation
{
    /**
     * 与えられた状態をパースして返します。
     *
     * @return  mixed   パース結果
     */
    protected function parse()
    {
        return !isset($this->options[0]) || \filter_var($this->options[0], \FILTER_VALIDATE_BOOLEAN);
    }
}
