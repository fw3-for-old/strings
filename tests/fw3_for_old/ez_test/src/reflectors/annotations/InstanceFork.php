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

namespace fw3_for_old\ez_test\reflectors\annotations;

use fw3_for_old\ez_test\reflectors\annotations\abstracts\AbstractBoolAnnotation;

/**
 * Annotation: InstanceFork
 *
 * 実行対象を別インスタンスで実行する
 */
class InstanceFork extends AbstractBoolAnnotation
{
    /**
     * @var string  アノテーション
     */
    const ANNOTATION    = '@instanceFork';
}
