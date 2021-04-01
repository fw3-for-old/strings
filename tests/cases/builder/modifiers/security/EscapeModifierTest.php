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

namespace fw3_for_old\tests\strings\builder\modifys\security;

use fw3_for_old\ez_test\test_unit\AbstractTest;
use fw3_for_old\strings\builder\modifiers\security\EscapeModifier;
use fw3_for_old\strings\converter\Convert;

class EscapeModifierTest extends AbstractTest
{

    public function testModify()
    {
        //----------------------------------------------
        $expected   = "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface";
        $actual     = "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier";

        $this->assertIsSubclassOf($expected, $actual);

        //----------------------------------------------
        $expected   = '&lt;a href=&quot;#id&quot; onclick=&quot;alert(&#039;alert&#039;);&quot;&gt;';
        $actual     = EscapeModifier::modify('<a href="#id" onclick="alert(\'alert\');">');

        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $options    = array(
            'type'  => Convert::ESCAPE_TYPE_JS,
        );

        $expected   = '\x3ca\x20href\x3d\x22\x23id\x22\x20onclick\x3d\x22alert\x28\x27alert\x27\x29\x3b\x22\x3e';
        $actual     = EscapeModifier::modify('<a href="#id" onclick="alert(\'alert\');">', $options);

        $this->assertEquals($expected, $actual);
    }

    public function test__invoke()
    {
        $modifier   = new EscapeModifier();

        //----------------------------------------------
        $expected   = "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface";
        $actual     = $modifier;

        $this->assertIsSubclassOf($expected, $actual);

        //----------------------------------------------
        $expected   = '&lt;a href=&quot;#id&quot; onclick=&quot;alert(&#039;alert&#039;);&quot;&gt;';
        $actual     = $modifier('<a href="#id" onclick="alert(\'alert\');">');

        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $options    = array(
            'type'  => Convert::ESCAPE_TYPE_JS,
        );

        $expected   = '\x3ca\x20href\x3d\x22\x23id\x22\x20onclick\x3d\x22alert\x28\x27alert\x27\x29\x3b\x22\x3e';
        $actual     = $modifier('<a href="#id" onclick="alert(\'alert\');">', $options);

        $this->assertEquals($expected, $actual);
    }

}
