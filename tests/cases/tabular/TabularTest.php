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

namespace fw3_for_old\tests\strings\tabular;

use fw3_for_old\ez_test\test_unit\AbstractTest;
use fw3_for_old\strings\converter\Convert;
use fw3_for_old\strings\tabular\Tabular;

/**
 * @processFork
 */
class TabularTest extends AbstractTest
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

    public function testFactory()
    {
        //==============================================
        $tabular    = Tabular::factory();

        //----------------------------------------------
        $expected   = Tabular::factory();
        $actual     = $tabular;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_NAME;
        $actual     = $tabular->getName();
        $this->assertSame($expected, $actual);

        $actual     = $tabular->getCacheName();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_TAB_WIDTH;
        $actual     = $tabular->tabWidth();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_INDENTE_LEVEL;
        $actual     = $tabular->indenteLevel();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_CHARACTER_ENCODING;
        $actual     = $tabular->characterEncoding();
        $this->assertSame($expected, $actual);

        //==============================================
        $name           = 'test_name';
        $tab_width      = 2;
        $indent_level   = 1;
        $encoding       = 'SJIS-win';

        $tabular    = Tabular::factory($name, $tab_width, $indent_level, $encoding);

        //----------------------------------------------
        $expected   = Tabular::factory($name);
        $actual     = $tabular;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $name;
        $actual     = $tabular->getName();
        $this->assertSame($expected, $actual);

        $actual     = $tabular->getCacheName();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $tab_width;
        $actual     = $tabular->tabWidth();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $indent_level;
        $actual     = $tabular->indenteLevel();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $encoding;
        $actual     = $tabular->characterEncoding();
        $this->assertSame($expected, $actual);

        //==============================================
        $name       = array('test', 'name');

        $tabular    = Tabular::factory($name);

        $expected   = $name;
        $actual     = $tabular->getName();
        $this->assertSame($expected, $actual);

        $expected   = implode('::', $name);
        $actual     = $tabular->getCacheName();
        $this->assertSame($expected, $actual);
    }

    public function testGet()
    {
        //==============================================
        Tabular::factory();
        $tabular    = Tabular::get();

        //----------------------------------------------
        $expected   = Tabular::get();
        $actual     = $tabular;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_NAME;
        $actual     = $tabular->getName();
        $this->assertSame($expected, $actual);

        //==============================================
        $name   = array('test', 'get');

        Tabular::factory($name);
        $tabular    = Tabular::get($name);

        //----------------------------------------------
        $expected   = Tabular::get($name);
        $actual     = $tabular;
        $this->assertSame($expected, $actual);

        //==============================================
        $name           = 'test_name';
        $tab_width      = 2;
        $indent_level   = 1;
        $encoding       = 'SJIS-win';

        Tabular::factory($name, $tab_width, $indent_level, $encoding);
        $tabular    = Tabular::get($name);

        //----------------------------------------------
        $expected   = Tabular::factory($name);
        $actual     = $tabular;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $name;
        $actual     = $tabular->getName();
        $this->assertSame($expected, $actual);
    }

    public function testRemove()
    {
        //==============================================
        Tabular::factory();

        //----------------------------------------------
        $this->assertExceptionMessage(sprintf('Tabularキャッシュに無いキーを指定されました。name:%s', Convert::toDebugString(Tabular::DEFAULT_NAME)));

        Tabular::remove(Tabular::DEFAULT_NAME);
        Tabular::get();
    }

    public function testRemoveByName()
    {
        //==============================================
        $defaultTabular = Tabular::factory();

        $name           = 'test_name';
        Tabular::factory($name);

        Tabular::remove($name);

        //----------------------------------------------
        $expected   = $defaultTabular;
        $actual     = Tabular::get();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $this->assertExceptionMessage(sprintf('Tabularキャッシュに無いキーを指定されました。name:%s', Convert::toDebugString($name)));

        Tabular::get();
    }

    public function testDisposableFactory()
    {
        //==============================================
        $tabular    = Tabular::disposableFactory();

        //----------------------------------------------
        $expected   =null;
        $actual     = $tabular->getName();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::factory();
        $actual     = $tabular;
        $this->assertNotSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::disposableFactory();
        $actual     = $tabular;
        $this->assertNotSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_TAB_WIDTH;
        $actual     = $tabular->tabWidth();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_INDENTE_LEVEL;
        $actual     = $tabular->indenteLevel();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = Tabular::DEFAULT_CHARACTER_ENCODING;
        $actual     = $tabular->characterEncoding();
        $this->assertSame($expected, $actual);

        //==============================================
        $name           = null;
        $tab_width      = 2;
        $indent_level   = 1;
        $encoding       = 'SJIS-win';

        $tabular    = Tabular::disposableFactory($tab_width, $indent_level, $encoding);

        //----------------------------------------------
        $expected   = $name;
        $actual     = $tabular->getName();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $tab_width;
        $actual     = $tabular->tabWidth();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $indent_level;
        $actual     = $tabular->indenteLevel();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $expected   = $encoding;
        $actual     = $tabular->characterEncoding();
        $this->assertSame($expected, $actual);
    }

    public function testSettings1()
    {
        //==============================================
        $expected   = Tabular::defaultSettings();
        $actual     = array('header' => array(), 'rows' => array(), 'tab_width' => 4, 'indent_level' => 0, 'character_encodingg' => 'UTF-8', 'trim_eol_space' => FALSE);
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultHeader();
        $actual     = array();
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultRows();
        $actual     = array();
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultTabWidth();
        $actual     = Tabular::DEFAULT_TAB_WIDTH;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultIndenteLevel();
        $actual     = Tabular::DEFAULT_INDENTE_LEVEL;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultCharacterEncoding();
        $actual     = Tabular::DEFAULT_CHARACTER_ENCODING;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultTrimEolSpace();
        $actual     = false;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->settings();
        $actual     = array('header' => array(), 'rows' => array(), 'tab_width' => 4, 'indent_level' => 0, 'character_encodingg' => 'UTF-8', 'trim_eol_space' => FALSE);
        $this->assertSame($expected, $actual);

        $expected   = $tabular->header();
        $actual     = array();
        $this->assertSame($expected, $actual);

        $expected   = $tabular->rows();
        $actual     = array();
        $this->assertSame($expected, $actual);

        $expected   = $tabular->tabWidth();
        $actual     = Tabular::DEFAULT_TAB_WIDTH;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->indenteLevel();
        $actual     = Tabular::DEFAULT_INDENTE_LEVEL;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->characterEncoding();
        $actual     = Tabular::DEFAULT_CHARACTER_ENCODING;
        $this->assertSame($expected, $actual);

        //==============================================
        $header         = array('header1', 'header2');
        $rows           = array('row1', 'row2');
        $tab_width      = 2;
        $indent_level   = 1;
        $encoding       = 'SJIS-win';
        $trim_eol_space = false;

        $default_settings   = array(
            'header'                => $header,
            'rows'                  => $rows,
            'tab_width'             => $tab_width,
            'indent_level'          => $indent_level,
            'character_encodingg'   => $encoding,
            'trim_eol_space'        => $trim_eol_space,
        );

        //----------------------------------------------
        $tabular    = Tabular::factory();
        $tabular->settings($default_settings);

        $expected   = $tabular->settings();
        $actual     = $default_settings;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->header();
        $actual     = $header;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->rows();
        $actual     = $rows;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->tabWidth();
        $actual     = $tab_width;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->indenteLevel();
        $actual     = $indent_level;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->characterEncoding();
        $actual     = $encoding;
        $this->assertSame($expected, $actual);
    }

    public function testSettings2()
    {
        //==============================================
        $header         = array('header1', 'header2');
        $rows           = array('row1', 'row2');
        $tab_width      = 2;
        $indent_level   = 1;
        $encoding       = 'SJIS-win';
        $trim_eol_space = true;

        $default_settings   = array(
            'header'                => $header,
            'rows'                  => $rows,
            'tab_width'             => $tab_width,
            'indent_level'          => $indent_level,
            'character_encodingg'   => $encoding,
            'trim_eol_space'        => $trim_eol_space,
        );

        //----------------------------------------------
        Tabular::defaultSettings($default_settings);

        $expected   = Tabular::defaultSettings();
        $actual     = $default_settings;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultHeader();
        $actual     = $header;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultRows();
        $actual     = $rows;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultTabWidth();
        $actual     = $tab_width;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultIndenteLevel();
        $actual     = $indent_level;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultCharacterEncoding();
        $actual     = $encoding;
        $this->assertSame($expected, $actual);

        $expected   = Tabular::defaultTrimEolSpace();
        $actual     = $trim_eol_space;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->settings();
        $actual     = $default_settings;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->header();
        $actual     = $header;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->rows();
        $actual     = $rows;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->tabWidth();
        $actual     = $tab_width;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->indenteLevel();
        $actual     = $indent_level;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->characterEncoding();
        $actual     = $encoding;
        $this->assertSame($expected, $actual);

        $expected   = $tabular->trimEolSpace();
        $actual     = $trim_eol_space;
        $this->assertSame($expected, $actual);
    }

    public function testHeader()
    {
        //----------------------------------------------
        $expected   = Tabular::defaultHeader();
        $actual     = array();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->header();
        $actual     = array();
        $this->assertSame($expected, $actual);

        //==============================================
        $header     = array('a', 'b', 'c');

        Tabular::defaultHeader($header);

        $expected   = Tabular::defaultHeader();
        $actual     = $header;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular->header($header);

        $expected   = $tabular->header();
        $actual     = $header;
        $this->assertSame($expected, $actual);

        //==============================================
        Tabular::factory(Tabular::DEFAULT_NAME);

        $tabular    = Tabular::factory();

        $expected   = $tabular->header();
        $actual     = $header;
        $this->assertSame($expected, $actual);
    }

    public function testRows()
    {
        //----------------------------------------------
        $expected   = Tabular::defaultRows();
        $actual     = array();
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->rows();
        $actual     = array();
        $this->assertSame($expected, $actual);

        //==============================================
        $rows       = array(
            'a', 'b', 'c',
            '1', '22', '333',
        );

        Tabular::defaultRows($rows);

        $expected   = Tabular::defaultRows();
        $actual     = $rows;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular->rows($rows);

        $expected   = $tabular->rows();
        $actual     = $rows;
        $this->assertSame($expected, $actual);

        //==============================================
        Tabular::factory(Tabular::DEFAULT_NAME);

        $tabular    = Tabular::factory();

        $expected   = $tabular->rows();
        $actual     = $rows;
        $this->assertSame($expected, $actual);
    }

    public function testTabWidth()
    {
        //----------------------------------------------
        $expected   = Tabular::defaultTabWidth();
        $actual     = Tabular::DEFAULT_TAB_WIDTH;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->tabWidth();
        $actual     = Tabular::DEFAULT_TAB_WIDTH;
        $this->assertSame($expected, $actual);

        //==============================================
        $tab_width  = 2;

        Tabular::defaultTabWidth($tab_width);

        $expected   = Tabular::defaultTabWidth();
        $actual     = $tab_width;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular->tabWidth($tab_width);

        $expected   = $tabular->tabWidth();
        $actual     = $tab_width;
        $this->assertSame($expected, $actual);

        //==============================================
        Tabular::factory(Tabular::DEFAULT_NAME);

        $tabular    = Tabular::factory();

        $expected   = $tabular->tabWidth();
        $actual     = $tab_width;
        $this->assertSame($expected, $actual);
    }

    public function testIndenteLevel()
    {
        //----------------------------------------------
        $expected   = Tabular::defaultIndenteLevel();
        $actual     = Tabular::DEFAULT_INDENTE_LEVEL;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->indenteLevel();
        $actual     = Tabular::DEFAULT_INDENTE_LEVEL;
        $this->assertSame($expected, $actual);

        //==============================================
        $indente_level  = 1;

        Tabular::defaultIndenteLevel($indente_level);

        $expected   = Tabular::defaultIndenteLevel();
        $actual     = $indente_level;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular->indenteLevel($indente_level);

        $expected   = $tabular->indenteLevel();
        $actual     = $indente_level;
        $this->assertSame($expected, $actual);

        //==============================================
        Tabular::factory(Tabular::DEFAULT_NAME);

        $tabular    = Tabular::factory();

        $expected   = $tabular->indenteLevel();
        $actual     = $indente_level;
        $this->assertSame($expected, $actual);
    }

    public function testCharacterEncoding()
    {
        //----------------------------------------------
        $expected   = Tabular::defaultCharacterEncoding();
        $actual     = Tabular::DEFAULT_CHARACTER_ENCODING;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->characterEncoding();
        $actual     = Tabular::DEFAULT_CHARACTER_ENCODING;
        $this->assertSame($expected, $actual);

        //==============================================
        $character_encoding = 'SJIS-win';

        Tabular::defaultCharacterEncoding($character_encoding);

        $expected   = Tabular::defaultCharacterEncoding();
        $actual     = $character_encoding;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular->characterEncoding($character_encoding);

        $expected   = $tabular->characterEncoding();
        $actual     = $character_encoding;
        $this->assertSame($expected, $actual);

        //==============================================
        Tabular::factory(Tabular::DEFAULT_NAME);

        $tabular    = Tabular::factory();

        $expected   = $tabular->characterEncoding();
        $actual     = $character_encoding;
        $this->assertSame($expected, $actual);
    }

    public function testTrimEolSpace()
    {
        //----------------------------------------------
        $expected   = Tabular::defaultTrimEolSpace();
        $actual     = false;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->trimEolSpace();
        $actual     = false;
        $this->assertSame($expected, $actual);

        //==============================================
        $trim_eol_space = true;

        Tabular::defaultTrimEolSpace($trim_eol_space);

        $expected   = Tabular::defaultTrimEolSpace();
        $actual     = $trim_eol_space;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular->trimEolSpace($trim_eol_space);

        $expected   = $tabular->trimEolSpace();
        $actual     = $trim_eol_space;
        $this->assertSame($expected, $actual);

        //==============================================
        Tabular::factory(Tabular::DEFAULT_NAME);

        $tabular    = Tabular::factory();

        $expected   = $tabular->trimEolSpace();
        $actual     = $trim_eol_space;
        $this->assertSame($expected, $actual);
    }

    public function testGetName()
    {
        //----------------------------------------------
        $tabular    = Tabular::factory();

        $expected   = $tabular->getName();
        $actual     = Tabular::DEFAULT_NAME;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $name   = 'name2';

        $tabular    = Tabular::factory($name);

        $expected   = $tabular->getName();
        $actual     = $name;
        $this->assertSame($expected, $actual);
    }

    public function testStringWidth()
    {
        //==============================================
        $string = 'a';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 1;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 'ab';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 'あ';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 'aあb';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 4;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = '※';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 't';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 1;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = '†';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = '｢';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 1;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 'ﾡ';

        $expected   = Tabular::disposableFactory()->stringWidth($string);
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //==============================================
        $encoding   = 'SJIS-win';

        //----------------------------------------------
        $string = 'a';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 1;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 'ab';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 'あ';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 'aあb';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 4;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = '※';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = 't';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 1;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = '†';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 2;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $string = '｢';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 1;
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        // 変換不能文字
        $string = 'ﾡ';

        $expected   = Tabular::disposableFactory()->characterEncoding($encoding)->stringWidth(mb_convert_encoding($string, $encoding, 'UTF-8'));
        $actual     = 1;
        $this->assertSame($expected, $actual);
    }

    public function testBuildMaxWidthMap()
    {
        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array(1, 22, 333));
        $tabular->addRow(array(11, 222, 3));
        $tabular->addRow(array(111, 2, 33));

        $expected   = $tabular->buildMaxWidthMap();
        $actual     = array(3, 3, 3);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※'));
        $tabular->addRow(array('※※'));
        $tabular->addRow(array('※※※'));

        $expected   = $tabular->buildMaxWidthMap();
        $actual     = array(6);
        $this->assertSame($expected, $actual);
    }

    public function testBuildCellWidthMap()
    {
        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array(1, 22, 333));
        $tabular->addRow(array(11, 222, 3));
        $tabular->addRow(array(111, 2, 33));

        $expected   = $tabular->buildCellWidthMap();
        $actual     = array(4, 4, 4);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※'));
        $tabular->addRow(array('※※'));
        $tabular->addRow(array('※※※'));

        $expected   = $tabular->buildCellWidthMap();
        $actual     = array(8);
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※', '※'));
        $tabular->addRow(array('※※'));
        $tabular->addRow(array('※※※', '※'));

        $expected   = $tabular->buildCellWidthMap();
        $actual     = array(8, 4);
        $this->assertSame($expected, $actual);
    }

    public function testBuildRepart()
    {
        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array(1, 22, 333));

        $expected   = $tabular->buildRepart('a', 0);
        $actual     = '   ';
        $this->assertSame($expected, $actual);

        $expected   = $tabular->buildRepart('a', 1);
        $actual     = '   ';
        $this->assertSame($expected, $actual);

        $expected   = $tabular->buildRepart('a', 2);
        $actual     = '   ';
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※※※', '※'));

        $expected   = $tabular->buildRepart('a', 0);
        $actual     = '       ';
        $this->assertSame($expected, $actual);

        $expected   = $tabular->buildRepart('a', 1);
        $actual     = '   ';
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array(1, 22, 333));

        $expected   = $tabular->buildRepart('a', 0, '-');
        $actual     = '---';
        $this->assertSame($expected, $actual);

        $expected   = $tabular->buildRepart('a', 1, '-');
        $actual     = '---';
        $this->assertSame($expected, $actual);

        $expected   = $tabular->buildRepart('a', 2, '-');
        $actual     = '---';
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※※※', '※'));

        $expected   = $tabular->buildRepart('a', 0, '-');
        $actual     = '-------';
        $this->assertSame($expected, $actual);

        $expected   = $tabular->buildRepart('a', 1, '-');
        $actual     = '---';
        $this->assertSame($expected, $actual);
    }

    public function testBuild()
    {
        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array(1, 22, 333));
        $tabular->addRow(array(11, 222, 3));
        $tabular->addRow(array(111, 2, 33));

        $expected   = $tabular->build();
        $actual     = array('1   22  333 ', '11  222 3   ', '111 2   33  ');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※'));
        $tabular->addRow(array('※※'));
        $tabular->addRow(array('※※※'));

        $expected   = $tabular->build();
        $actual     = array('※      ', '※※    ', '※※※  ');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※', '※'));
        $tabular->addRow(array('※※'));
        $tabular->addRow(array('※※※', '※'));

        $expected   = $tabular->build();
        $actual     = array('※      ※  ', '※※    ', '※※※  ※  ');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array(1, 22, 333));
        $tabular->addRow(array(11, 222, 3));
        $tabular->addRow(array(111, 2, 33));

        $expected   = $tabular->build();
        $actual     = array('1   22  333 ', '11  222 3   ', '111 2   33  ');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※'));
        $tabular->addRow(array('※※'));
        $tabular->addRow(array('※※※'));

        $expected   = $tabular->build();
        $actual     = array('※      ', '※※    ', '※※※  ');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->addRow(array('※', '※'));
        $tabular->addRow(array('※※'));
        $tabular->addRow(array('※※※', '※'));

        $expected   = $tabular->build();
        $actual     = array('※      ※  ', '※※    ', '※※※  ※  ');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->header(array('aa', 'bb', 'cc'));

        $tabular->addRow(array(1, 22, 333));
        $tabular->addRow(array(11, 222, 3));
        $tabular->addRow(array(111, 2, 33));

        $expected   = $tabular->build();
        $actual     = array('aa  ', 'bb  ', 'cc  ', '1   22  333 ', '11  222 3   ', '111 2   33  ');
        $this->assertSame($expected, $actual);

        //----------------------------------------------
        $tabular    = Tabular::disposableFactory();

        $tabular->header(array('aa', 'bb', 'cc'));

        $tabular->addRows(array(
            array(1, 22, 333),
            array(11, 222, 3),
            array(111, 2, 33),
        ));

        $expected   = $tabular->build();
        $actual     = array('aa  ', 'bb  ', 'cc  ', '1   22  333 ', '11  222 3   ', '111 2   33  ');
        $this->assertSame($expected, $actual);
    }
}
