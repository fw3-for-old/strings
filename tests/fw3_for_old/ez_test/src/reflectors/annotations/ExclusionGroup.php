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

use fw3_for_old\ez_test\reflectors\annotations\abstracts\AbstractAnnotation;

/**
 * Annotation: ExclusionGroup
 *
 * パラメータでグループが指定されている場合、アノテーションと同一グループであった場合のみ実行しない
 */
class ExclusionGroup extends AbstractAnnotation
{
    /**
     * @var string  アノテーション
     */
    const ANNOTATION    = '@exclusionGroup';

    /**
     * 与えられた状態をパースします。
     */
    protected function parse()
    {
        $value  = array();

        foreach (\str_getcsv(str_replace(' ', ',', $this->options), ',', '\'', "\\") as $group) {
            $value[$group]  = $group;
        }

        foreach ($this->stacks as $stack) {
            foreach (\str_getcsv(str_replace(' ', ',', $stack), ',', '\'', "\\") as $group) {
                $value[$group]  = $group;
            }
        }

        return $value;
    }

    /**
     * テストを実行しても良いかどうかを返します。
     *
     * @return  bool    テストを実行しても良いかどうか
     */
    public function executable()
    {
        $groups = array();

        foreach (\str_getcsv(str_replace(' ', ',', isset($this->parameters['group']) ? $this->parameters['group'] : ''), ',', '\'', "\\") as $group) {
            $groups[$group]  = $group;
        }

        $result = array_intersect($groups, $this->value);

        return empty($result);
    }
}
