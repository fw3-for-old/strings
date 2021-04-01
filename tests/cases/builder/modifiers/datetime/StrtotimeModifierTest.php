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
 * @author      wakaba <wakabadou@gmail.com>
 * @copyright   2020 - Wakabadou (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license     http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion     1.0.0
 */

namespace fw3_for_old\tests\strings\builder\modifys\datetime;

use fw3_for_old\ez_test\test_unit\AbstractTest;
use fw3_for_old\strings\builder\modifiers\datetime\StrtotimeModifier;

class StrtotimeModifierTest extends AbstractTest
{

    public function testModify()
    {
        $base_ts    = strtotime('2020-01-01 00:00:00');

        //----------------------------------------------
        $expected   = "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface";
        $actual     = "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\StrtotimeModifier";

        $this->assertIsSubclassOf($expected, $actual);

        //----------------------------------------------
        $expected   = $base_ts;
        $actual     = StrtotimeModifier::modify('2020-01-01 00:00:00');

        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $opsions    = array(
            'baseTimestamp' => strtotime('2021-01-01 00:00:00'),
        );

        $expected   = strtotime(date('2021-01-02 00:00:00'));
        $actual     = StrtotimeModifier::modify('+ 1 day', $opsions);

        $this->assertEquals($expected, $actual);
    }

    public function test__invoke()
    {
        $base_ts    = strtotime('2020-01-01 00:00:00');
        $modifier   = new StrtotimeModifier();

        //----------------------------------------------
        $expected   = "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface";
        $actual     = $modifier;

        $this->assertIsSubclassOf($expected, $actual);

        //----------------------------------------------
        $expected   = $base_ts;
        $actual     = $modifier('2020-01-01 00:00:00');

        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $opsions    = array(
            'baseTimestamp' => strtotime('2021-01-01 00:00:00'),
        );

        $expected   = strtotime(date('2021-01-02 00:00:00'));
        $actual     = $modifier('+ 1 day', $opsions);

        $this->assertEquals($expected, $actual);
    }

}
