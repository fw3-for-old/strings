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

namespace fw3_for_old\tests\strings\converter;

use fw3_for_old\ez_test\test_unit\AbstractTest;
use fw3_for_old\strings\converter\Convert;
use stdClass;

class ConvertTest extends AbstractTest
{
    protected $internalEncoding = null;

    public function setUp()
    {
        parent::setUp();

        $this->internalEncoding = mb_internal_encoding();
        mb_internal_encoding('UTF-8');
    }

    public function tearDown()
    {
        mb_internal_encoding($this->internalEncoding);

        parent::tearDown();
    }

    public function testToJson()
    {
        //----------------------------------------------
        $value = array();

        $expected = '[]';
        $actual = Convert::toJson($value);

        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value = new stdClass;

        $expected = '{}';
        $actual = Convert::toJson($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value = array(1, 'a', 'あ', true, null, (object) array(), array(1, 'a', 'あ', true, null, (object) array()));

        $expected = '[1,"a","\u3042",true,null,{},[1,"a","\u3042",true,null,{}]]';
        $actual = Convert::toJson($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value = (object) array(1, 'a', 'あ', true, null, (object) array(), array(1, 'a', 'あ', true, null, (object) array()));

        $expected = '{"0":1,"1":"a","2":"\u3042","3":true,"4":null,"5":{},"6":[1,"a","\u3042",true,null,{}]}';
        $actual = Convert::toJson($value);
        $this->assertEquals($expected, $actual);
    }

    public function testHtmlEscape()
    {
        //----------------------------------------------
        $value  = 'asdf';

        $expected   = $value;
        $actual     = Convert::htmlEscape($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = '<a href="#id">';

        $expected   = '&lt;a href=&quot;#id&quot;&gt;';
        $actual     = Convert::htmlEscape($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = '<a href="#id" onclick="alert(\'alert\');">';

        $expected   = '&lt;a href=&quot;#id&quot; onclick=&quot;alert(&#039;alert&#039;);&quot;&gt;';
        $actual     = Convert::htmlEscape($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = 'alert(\'alert\');alert("alert2")';

        $expected   = 'alert\x28\x27alert\x27\x29\x3balert\x28\x22alert2\x22\x29';
        $actual     = Convert::jsEscape($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = '<a href="#id" target=\'_blank\'>';
        $options    = array();

        $expected   = '&lt;a href=&quot;#id&quot; target=&#039;_blank&#039;&gt;';
        $actual     = Convert::htmlEscape($value);
        $this->assertEquals($expected, $actual);

        $expected   = '&lt;a href=&quot;#id&quot; target=&#039;_blank&#039;&gt;';
        $actual     = Convert::htmlEscape($value, $options);
        $this->assertEquals($expected, $actual);
    }

    public function testHtmlEscapeIllegalSequence001()
    {
        //----------------------------------------------
        $value = '<a href="#id" tar' . "\xff" . 'get=\'_blank\'>';

        try {
            Convert::htmlEscape($value);
        } catch (\Exception $e) {
            $this->expectException($e, "\\InvalidArgumentException");
            $this->expectExceptionMessage($e, '不正なエンコーディングが検出されました。encoding:\'UTF-8\', value_encoding:false');
        }
    }

    public function testHtmlEscapeIllegalSequence002()
    {
        //----------------------------------------------
        $value = 'あああああああああ';

        try {
            Convert::htmlEscape($value, array(), 'SJIS-win');
        } catch (\Exception $e) {
            $this->expectException($e, "\\InvalidArgumentException");
            $this->expectExceptionMessage($e, '不正なエンコーディングが検出されました。encoding:\'SJIS-win\', value_encoding:\'UTF-8\'');
        }
    }

    public function testHtmlEscapeIllegalSequence003()
    {
        //----------------------------------------------
        $value = 'あああああああああ';

        try {
            Convert::htmlEscape($value, array(), 'SJIS-win');
        } catch (\Exception $e) {
            $this->expectException($e, "\\InvalidArgumentException");
            $this->expectExceptionMessage($e, '不正なエンコーディングが検出されました。encoding:\'SJIS-win\', value_encoding:\'UTF-8\'');
        }
    }


    public function testHtmlEscapeIllegalSequence004()
    {
        //----------------------------------------------
        $value  = mb_convert_encoding('あああああああああ', 'SJIS-win');

        try {
            Convert::htmlEscape($value);
        } catch (\Exception $e) {
            $this->expectException($e, "\\InvalidArgumentException");
            $this->expectExceptionMessage($e, '不正なエンコーディングが検出されました。encoding:\'UTF-8\', value_encoding:\'SJIS-win\'');
        }
    }

    public function testJsEscape()
    {
        //----------------------------------------------
        $value  = 'asdf';

        $expected   = $value;
        $actual     = Convert::jsEscape($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = '<a href="#id">';

        $expected   = '\x3ca\x20href\x3d\x22\x23id\x22\x3e';
        $actual     = Convert::jsEscape($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = '<a href="#id" onclick="alert(\'alert\');">';

        $expected   = '\x3ca\x20href\x3d\x22\x23id\x22\x20onclick\x3d\x22alert\x28\x27alert\x27\x29\x3b\x22\x3e';
        $actual     = Convert::jsEscape($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = 'alert(\'alert\');alert("alert2")';

        $expected   = 'alert\x28\x27alert\x27\x29\x3balert\x28\x22alert2\x22\x29';
        $actual     = Convert::jsEscape($value);
        $this->assertEquals($expected, $actual);
    }

    public function testEscape()
    {
        //----------------------------------------------
        $value  = '<a href="#id">';

        $expected   = '&lt;a href=&quot;#id&quot;&gt;';
        $actual     = Convert::escape($value, Convert::ESCAPE_TYPE_HTML);
        $this->assertEquals($expected, $actual);

        $expected   = '\x3ca\x20href\x3d\x22\x23id\x22\x3e';
        $actual     = Convert::escape($value, Convert::ESCAPE_TYPE_JS);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = 'alert(\'alert\');alert("alert2")';

        $expected   = 'alert(&#039;alert&#039;);alert(&quot;alert2&quot;)';
        $actual     = Convert::escape($value, Convert::ESCAPE_TYPE_HTML);
        $this->assertEquals($expected, $actual);

        $expected   = 'alert\x28\x27alert\x27\x29\x3balert\x28\x22alert2\x22\x29';
        $actual     = Convert::escape($value, Convert::ESCAPE_TYPE_JS);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = '<a href="#id" target=\'_blank\'>';

        $options    = array();

        $expected   = '&lt;a href=&quot;#id&quot; target=&#039;_blank&#039;&gt;';
        $actual     = Convert::escape($value, Convert::ESCAPE_TYPE_HTML, $options);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = mb_convert_encoding('ああああああ', 'SJIS-win');

        $expected   = $value;
        try {
            Convert::escape($value, Convert::ESCAPE_TYPE_HTML, array(), 'SJIS-win');
        } catch (\Exception $e) {
            $this->expectException($e, "\\InvalidArgumentException");
            $this->expectExceptionMessage($e, 'PHP5.4.0未満ではhtmlspecialcharsに次のエンコーディングは使用できません。encoding:SJIS-win');
        }

        //----------------------------------------------
        // PHP 5.3専用検証
        foreach (array(
// mb_convert_encodingで利用できないエンコーディング
//            'ISO8859-1',
//            'ISO-8859-5',
//            'ISO8859-5',
//            'ISO-8859-15',
//            'ISO8859-15',
//            'ibm866',
//            '866',
//            'win-1251',
//            '1252',
//            '1251',
//            'koi8-ru',
//            '950',
//            '936',
//            'BIG5-HKSCS',
//            '932',
//            'MacRoman'
// PHP5.3.0でも利用できるエンコーディング
            'UTF-8',
            'cp866',
            'cp1251',
            'Windows-1251',
            'cp1252',
            'Windows-1252',
            'KOI8-R',
            'koi8r',
            'BIG5',
            'GB2312',
            'Shift_JIS',
            'SJIS',
            'EUC-JP',
            'EUCJP',
// htmlspecialcharsで利用できないエンコーディング
            'SJIS-win',
            'cp932',
            'eucJP-win',
        ) as $encoding) {
            $value  = mb_convert_encoding('ああああああ', $encoding);

            $expected   = $value;
            try {
                Convert::escape($value, Convert::ESCAPE_TYPE_HTML, array(), $encoding);
            } catch (\Exception $e) {
                $this->expectException($e, "\\InvalidArgumentException");
                $this->expectExceptionMessage($e, sprintf('PHP5.4.0未満ではhtmlspecialcharsに次のエンコーディングは使用できません。encoding:%s', $encoding));
            }
        }
    }

    public function testToTextNotation()
    {
        //----------------------------------------------
        $value  = true;

        $expected   = 'true';
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = 1;

        $expected   = 1;
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = (float) 1;

        $expected   = 1.0;
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = 0.1;

        $expected   = 0.1;
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = 'string';

        $expected   = '\'string\'';
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = array(1, array(2, array(3)), 'a' => 'a', 'b' => array('b' => 'b', 'c' => array('c' => 'c')));

        $expected   = 'Array';
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        $expected   = '[0 => 1, 1 => Array, \'a\' => \'a\', \'b\' => Array]';
        $actual     = Convert::toDebugString($value, 1);
        $this->assertEquals($expected, $actual);

        $expected   = '[0 => 1, 1 => [0 => 2, 1 => Array], \'a\' => \'a\', \'b\' => [\'b\' => \'b\', \'c\' => Array]]';
        $actual     = Convert::toDebugString($value, 2);
        $this->assertEquals($expected, $actual);

        $expected   = '[0 => 1, 1 => [0 => 2, 1 => [0 => 3]], \'a\' => \'a\', \'b\' => [\'b\' => \'b\', \'c\' => [\'c\' => \'c\']]]';
        $actual     = Convert::toDebugString($value, 3);
        $this->assertEquals($expected, $actual);

        $actual     = Convert::toDebugString($value, 4);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = new MockForConvertTest();
        ob_start();
        var_dump($value);
        $object_status = ob_get_clean();

        $object_status = substr($object_status, 0, strpos($object_status, ' ('));
        $object_status = sprintf('object(%s)', substr($object_status, 6));

        $expected   = $object_status;
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        $expected   = sprintf('%s {static public \'public_const\' = Array, static public \'publicStatic\' = Array, static protected \'PROTECTD_CONST\' = Array, static protected \'protectdStatic\' = Array, static private \'PRIVATE_CONST\' = Array, static private \'privateStatic\' = Array, public \'public\' = Array, protected \'protectd\' = Array, private \'private\' = Array}', $object_status);
        $actual     = Convert::toDebugString($value, 1);
        $this->assertEquals($expected, $actual);

        $expected   = sprintf('%s {static public \'public_const\' = [0 => Array], static public \'publicStatic\' = [0 => Array], static protected \'PROTECTD_CONST\' = [0 => Array], static protected \'protectdStatic\' = [0 => Array], static private \'PRIVATE_CONST\' = [0 => Array], static private \'privateStatic\' = [0 => Array], public \'public\' = [0 => Array], protected \'protectd\' = [0 => Array], private \'private\' = [0 => Array]}', $object_status);
        $actual     = Convert::toDebugString($value, 2);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = fopen('php://memory', 'wb');

        $expected   = sprintf('stream %s', $value);
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        fclose($value);
        $expected   = version_compare(PHP_VERSION, '5.4.0', '<') ? 'unknown type' : sprintf('resource (closed) %s', $value);
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value  = null;

        $expected   = 'NULL';
        $actual     = Convert::toDebugString($value);
        $this->assertEquals($expected, $actual);

    }

    public function testToSnakeCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'Test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'Test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'Test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'Test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'Test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'Test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'Test_To_Case_Convert';
        $actual     = Convert::toSnakeCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToUpperSnakeCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'TEST_TO_CASE_CONVERT';
        $actual     = Convert::toUpperSnakeCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToLowerSnakeCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'test_to_case_convert';
        $actual     = Convert::toLowerSnakeCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToChainCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'Test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'Test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'Test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'Test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'Test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'Test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'Test-To-Case-Convert';
        $actual     = Convert::toChainCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToUpperChainCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'TEST-TO-CASE-CONVERT';
        $actual     = Convert::toUpperChainCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToLowerChainCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'test-to-case-convert';
        $actual     = Convert::toLowerChainCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToCamelCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toCamelCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToUpperCamelCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'TestToCaseConvert';
        $actual     = Convert::toUpperCamelCase($value);
        $this->assertEquals($expected, $actual);
    }

    public function testToLowerCamelCase()
    {
        //----------------------------------------------
        $value      = 'Test_To_Case_Convert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To-Case-Convert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To Case Convert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test_To-Case Convert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test-To Case_Convert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'Test To_Case-Convert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'testToCaseConvert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);

        //----------------------------------------------
        $value      = 'TestToCaseConvert';
        $expected   = 'testToCaseConvert';
        $actual     = Convert::toLowerCamelCase($value);
        $this->assertEquals($expected, $actual);
    }
}

class MockForConvertTest
{
    public      static $public_const    = array(array('public const'));
    protected   static $PROTECTD_CONST  = array(array('protected const'));
    private     static $PRIVATE_CONST   = array(array('private const'));

    public      static  $publicStatic   = array(array('public static'));
    protected   static  $protectdStatic = array(array('protected static'));
    private     static  $privateStatic  = array(array('private static'));

    public      $public     = array(array('public'));
    protected   $protectd   = array(array('protected'));
    private     $private    = array(array('private'));
}
