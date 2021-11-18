<?php

namespace fw3_for_old\tests\strings\cases\builder;

use fw3_for_old\ez_test\test_unit\AbstractTest;
use fw3_for_old\strings\builder\StringBuilder;
use fw3_for_old\strings\builder\modifiers\AbstractModifier;
use fw3_for_old\strings\builder\modifiers\security\EscapeModifier;
use fw3_for_old\strings\builder\traits\converter\AbstractConverter;
use fw3_for_old\strings\converter\Convert;

/**
 * @processFork
 */
class StringBuilderTest extends AbstractTest
{
    protected $internalEncoding = null;

    public function initialize()
    {
        $this->internalEncoding = mb_internal_encoding();
        mb_internal_encoding('UTF-8');
    }

    public function finalize()
    {
        mb_internal_encoding($this->internalEncoding);
    }

    public function testBuild()
    {
        $stringBuilder    = StringBuilder::factory();

        //----------------------------------------------
        $expected   = '';
        $actual     = $stringBuilder->buildMessage('');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|e}';
        $values     = array('"');

        $expected   = '&quot;';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|e}';
        $values     = (object) array('"');

        $expected   = '&quot;';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|e}{:ickx}{:0|e}';
        $values     = (object) array('"');
        $converter  = "\\fw3_for_old\\tests\\strings\\cases\\builder\\UrlConvertr";

        $expected   = '&quot;https://ickx.jp&quot;';
        $actual     = $stringBuilder->buildMessage($message, $values, $converter);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|e}{:ickx}{:0|e}';
        $values     = (object) array('"');
        $converter  = new UrlConvertr();

        $expected   = '&quot;https://ickx.jp&quot;';
        $actual     = $stringBuilder->buildMessage($message, $values, $converter);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|e}{:ickx}{:0|e}';
        $values     = (object) array('"');
        $converter  = function ($name, $search, $values) {
            return $name === '0' ? '0' : $name;
        };

        $expected   = '&quot;ickx&quot;';
        $actual     = $stringBuilder->buildMessage($message, $values, $converter);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = 'message_{:pattern_a}';
        $actual     = StringBuilder::disposableFactory()->buildMessage('message_{:pattern_a}', [
            'pattern_a' => '{:pattern_a}'
        ]);
        $this->assertSame($expected, $actual);
    }

    public function testBuildMessage()
    {
        $stringBuilder    = StringBuilder::factory();

        //----------------------------------------------
        $stringBuilder->values(array(
            'part1' => 'part_1',
            'part2' => 'part_2',
            'expected'  => '{:part1}_{:part2}',
        ));

        $expected   = 'part_1_part_2';
        $actual     = $stringBuilder->buildMessage('{:expected}');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $stringBuilder->values(array(
            'part1' => '<part_1',
            'part2' => 'part_2>',
            'expected'  => '{:part1}_{:part2}',
        ));

        $expected   = '&lt;part_1_part_2&gt;';
        $actual     = $stringBuilder->buildMessage('{:expected|escape}');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $stringBuilder->values(array(
            'part1' => '<part_1',
            'part2' => 'part_2>',
            'expected'  => '{:part1|escape}_{:part2|escape}',
        ));

        $expected   = '&lt;part_1_part_2&gt;';
        $actual     = $stringBuilder->buildMessage('{:expected}');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $stringBuilder->values(array(
            'part1' => '\'part1',
            'part2' => 'part2\'',
            'expected'  => '<{:part1|escape(\'javascript\')}_{:part2|escape(\'javascript\')}>',
        ));

        $expected   = '&lt;\x27part1_part2\x27&gt;';
        $actual     = $stringBuilder->buildMessage('{:expected|escape}');
        $this->assertSame($expected, $actual);
    }

    public function testDisposableFactory()
    {
        //----------------------------------------------
        $expected   = StringBuilder::factory('same');
        $actual     = StringBuilder::factory('same');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = StringBuilder::disposableFactory();
        $actual     = StringBuilder::disposableFactory();
        $this->assertNotSame($expected, $actual);
    }

    public function testCharacterEncoding()
    {
        //==============================================
        $character_encoding = StringBuilder::factory()->characterEncoding();

        $expected   = 'UTF-8';
        $actual     = $character_encoding;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::get()->characterEncoding('SJIS');

        $expected   = 'SJIS';
        $actual     = StringBuilder::get()->characterEncoding();
        $this->assertSame($expected, $actual);
    }

    public function testConverter()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="{:ickx}">{:ick x}</a><br><a href="{:effy}">{:effy}</a>',
        );

        $converter_set   = $this->getUrlConverterSet();

        //----------------------------------------------
        $set_name       = 'closure';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href="https://ickx.jp">{:ick x}</a><br><a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'instance';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $stringBuilder->substitute('');

        $this->assertSame('', $stringBuilder->substitute());

        $message    = '{:html}';
        $expected   = '<a href="https://ickx.jp">{:ick x}</a><br><a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'classpath';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href="https://ickx.jp">{:ick x}</a><br><a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //==============================================
        $values     = array(
            'html'  => '{:ick x}:<a href="{:ickx}">{:ick x}</a><br>{:effy}:<a href="{:effy}">{:effy}</a>',
        );

        $converter_set   = $this->getUrlConverterSet();
        var_dump($stringBuilder->substitute());
        //----------------------------------------------
        $set_name       = 'closure';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '{:ick x}:<a href="https://ickx.jp">{:ick x}</a><br>https://effy.info:<a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'instance';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '{:ick x}:<a href="https://ickx.jp">{:ick x}</a><br>https://effy.info:<a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'classpath';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '{:ick x}:<a href="https://ickx.jp">{:ick x}</a><br>https://effy.info:<a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //==============================================
        StringBuilder::remove(StringBuilder::DEFAULT_NAME);

        $values     = array(
            'html'  => '<a href="{:ickx}">{:ickx}</a><br><a href="{:effy}">{:effy}</a>',
        );

        $converter_set   = $this->getDoNothingConverterSet();

        //----------------------------------------------
        $set_name       = 'closure';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href=""></a><br><a href=""></a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'instance';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href=""></a><br><a href=""></a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'classpath';

        $stringBuilder    = StringBuilder::factory()->converter($converter_set[$set_name]);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href=""></a><br><a href=""></a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::factory()->converter();
        $this->assertSame($expected, $actual);
    }

    public function testDefaultCharacterEncoding()
    {
        //==============================================
        $character_encoding = StringBuilder::defaultCharacterEncoding();

        $expected   = null;
        $actual     = $character_encoding;
        $this->assertSame($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $expected   = 'UTF-8';
        $actual     = StringBuilder::get()->characterEncoding();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::defaultCharacterEncoding('SJIS-win');

        $expected   = 'SJIS-win';
        $actual     = StringBuilder::defaultCharacterEncoding();
        $this->assertSame($expected, $actual);

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class::factory();

        //----------------------------------------------
        $expected   = 'SJIS-win';
        $actual     = StringBuilder::get()->characterEncoding();
        $this->assertSame($expected, $actual);
    }

    /**
     * @preserveGlobalState disable
     */
    public function testDefaultConverter()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="{:ickx}">{:ickx}</a><br><a href="{:effy}">{:effy}</a>',
        );

        $converter_set   = $this->getUrlConverterSet();

        //----------------------------------------------
        $set_name       = 'closure';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultConverter($converter_set[$set_name]);
        $stringBuilder          = $string_builder_class::factory();
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href="https://ickx.jp">https://ickx.jp</a><br><a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::defaultConverter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'instance';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultConverter($converter_set[$set_name]);
        $stringBuilder          = $string_builder_class::factory();
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href="https://ickx.jp">https://ickx.jp</a><br><a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::defaultConverter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'classpath';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultConverter($converter_set[$set_name]);
        $stringBuilder          = $string_builder_class::factory();
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href="https://ickx.jp">https://ickx.jp</a><br><a href="https://effy.info">https://effy.info</a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::defaultConverter();
        $this->assertSame($expected, $actual);

        //==============================================
        $values     = array(
            'html'  => '<a href="{:ickx}">{:ickx}</a><br><a href="{:effy}">{:effy}</a>',
        );

        $converter_set   = $this->getDoNothingConverterSet();

        //----------------------------------------------
        $set_name       = 'closure';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultConverter($converter_set[$set_name]);
        $stringBuilder          = $string_builder_class::factory();
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href=""></a><br><a href=""></a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::defaultConverter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'instance';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultConverter($converter_set[$set_name]);
        $stringBuilder          = $string_builder_class::factory();
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href=""></a><br><a href=""></a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::defaultConverter();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $set_name       = 'classpath';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultConverter($converter_set[$set_name]);
        $stringBuilder          = $string_builder_class::factory();
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html}';
        $expected   = '<a href=""></a><br><a href=""></a>';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $expected   = $converter_set[$set_name];
        $actual     = StringBuilder::defaultConverter();
        $this->assertSame($expected, $actual);
    }

    public function testDefaultEnclosure()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultEnclosure('${', '}}');
        $this->assertSame($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $expected   = array('begin' => '${', 'end' => '}}');
        $actual     = StringBuilder::defaultEnclosure();
        $this->assertSame($expected, $actual);

        $message    = 'asdf ${html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultEnclosure(array('begin' => '${', 'end' => '}}', '{:', '}'));
        $string_builder_class::factory();

        $message    = 'asdf {:html} zxcv';

        $expected   = 'asdf {:html} zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = 'asdf ${html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::remove(StringBuilder::DEFAULT_NAME);

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultEnclosure(array('${', '}}'));
        $string_builder_class::factory();

        $message    = 'asdf ${html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function defaultEnclosureBeginExceptionDataProvider()
    {
        return array(
            array(':', 'クラスデフォルトの変数部開始文字列にクラスデフォルトの変数名セパレータと同じ値を設定しようとしています。value::'),
            array('|', 'クラスデフォルトの変数部開始文字列にクラスデフォルトの修飾子セパレータと同じ値を設定しようとしています。value:|'),
            array('■', 'クラスデフォルトの変数部開始文字列にクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('}', 'クラスデフォルトの変数部開始文字列にクラスデフォルトの変数部終了文字列と同じ値を設定しようとしています。value:}'),
        );
    }

    public function defaultEnclosureEndExceptionDataProvider()
    {
        return array(
            array(':', 'クラスデフォルトの変数部終了文字列にクラスデフォルトの変数名セパレータと同じ値を設定しようとしています。value::'),
            array('|', 'クラスデフォルトの変数部終了文字列にクラスデフォルトの修飾子セパレータと同じ値を設定しようとしています。value:|'),
            array('■', 'クラスデフォルトの変数部終了文字列にクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('{:', 'クラスデフォルトの変数部終了文字列にクラスデフォルトの変数部開始文字列と同じ値を設定しようとしています。value:{:'),
        );
    }

    public function testDefaultEnclosureBeginException()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->defaultEnclosureBeginExceptionDataProvider() as $data) {
            list($value, $message) = $data;

            try {
                StringBuilder::defaultEnclosure(array($value, '}'));
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException("\\InvalidArgumentException", $e);
            }
        }
    }

    public function testDefaultEnclosureEndExceptionDataProvider()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->defaultEnclosureEndExceptionDataProvider() as $data) {
            list($value, $message) = $data;

            try {
                StringBuilder::defaultEnclosure(array('{:', $value));
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException("\\InvalidArgumentException", $e);
            }
        }
    }

    public function testDefaultEnclosureBegin()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $enclosure_begin    = StringBuilder::defaultEnclosureBegin();

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultEnclosureBegin('${');
        $this->assertSame($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $expected   = '${';
        $actual     = StringBuilder::defaultEnclosureBegin();
        $this->assertSame($expected, $actual);

        $message    = 'asdf ${html} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultEnclosureBegin($enclosure_begin);
        $string_builder_class::factory();

        $message    = 'asdf {:html} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testSetDefaultValue()
    {
        //==============================================
        $expected   = array();
        $actual     = StringBuilder::defaultValues();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";

        $string_builder_class   = StringBuilder::setDefaultValue('html', '<a href="#id">');
        $actual                 = $string_builder_class::factory();

        $this->assertInstanceOf($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        StringBuilder::remove(StringBuilder::DEFAULT_NAME);

        //----------------------------------------------
        $string_builder_class   = StringBuilder::setDefaultValue('html', '<a href=\'#id\'>');
        $actual                 = $string_builder_class::factory();

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href=\'#id\'> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    public function testDefaultEnclosureEnd()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $enclosure_End    = StringBuilder::defaultEnclosureEnd();

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultEnclosureEnd('}}');
        $this->assertSame($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $expected   = '}}';
        $actual     = StringBuilder::defaultEnclosureEnd();
        $this->assertSame($expected, $actual);

        $message    = 'asdf {:html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultEnclosureEnd($enclosure_End);
        $string_builder_class::factory();

        $message    = 'asdf {:html} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testDefaultNameSeparator()
    {
        //==============================================
        $values     = array(
            '0'         => '00000',
            'begin'     => 'begin',
            'begin:0'   => 'aaaa',
        );

        $modifier_list  = array(
            'zero_to_dq'    => "\\fw3_for_old\\tests\\strings\\cases\\builder\\ZeroToDqModifier",
            'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        );

        StringBuilder::defaultModifierSet($modifier_list);

        //==============================================
        $expected   = ':';
        $actual     = StringBuilder::defaultNameSeparator();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory();

        $message    = '{:begin:0}';

        $expected   = 'begin';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0:begin}';

        $expected   = '00000';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:begin:0|zero_to_dq|escape}';

        $expected   = 'begin';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0:begin|zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultNameSeparator('<>');
        $this->assertSame($expected, $actual);

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class::factory();

        $message    = '{:begin:0}';

        $expected   = 'aaaa';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:begin<>0}';

        $expected   = 'begin';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function defaultNameSeparatorExceptionDataProvider()
    {
        return array(
            array('|', "\\InvalidArgumentException", 'クラスデフォルトの変数名セパレータにクラスデフォルトの修飾子セパレータと同じ値を設定しようとしています。value:|'),
            array('■', "\\InvalidArgumentException", 'クラスデフォルトの変数名セパレータにクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('{:', "\\InvalidArgumentException", 'クラスデフォルトの変数名セパレータにクラスデフォルトの変数部開始文字列と同じ値を設定しようとしています。value:{:'),
            array('}', "\\InvalidArgumentException", 'クラスデフォルトの変数名セパレータにクラスデフォルトの変数部終了文字列と同じ値を設定しようとしています。value:}'),
        );
    }

    public function testDefaultNameSeparatorException()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->defaultNameSeparatorExceptionDataProvider() as $data) {
            list($value, $exception_class, $message) = $data;

            try {
                StringBuilder::defaultNameSeparator($value);
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException($exception_class, $e);
            }
        }
    }

    public function testDefaultModifierSeparator()
    {
        //==============================================
        $values     = array(
            '0'         => '00000',
        );

        $modifier_list  = array(
            'zero_to_dq'    => "\\fw3_for_old\\tests\\strings\\cases\\builder\\ZeroToDqModifier",
            'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        );

        StringBuilder::defaultModifierSet($modifier_list);

        //==============================================
        $expected   = '|';
        $actual     = StringBuilder::defaultModifierSeparator();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory();

        $message    = '{:0|zero_to_dq}';

        $expected   = '"""""';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0|zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultModifierSeparator('<>');
        $this->assertSame($expected, $actual);

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class::factory();

        $message    = '{:0<>zero_to_dq}';

        $expected   = '"""""';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0<>zero_to_dq<>escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0<>zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function defaultModifierSeparatorExceptionDataProvider()
    {
        return array(
            array(':', "\\InvalidArgumentException", 'クラスデフォルトの修飾子セパレータにクラスデフォルトの変数名セパレータと同じ値を設定しようとしています。value::'),
            array('■', "\\InvalidArgumentException", 'クラスデフォルトの修飾子セパレータにクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('{:', "\\InvalidArgumentException", 'クラスデフォルトの修飾子セパレータにクラスデフォルトの変数部開始文字列と同じ値を設定しようとしています。value:{:'),
            array('}', "\\InvalidArgumentException", 'クラスデフォルトの修飾子セパレータにクラスデフォルトの変数部終了文字列と同じ値を設定しようとしています。value:}'),
        );
    }

    public function testDefaultModifierSeparatorException()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->defaultModifierSeparatorExceptionDataProvider() as $data) {
            list($value, $exception_class, $message)   = $data;

            try {
                StringBuilder::defaultModifierSeparator($value);
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException($exception_class, $e);
            }
        }

    }

    public function testDefaultModifierSet()
    {
        //==============================================
        $values     = array(
            '0'         => '00000',
        );

        $modifier_list  = array(
            'zero_to_dq'    => "\\fw3_for_old\\tests\\strings\\cases\\builder\\ZeroToDqModifier",
            'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        );

        //==============================================
        $expected   = array(
            'date'      => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\DateModifier",
            'strtotime' => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\StrtotimeModifier",
            'escape'    => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
            'e'         => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
            'to_debug'          => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
            'to_debug_str'      => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
            'to_debug_string'   => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        );
        $actual     = StringBuilder::defaultModifierSet();
        $this->assertSame($expected, $actual);

        //==============================================
        StringBuilder::factory();

        $message    = '{:0|zero_to_dq}';

        $expected   = '00000';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|zero_to_dq|escape}';

        $expected   = '00000';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultModifierSet($modifier_list);
        $this->assertSame($expected, $actual);

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class::factory();

        //----------------------------------------------
        $message    = '{:0|zero_to_dq}';

        $expected   = '"""""';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testDefaultSubstitute()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
            'alt'   => '{:html}',
        );

        $message    = '{:html2}/{:alt2}';

        //==============================================
        $expected   = '';
        $actual     = StringBuilder::defaultSubstitute();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory();

        $expected   = '/';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = null;

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultSubstitute($substitute);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class::factory();

        $expected   = '{:html2}/{:alt2}';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = '';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultSubstitute($substitute);
        $string_builder_class::factory();

        $expected   = '/';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = '■';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultSubstitute($substitute);
        $string_builder_class::factory();

        $expected   = '■/■';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = '{:alt}';

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultSubstitute($substitute);
        $string_builder_class::factory();

        $expected   = '<a href="#id">/<a href="#id">';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testDefaultValues()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $expected   = array();
        $actual     = StringBuilder::defaultValues();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::defaultValues($values);
        $this->assertSame($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $values     = array(
            'html'  => '<a href=\'#id\'>',
        );

        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class   = $string_builder_class::defaultValues($values);
        $string_builder_class::factory();

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href=\'#id\'> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    public function testEnclosure()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::factory()->enclosure('${', '}}');
        $this->assertInstanceOf($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $expected   = array('begin' => '${', 'end' => '}}');
        $actual     = StringBuilder::factory()->enclosure();
        $this->assertSame($expected, $actual);

        $message    = 'asdf ${html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory()->enclosure(array('begin' => '${', 'end' => '}}', '{:', '}'));

        $message    = 'asdf {:html} zxcv';

        $expected   = 'asdf {:html} zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = 'asdf ${html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory()->enclosure(array('${', '}}'));

        $message    = 'asdf ${html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function enclosureBeginExceptionDataProvider()
    {
        return array(
            array(':', "\\InvalidArgumentException", '変数部開始文字列に変数名セパレータと同じ値を設定しようとしています。value::'),
            array('|', "\\InvalidArgumentException", '変数部開始文字列に修飾子セパレータと同じ値を設定しようとしています。value:|'),
            array('■', "\\InvalidArgumentException", '変数部開始文字列に変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('}', "\\InvalidArgumentException", '変数部開始文字列に変数部終了文字列と同じ値を設定しようとしています。value:}'),
        );
    }

    public function enclosureEndExceptionDataProvider()
    {
        return array(
            array(':', "\\InvalidArgumentException", '変数部終了文字列に変数名セパレータと同じ値を設定しようとしています。value::'),
            array('|', "\\InvalidArgumentException", '変数部終了文字列に修飾子セパレータと同じ値を設定しようとしています。value:|'),
            array('■', "\\InvalidArgumentException", '変数部終了文字列に変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('{:', "\\InvalidArgumentException", '変数部終了文字列に変数部開始文字列と同じ値を設定しようとしています。value:{:'),
        );
    }

    public function testEnclosureBeginException()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->enclosureBeginExceptionDataProvider() as $data) {
            list($value, $exception_class, $message) = $data;

            try {
                StringBuilder::factory()->enclosure(array($value, '}'));
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException($exception_class, $e);
            }
        }
    }

    public function testEnclosureEndExceptionDataProvider()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->enclosureEndExceptionDataProvider() as $data) {
            list($value, $exception_class, $message) = $data;

            try {
                StringBuilder::factory()->enclosure(array('{:', $value));
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException($exception_class, $e);
            }
        }
    }

    public function testEnclosureBegin()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $enclosure_begin    = StringBuilder::factory()->enclosureBegin();

        $stringBuilder        = StringBuilder::factory()->enclosureBegin('${');

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = $stringBuilder;
        $this->assertInstanceOf($expected, $actual);

        //----------------------------------------------
        $expected   = '${';
        $actual     = StringBuilder::factory()->enclosureBegin();
        $this->assertSame($expected, $actual);

        $message    = 'asdf ${html} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory()->enclosureBegin($enclosure_begin);

        $message    = 'asdf {:html} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testEnclosureEnd()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $enclosure_End    = StringBuilder::factory()->enclosureEnd();

        $stringBuilder        = StringBuilder::factory()->enclosureEnd('}}');

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = $stringBuilder;
        $this->assertInstanceOf($expected, $actual);

        //----------------------------------------------
        $expected   = '}}';
        $actual     = StringBuilder::factory()->enclosureEnd();
        $this->assertSame($expected, $actual);

        $message    = 'asdf {:html}} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory()->enclosureEnd($enclosure_End);

        $message    = 'asdf {:html} zxcv';

        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

    }

    public function testFactory()
    {
        //==============================================
        $message    = '{:text}';

        $values     = array(
            'text'  => StringBuilder::DEFAULT_NAME,
        );
        StringBuilder::factory(StringBuilder::DEFAULT_NAME, $values);

        $name   = 'a1';
        $values = array(
            'text'  => $name,
        );
        StringBuilder::factory($name, $values);

        $name   = 'b2';
        $values = array(
            'text'  => $name,
        );
        StringBuilder::factory($name, $values);

        //----------------------------------------------
        $expected   = StringBuilder::DEFAULT_NAME;
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = 'a1';
        $actual     = StringBuilder::get('a1')->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = 'b2';
        $actual     = StringBuilder::get('b2')->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    public function testGet()
    {
        //==============================================
        $message    = '{:text}';

        $values     = array(
            'text'  => StringBuilder::DEFAULT_NAME,
        );
        StringBuilder::factory(StringBuilder::DEFAULT_NAME, $values);

        $name   = 'a1';
        $values = array(
            'text'  => $name,
        );
        StringBuilder::factory($name, $values);

        $name   = 'b2';
        $values = array(
            'text'  => $name,
        );
        StringBuilder::factory($name, $values);

        //----------------------------------------------
        $expected   = StringBuilder::DEFAULT_NAME;
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = 'a1';
        $actual     = StringBuilder::get('a1')->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = 'b2';
        $actual     = StringBuilder::get('b2')->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    public function testGetName()
    {
        //----------------------------------------------
        $expected   = StringBuilder::DEFAULT_NAME;
        $actual     = StringBuilder::factory()->getName();
        $this->assertSame($expected, $actual);

        $actual     = StringBuilder::factory($expected)->getCacheName();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = 'testGetName';
        $actual     = StringBuilder::factory($expected)->getName();
        $this->assertSame($expected, $actual);

        $actual     = StringBuilder::factory($expected)->getCacheName();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = array('test', 'get', 'name');

        $builder    = StringBuilder::factory($name);

        $expected   = $name;
        $actual     = $builder->getName();
        $this->assertSame($expected, $actual);

        $expected   = implode('::', $name);
        $actual     = $builder->getCacheName();
        $this->assertSame($expected, $actual);
    }

    public function testNameSeparator()
    {
        //==============================================
        $values     = array(
            '0'         => '00000',
            'begin'     => 'begin',
            'begin:0'   => 'aaaa',
        );

        $modifier_list  = array(
            'zero_to_dq'    => "\\fw3_for_old\\tests\\strings\\cases\\builder\\ZeroToDqModifier",
            'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        );

        StringBuilder::defaultModifierSet($modifier_list);

        //==============================================
        $expected   = ':';
        $actual     = StringBuilder::factory()->nameSeparator();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:begin:0}';

        $expected   = 'begin';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0:begin}';

        $expected   = '00000';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:begin:0|zero_to_dq|escape}';

        $expected   = 'begin';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0:begin|zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::get()->nameSeparator('<>');
        $this->assertInstanceOf($expected, $actual);

        StringBuilder::factory();

        $message    = '{:begin:0}';

        $expected   = 'aaaa';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:begin<>0}';

        $expected   = 'begin';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function nameSeparatorExceptionDataProvider()
    {
        return array(
            array('|', "\\InvalidArgumentException", '変数名セパレータに修飾子セパレータと同じ値を設定しようとしています。value:|'),
            array('■', "\\InvalidArgumentException", '変数名セパレータに変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('{:', "\\InvalidArgumentException", '変数名セパレータに変数部開始文字列と同じ値を設定しようとしています。value:{:'),
            array('}', "\\InvalidArgumentException", '変数名セパレータに変数部終了文字列と同じ値を設定しようとしています。value:}'),
        );
    }

    public function testNameSeparatorException()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->nameSeparatorExceptionDataProvider() as $data) {
            list($value, $exception_class, $message) = $data;

            try {
                StringBuilder::factory()->nameSeparator($value);
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException($exception_class, $e);
            }
        }
    }

    public function testModifierSeparator()
    {
        //==============================================
        $values     = array(
            '0'         => '00000',
        );

        $modifier_list  = array(
            'zero_to_dq'    => "\\fw3_for_old\\tests\\strings\\cases\\builder\\ZeroToDqModifier",
            'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        );

        StringBuilder::defaultModifierSet($modifier_list);

        //==============================================
        $expected   = '|';
        $actual     = StringBuilder::factory()->modifierSeparator();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory();

        $message    = '{:0|zero_to_dq}';

        $expected   = '"""""';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0|zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::factory()->modifierSeparator('<>');
        $this->assertInstanceOf($expected, $actual);

        $message    = '{:0<>zero_to_dq}';

        $expected   = '"""""';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0<>zero_to_dq<>escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        $message    = '{:0<>zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function modifierSeparatorExceptionDataProvider()
    {
        return array(
            array(':', "\\InvalidArgumentException", '修飾子セパレータに変数名セパレータと同じ値を設定しようとしています。value::'),
            array('■', "\\InvalidArgumentException", '修飾子セパレータに変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:■'),
            array('{:', "\\InvalidArgumentException", '修飾子セパレータに変数部開始文字列と同じ値を設定しようとしています。value:{:'),
            array('}', "\\InvalidArgumentException", '修飾子セパレータに変数部終了文字列と同じ値を設定しようとしています。value:}'),
        );
    }

    public function testModifierSeparatorException()
    {
        StringBuilder::defaultSubstitute('■');

        foreach ($this->modifierSeparatorExceptionDataProvider() as $data) {
            list($value, $exception_class, $message) = $data;

            try {
                StringBuilder::factory()->modifierSeparator($value);
            } catch (\Exception $e) {
                $this->assertExceptionMessage($message, $e);
                $this->assertException($exception_class, $e);
            }
        }
    }

    public function testModifierSet()
    {
        //==============================================
        $values     = array(
            '0'         => '00000',
        );

        $modifier_list  = array(
            'zero_to_dq'    => "\\fw3_for_old\\tests\\strings\\cases\\builder\\ZeroToDqModifier",
            'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        );

        //==============================================
        $expected   = array(
            'date'      => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\DateModifier",
            'strtotime' => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\StrtotimeModifier",
            'escape'    => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
            'e'         => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
            'to_debug'          => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
            'to_debug_str'      => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
            'to_debug_string'   => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        );
        $actual     = StringBuilder::factory()->modifierSet();
        $this->assertSame($expected, $actual);

        //==============================================
        StringBuilder::factory();

        $message    = '{:0|zero_to_dq}';

        $expected   = '00000';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|zero_to_dq|escape}';

        $expected   = '00000';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $string_builder_class   = StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class::factory();

        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::factory()->modifierSet($modifier_list);
        $this->assertInstanceOf($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|zero_to_dq}';

        $expected   = '"""""';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:0|zero_to_dq|escape}';

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testModify()
    {
        //==============================================
        $modifier_list  = array(
            'zero_to_dq'        => "\\fw3_for_old\\tests\\strings\\cases\\builder\\ZeroToDqModifier",
            'escape'            => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
            'obj_zero_to_dq'    => new ZeroToDqModifier(),
        );

        StringBuilder::defaultModifierSet($modifier_list);

        //==============================================
        StringBuilder::factory();

        $message    = '00000';

        $modifiers  = array(
            'zero_to_dq'    => array(),
        );

        $expected   = '"""""';
        $actual     = StringBuilder::get()->modify($message, $modifiers);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '00000';

        $modifiers  = array(
            'zero_to_dq'    => array(),
            'escape'        => array(),
        );

        $expected   = '&quot;&quot;&quot;&quot;&quot;';
        $actual     = StringBuilder::get()->modify($message, $modifiers);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '00000';

        $modifiers  = array(
            'zero_to_dq'    => array(),
            'escape'        => array(Convert::ESCAPE_TYPE_JAVASCRIPT),
        );

        $expected   = '\x22\x22\x22\x22\x22';
        $actual     = StringBuilder::get()->modify($message, $modifiers);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '00000';

        $modifiers  = array(
            'obj_zero_to_dq'    => array(),
            'escape'            => array(Convert::ESCAPE_TYPE_JAVASCRIPT),
        );

        $expected   = '\x22\x22\x22\x22\x22';
        $actual     = StringBuilder::get()->modify($message, $modifiers);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '00000';

        $modifiers  = array(
        );

        $expected   = '00000';
        $actual     = StringBuilder::get()->modify($message, $modifiers);
        $this->assertSame($expected, $actual);
    }

    public function testRemove01()
    {
        $exception_class    = "\\OutOfBoundsException";
        $message            = 'StringBuilderキャッシュに無いキーを指定されました。name:\':default:\'';

        $this->assertExceptionMessage($message);
        $this->assertException($exception_class);

        StringBuilder::factory();
        StringBuilder::get()->buildMessage('');
        StringBuilder::remove(StringBuilder::DEFAULT_NAME);
        StringBuilder::get();
    }

    public function testRemove02()
    {
        $exception_class    = "\\OutOfBoundsException";
        $message            = 'StringBuilderキャッシュに無いキーを指定されました。name:\'a1\'';

        $this->assertExceptionMessage($message);
        $this->assertException($exception_class);

        StringBuilder::factory('a1');
        StringBuilder::get('a1')->buildMessage('');
        StringBuilder::remove('a1');
        StringBuilder::get('a1');
    }

    public function testRemoveDefaultModifier()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $escape_modifier_set    = $this->getEscapeModifierSet();

        $string_builder_class   = StringBuilder::defaultModifierSet($escape_modifier_set);
        $string_builder_class::factory();

        $string_builder_class   = StringBuilder::removeDefaultModifier('classpath');
        $string_builder_class::factory('removed');

        $message    = '{:html|classpath}';

        //----------------------------------------------
        $expected   = '&lt;a href=&quot;#id&quot;&gt;';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $this->assertNotEquals($escape_modifier_set, StringBuilder::get('removed')->modifierSet());
        $this->assertArrayNotHasKey('classpath', StringBuilder::get('removed')->modifierSet());

        $expected   = '<a href="#id">';
        $actual     = StringBuilder::get('removed')->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testRemoveDefaultValue()
    {
        //----------------------------------------------
        $string_builder_class   = StringBuilder::setDefaultValue('html', '<a href="#id">');
        $string_builder_class::factory();

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        StringBuilder::remove(StringBuilder::DEFAULT_NAME);

        //----------------------------------------------
        $string_builder_class   = StringBuilder::removeDefaultValue('html');
        $string_builder_class::remove(StringBuilder::DEFAULT_NAME);
        $string_builder_class::factory();

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf  zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    public function testRemoveModifier()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $escape_modifier_set    = $this->getEscapeModifierSet();

        $stringBuilder    = StringBuilder::factory()->modifierSet($escape_modifier_set);

        $message    = '{:html|classpath}';

        //----------------------------------------------
        $this->assertSame($escape_modifier_set, $stringBuilder->modifierSet());

        //----------------------------------------------
        $expected   = '&lt;a href=&quot;#id&quot;&gt;';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $stringBuilder->removeModifier('classpath');

        $this->assertNotEquals($escape_modifier_set, $stringBuilder->modifierSet());
        $this->assertArrayNotHasKey('classpath', $stringBuilder->modifierSet());

        $expected   = '<a href="#id">';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testRemoveValue()
    {
        //----------------------------------------------
        StringBuilder::factory()->setValue('html', '<a href="#id">');

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::get()->removeValue('html');

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf  zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    public function testSetDefaultModifier()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $modifier_set   = $this->getEscapeModifierSet();

        $expected   = '&lt;a href=&quot;#id&quot;&gt;';

        //----------------------------------------------
        $string_builder_class   = StringBuilder::setDefaultModifier('closure', $modifier_set['closure']);
        $string_builder_class   = StringBuilder::setDefaultModifier('instance', $modifier_set['instance']);
        $string_builder_class   = StringBuilder::setDefaultModifier('classpath', $modifier_set['classpath']);
        $stringBuilder          = $string_builder_class::factory();

        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        //----------------------------------------------
        $message    = '{:html|closure}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:html|instance}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $message    = '{:html|classpath}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $modifier_set   = $this->getDoNothingModifierSet();

        $expected   = '<a href="#id">';

        //----------------------------------------------
        $name       = 'closure';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = $stringBuilder->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|closure}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'instance';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = $stringBuilder->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|instance}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'classpath';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = $stringBuilder->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|classpath}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $modifier_set   = $this->getEscapeModifierSet();

        $expected   = '&lt;a href=&quot;#id&quot;&gt;';

        //----------------------------------------------
        $name       = 'closure';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = $stringBuilder->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|closure}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'instance';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = $stringBuilder->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|instance}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'classpath';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = $stringBuilder->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|classpath}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testSetModifier()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $modifier_set   = $this->getEscapeModifierSet();

        $expected   = '&lt;a href=&quot;#id&quot;&gt;';

        //----------------------------------------------
        $name       = 'closure';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = StringBuilder::factory()->setModifier($name, $modifier);

        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|closure}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'instance';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = StringBuilder::factory()->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|instance}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'classpath';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = StringBuilder::factory()->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|classpath}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $modifier_set   = $this->getDoNothingModifierSet();

        $expected   = '<a href="#id">';

        //----------------------------------------------
        $name       = 'closure';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = StringBuilder::factory()->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|closure}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'instance';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = StringBuilder::factory()->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|instance}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name       = 'classpath';
        $modifier   = $modifier_set[$name];

        $stringBuilder    = StringBuilder::factory()->setModifier($name, $modifier);
        $this->assertInstanceOf("\\fw3_for_old\\strings\\builder\\StringBuilder", $stringBuilder);

        $message    = '{:html|classpath}';
        $actual     = $stringBuilder->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testSetValue()
    {
        //==============================================
        $expected   = array();
        $actual     = StringBuilder::factory()->values();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::factory()->setValue('html', '<a href="#id">');
        $this->assertInstanceOf($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        StringBuilder::factory()->setValue('html', '<a href=\'#id\'>');

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href=\'#id\'> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    public function testSubstitute()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
            'alt'   => '{:html}',
        );

        $message    = '{:html2}/{:alt2}';

        //==============================================
        $expected   = '';
        $actual     = StringBuilder::factory()->substitute();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = '/';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = null;

        $expected   = StringBuilder::get();
        $actual     = StringBuilder::get()->substitute($substitute);
        $this->assertSame($expected, $actual);

        $expected   = '{:html2}/{:alt2}';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = '';

        StringBuilder::get()->substitute($substitute);

        $expected   = '/';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = '■';

        StringBuilder::get()->substitute($substitute);

        $expected   = '■/■';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);

        //==============================================
        $substitute = '{:alt}';

        StringBuilder::get()->substitute($substitute);

        $expected   = '<a href="#id">/<a href="#id">';
        $actual     = StringBuilder::get()->buildMessage($message, $values);
        $this->assertSame($expected, $actual);
    }

    public function testValues()
    {
        //==============================================
        $values     = array(
            'html'  => '<a href="#id">',
        );

        $expected   = array();
        $actual     = StringBuilder::factory()->values();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = "fw3_for_old\\strings\\builder\\StringBuilder";
        $actual     = StringBuilder::factory()->values($values);
        $this->assertInstanceOf($expected, $actual);

        StringBuilder::factory();

        //----------------------------------------------
        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href="#id"> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $values     = array(
            'html'  => '<a href=\'#id\'>',
        );

        StringBuilder::factory()->values($values);

        $message    = 'asdf {:html} zxcv';
        $expected   = 'asdf <a href=\'#id\'> zxcv';
        $actual     = StringBuilder::get()->buildMessage($message);
        $this->assertSame($expected, $actual);
    }

    protected function getDoNothingConverterSet()
    {
        return array(
            'closure'   => function ($name, $search, $values) {
                return null;
            },
            'instance'  => new DoNothingConvertr(),
            'classpath' => "\\fw3_for_old\\tests\\strings\\cases\\builder\\DoNothingConvertr",
        );
    }

    protected function getUrlConverterSet()
    {
        return array(
            'closure'   => function ($name, $search, $values) {
                return UrlConvertr::convert($name, $search, $values);
            },
            'instance'  => new UrlConvertr(),
            'classpath' => "\\fw3_for_old\\tests\\strings\\cases\\builder\\UrlConvertr",
        );
    }

    protected function getDoNothingModifierSet()
    {
        return array(
            'closure'   => function ($replace, array $parameters = array(), array $context = array()) {
                return $replace;
            },
            'instance'  => new DoNothingModifier(),
            'classpath' => "\\fw3_for_old\\tests\\strings\\cases\\builder\\DoNothingModifier",
        );
    }

    protected function getEscapeModifierSet()
    {
        return array(
            'closure'   => function ($replace, array $parameters = array(), array $context = array()) {
                return Convert::htmlEscape($replace);
            },
            'instance'  => new EscapeModifier(),
            'classpath' => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        );
    }
}

class DoNothingConvertr extends AbstractConverter
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

    /**
     * 現在の変数名を元に値を返します。
     *
     * @param   string      $name   現在の変数名
     * @param   string      $search 変数名の元の文字列
     * @param   array       $values 変数
     * @return  string|null 値
     */
    public static function convert($name, $search, array $values)
    {
        return null;
    }
}

class UrlConvertr extends AbstractConverter
{
    public static $URL_MAP  = array(
        'ickx'  => 'https://ickx.jp',
        'effy'  => 'https://effy.info',
    );

    /**
     * 現在の変数名を元に値を返します。
     *
     * @param   string      $name   現在の変数名
     * @param   string      $search 変数名の元の文字列
     * @param   array       $values 変数
     * @return  string|null 値
     */
    public static function convert($name, $search, array $values)
    {
        return isset(static::$URL_MAP[$name]) ? static::$URL_MAP[$name] : $search;
    }
}

class DoNothingModifier extends AbstractModifier
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
        return $replace;
    }
}

class NgClass
{
    /**
     * 現在の変数名を元に値を返します。
     *
     * @param   string      $name   現在の変数名
     * @param   string      $search 変数名の元の文字列
     * @param   array       $values 変数
     * @return  string|null 値
     */
    public static function convert($name, $search, $values)
    {
        return null;
    }

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
        return $replace;
    }
}


class ZeroToDqModifier extends AbstractModifier
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
        return str_replace(0, '"', $replace);
    }
}
