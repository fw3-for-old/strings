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

namespace fw3_for_old\strings\tabular;

use fw3_for_old\strings\builder\modifiers\ModifierInterface;
use fw3_for_old\strings\builder\modifiers\security\EscapeModifier;
use fw3_for_old\strings\builder\traits\converter\ConverterInterface;
use fw3_for_old\strings\converter\Convert;
use Closure;
use InvalidArgumentException;
use OutOfBoundsException;
use fw3_for_old\strings\builder\StringBuilder;

/**
 * 文字配列に対する表化を提供します。
 */
class Tabular
{
    //==============================================
    // constants
    //==============================================
    /**
     * @const   string  文字列ビルダキャッシュのデフォルト名
     */
    const DEFAULT_NAME                  = ':default:';

    /**
     * @const   string  エンコーディングのデフォルト値
     */
    const DEFAULT_CHARACTER_ENCODING    = null;

    /**
     * @const   string  タブ幅のデフォルト値
     */
    const DEFAULT_TAB_WIDTH = 4;

    /**
     * @var int     インデント向け基底文字列長：`    public static `
     */
    const INDENTE_BASE_LENGTH_PUBLIC_STATIC     = 18;

    /**
     * @var int     インデント向け基底文字列長：`    protected static `
     */
    const INDENTE_BASE_LENGTH_PROTECTED_STATIC  = 21;

    /**
     * @var int     インデント向け基底文字列長：`    private static `
     */
    const INDENTE_BASE_LENGTH_PRIVATE_STATIC    = 19;

    /**
     * @var int     インデント向け基底文字列長：`     * @var `
     */
    const INDENTE_BASE_LENGTH_DOC_COMMENT_VAR_TYPE  = 12;

    /**
     * @var int     インデント向け基底文字列長：`     * @param   `
     */
    const INDENTE_BASE_LENGTH_DOC_COMMENT_PARAM = 16;

    /**
     * @var int     インデント向け基底文字列長：`     * @return  `
     */
    const INDENTE_BASE_LENGTH_DOC_COMMENT_RETURN    = 16;

    /**
     * @var int     インデント向け基底文字列長：`    const `
     */
    const INDENTE_BASE_LENGTH_CONST = 10;

    /**
     * @var int     インデント向け基底文字列長：`    public const `
     */
    const INDENTE_BASE_LENGTH_PUBLIC_CONST = 17;

    /**
     * @var int     インデント向け基底文字列長：`    protected const `
     */
    const INDENTE_BASE_LENGTH_PROTECTED_CONST   = 20;

    /**
     * @var int     インデント向け基底文字列長：`    protected const `
     */
    const INDENTE_BASE_LENGTH_PRIVATE_CONST = 18;

    /**
     * @const   array   インデント向け基底文字列長マップ
     */
    public static $INDENTE_BASE_LENGTH_MAP  = array(
        'public static'         => self::INDENTE_BASE_LENGTH_PUBLIC_STATIC,
        'protected static'      => self::INDENTE_BASE_LENGTH_PROTECTED_STATIC,
        'private static'        => self::INDENTE_BASE_LENGTH_PRIVATE_STATIC,
        'doc comment var type'  => self::INDENTE_BASE_LENGTH_DOC_COMMENT_VAR_TYPE,
        'doc comment param'     => self::INDENTE_BASE_LENGTH_DOC_COMMENT_PARAM,
        'doc comment return'    => self::INDENTE_BASE_LENGTH_DOC_COMMENT_RETURN,
        'const'                 => self::INDENTE_BASE_LENGTH_CONST,
        'public const'          => self::INDENTE_BASE_LENGTH_PUBLIC_CONST,
        'protected const'       => self::INDENTE_BASE_LENGTH_PROTECTED_CONST,
        'private const'         => self::INDENTE_BASE_LENGTH_PRIVATE_CONST,
    );

    //==============================================
    // static properties
    //==============================================
    /**
     * @var array   インスタンスキャッシュ
     */
    protected static $instanceCache  = array();

    /**
     * @var string|null クラスデフォルトのエンコーディング
     */
    protected static $defaultCharacterEncoding   = self::DEFAULT_CHARACTER_ENCODING;

    /**
     * @var string|null クラスデフォルトのタブ幅のデフォルト値
     */
    protected static $defaultTabWidth   = self::DEFAULT_TAB_WIDTH;

    /**
     * @var callable[]  クラスデフォルトのインデントレベル
     */
    protected static $defaultIndenteLevel   = 0;

    /**
     * @var array       クラスデフォルトのヘッダ
     */
    protected static $defaultHeader = [];

    protected static $defaultHeaderRule = null;

    /**
     * @var array       クラスデフォルトのタブ化対象データ
     */
    protected static $defaultRows   = [];

    protected static $defaultRowRule    = null;
    protected static $defaultSeparator          = null;
    protected static $defaultEnclosureBegin     = null;
    protected static $defaultEnclosureEnd       = null;

    //==============================================
    // properties
    //==============================================
    /**
     * @var string  Tabularキャッシュ名
     */
    protected $name;

    /**
     * @var string|null エンコーディング
     */
    protected $characterEncoding;

    /**
     * @var int|null    タブ幅
     */
    protected $tabWidth;

    /**
     * @var int|null    インデントレベル
     */
    protected $indentLevel;

    /**
     * @var array       ヘッダ
     */
    protected $header   = [];

    protected $headerRule   = null;

    /**
     * @var array       タブ化対象データ
     */
    protected $rows     = [];

    protected $rowRule          = null;
    protected $separator        = null;
    protected $enclosureBegin   = null;
    protected $enclosureEnd     = null;

    protected $baseIndente  = null;

    protected $preBuildeMmaxWidthMap    = null;
    protected $preBuildeCellMaxWidthMap = null;

    //==============================================
    // factory methods
    //==============================================
    /**
     * construct
     *
     * @param   string      $name           文字列ビルダキャッシュ名
     * @param   int|null    $tab_width      タブ幅
     * @param   int|null    $indent_level   インデントレベル
     * @param   string|null $encoding       エンコーディング
     */
    protected function __construct($name, $tab_width = null, $indent_level = null, $encoding = null)
    {
        $this->name         = $name;

        $this->tabWidth(isset($tab_width) ? $tab_width : static::$defaultTabWidth);
        $this->indenteLevel(isset($indent_level) ? $indent_level : static::$defaultIndenteLevel);

        $this->header(static::$defaultHeader);
        $this->rows(static::$defaultRows);


        $this->separator(static::$defaultSeparator);
        $this->enclosureBegin(static::$defaultEnclosureBegin);
        $this->enclosureEnd(static::$defaultEnclosureEnd);

        $this->characterEncoding    = isset($encoding) ? $encoding : (
            isset(static::$defaultCharacterEncoding) ? static::$defaultCharacterEncoding :mb_internal_encoding()
        );
    }

    /**
     * factory
     *
     * @param   string|array    $name           文字列ビルダキャッシュ名
     * @param   int|null        $tab_width      タブ幅
     * @param   int|null        $indent_level   インデントレベル
     * @param   string|null     $encoding       エンコーディング
     * @return  static  このインスタンス
     */
    public static function factory($name = self::DEFAULT_NAME, $tab_width = null, $indent_level = null, $encoding = null)
    {
        if (is_array($name)) {
            $name   = implode('::', $name);
        }

        if (!isset(static::$instanceCache[$name])) {
            static::$instanceCache[$name] = new static($name, $tab_width, $indent_level, $encoding);
        }

        return static::$instanceCache[$name];
    }

    /**
     * インスタンスをキャッシュしない使い捨てファクトリです。
     *
     * @param   int|null    $tab_width      タブ幅
     * @param   int|null    $indent_level   インデントレベル
     * @param   string|null $encoding       エンコーディング
     * @return  static  このインスタンス
     */
    public static function disposableFactory($tab_width = null, $indent_level = null, $encoding = null)
    {
        return new static(null, $tab_width, $indent_level, $encoding);
    }

    //==============================================
    // static methods
    //==============================================
    /**
     * 指定されたビルダキャッシュ名に紐づくビルダインスタンスを返します。
     *
     * @param   string  $name   ビルダキャッシュ名
     * @return  static  このインスタンス
     */
    public static function get($name = self::DEFAULT_NAME)
    {
        if (!isset(static::$instanceCache[$name])) {
            throw new OutOfBoundsException(sprintf('Tabularキャッシュに無いキーを指定されました。name:%s', Convert::toDebugString($name)));
        }

        return static::$instanceCache[$name];
    }

    /**
     * 指定されたビルダキャッシュ名に紐づくビルダキャッシュを削除します。
     *
     * @param   string  $name   ビルダキャッシュ名
     * @return  string  このクラスパス
     */
    public static function remove($name)
    {
        unset(static::$instanceCache[$name]);

        return get_called_class();
    }

    //==============================================
    // static property accessors
    //==============================================
    /**
     * デフォルトの設定を纏めて設定・取得します。
     *
     * @param   array|null  $default_settings   デフォルトの設定
     * @return  string|array    このクラスパスまたはデフォルトの設定
     */
    public static function defaultSettings($default_settings = null)
    {
        if (!is_array($default_settings)) {
            return array(
                'header'                => static::defaultHeader(),
                'header_rule'           => static::defaultHeaderRule(),
                'rows'                  => static::defaultRows(),
                'row_rule'              => static::defaultRowRule(),
                'separator'             => static::defaultSeparator(),
                'enclosure_begin'       => static::defaultEnclosureBegin(),
                'enclosure_end'         => static::defaultEnclosureEnd(),
                'tab_width'             => static::defaultTabWidth(),
                'indent_level'          => static::defaultIndenteLevel(),
                'character_encodingg'   => static::defaultCharacterEncoding(),
            );
        }

        if (isset($default_settings['header'])) {
            static::defaultHeader($default_settings['header']);
        }

        if (isset($default_settings['header_rule'])) {
            static::defaultHeaderRule($default_settings['header_rule']);
        }

        if (isset($default_settings['rows'])) {
            static::defaultRows($default_settings['rows']);
        }

        if (isset($default_settings['row_rule'])) {
            static::defaultRowRule($default_settings['row_rule']);
        }

        if (isset($default_settings['separator'])) {
            static::defaultSeparator($default_settings['separator']);
        }

        if (isset($default_settings['enclosure_start'])) {
            static::defaultEnclosureBegin($default_settings['enclosure_start']);
        }

        if (isset($default_settings['enclosure_end'])) {
            static::defaultEnclosureEnd($default_settings['enclosure_end']);
        }

        if (isset($default_settings['tab_width'])) {
            static::defaultTabWidth($default_settings['tab_width']);
        }

        if (isset($default_settings['indent_level'])) {
            static::defaultIndenteLevel($default_settings['indent_level']);
        }

        if (isset($default_settings['character_encodingg'])) {
            static::defaultCharacterEncoding($default_settings['character_encodingg']);
        }

        return get_called_class();
    }

    /**
     * クラスデフォルトのヘッダを設定・取得します。
     *
     * @param   array|\Closure|null $header クラスデフォルトのヘッダ
     * @return  string|array|\Closure   このクラスパスまたはクラスデフォルトのヘッダ
     */
    public static function defaultHeader($header = null)
    {
        if ($header === null) {
            return static::$defaultHeader;
        }

        if (!is_array($header) && !($header instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。header:%s', Convert::toDebugString($header, 2)));
        }

        static::$defaultHeader  = $header;
        return get_called_class();
    }

    /**
     * クラスデフォルトのヘッダ罫線を設定・取得します。
     *
     * @param   string|array|\Closure|null  $header_rule    クラスデフォルトのヘッダ罫線
     * @return  string|array|\Closure   このクラスパスまたはクラスデフォルトのヘッダ罫線
     */
    public static function defaultHeaderRule($header_rule = null)
    {
        if ($header_rule === null) {
            return static::$defaultHeaderRule;
        }

        if (!is_string($header_rule) && !is_array($header_rule) && !($header_rule instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。header_rule:%s', Convert::toDebugString($header_rule, 2)));
        }

        static::$defaultHeaderRule  = $header_rule;
        return get_called_class();
    }

    /**
     * クラスデフォルトの行を設定・取得します。
     *
     * @param   array|\Closure|null $rows   クラスデフォルトの行
     * @return  string|array|\Closure   このクラスパスまたはクラスデフォルトの行
     */
    public static function defaultRows($rows = null)
    {
        if ($rows === null) {
            return static::$defaultRows;
        }

        if (!is_array($rows) && !($rows instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。rows:%s', Convert::toDebugString($rows, 2)));
        }

        static::$defaultRows    = $rows;
        return get_called_class();
    }

    /**
     * クラスデフォルトの罫線を設定・取得します。
     *
     * @param   string|array|\Closure|null  $row_rule   クラスデフォルトの罫線
     * @return  string|array|\Closure   このクラスパスまたはクラスデフォルトの罫線
     */
    public static function defaultRowRule($row_rule = null)
    {
        if ($row_rule === null) {
            return static::$defaultRowRule;
        }

        if (!is_string($row_rule) && !is_array($row_rule) && !($row_rule instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。row_rule:%s', Convert::toDebugString($row_rule, 2)));
        }

        static::$defaultRowRule = $row_rule;
        return get_called_class();
    }

    /**
     * クラスデフォルトの行エンクロージャを設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_begin    クラスデフォルトの行開始文字列
     * @param   string|\Closure|null    $enclosure_end      クラスデフォルトの行終了文字列
     * @return  string|array        このクラスパスまたはクラスデフォルトの行エンクロージャ
     */
    public static function defaultEnclosure($enclosure_begin = null, $enclosure_end = null)
    {
        if ($enclosure_begin === null) {
            return array(
                'begin' => static::$defaultEnclosureBegin,
                'end'   => static::$defaultEnclosureEnd,
            );
        }

        if (is_array($enclosure_begin)) {
            $enclosure          = $enclosure_begin;
            $enclosure_begin    = isset($enclosure['begin']) ? $enclosure['begin'] : (
                isset($enclosure[0]) ? $enclosure[0] : null
            );

            $enclosure_end      = isset($enclosure['end']) ? $enclosure['end'] : (
                isset($enclosure[1]) ? $enclosure[1] : null
            );
        }

        static::defaultEnclosureBegin($enclosure_begin);
        static::defaultEnclosureEnd($enclosure_end);
        return get_called_class();
    }

    /**
     * クラスデフォルトの行開始文字列を設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_begin    クラスデフォルトの行開始文字列
     * @return  string|\Closure このクラスパスまたはクラスデフォルトの行開始文字列
     */
    public static function defaultEnclosureBegin($enclosure_begin = null)
    {
        if ($enclosure_begin === null) {
            return static::$defaultEnclosureBegin;
        }

        if (!is_string($enclosure_begin) && !($enclosure_begin instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。enclosure_begin:%s', Convert::toDebugString($enclosure_begin, 2)));
        }

        static::$defaultEnclosureBegin = $enclosure_begin;
        return get_called_class();
    }

    /**
     * クラスデフォルトの行終了文字列を設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_end  クラスデフォルトの行終了文字列
     * @return  string|\Closure このクラスパスまたはクラスデフォルトの行終了文字列
     */
    public static function defaultEnclosureEnd($enclosure_end = null)
    {
        if ($enclosure_end === null) {
            return static::$defaultEnclosureEnd;
        }

        if (!is_string($enclosure_end) && !is_array($enclosure_end) && !($enclosure_end instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。enclosure_end:%s', Convert::toDebugString($enclosure_end, 2)));
        }

        static::$defaultEnclosureEnd = $enclosure_end;
        return get_called_class();
    }

    /**
     * クラスデフォルトの行セパレータを設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_end  クラスデフォルトの行セパレータ
     * @return  string|\Closure このクラスパスまたはクラスデフォルトの行セパレータ
     */
    public static function defaultSeparator($separator = null)
    {
        if ($separator === null) {
            return static::$defaultSeparator;
        }

        if (!is_string($separator) && !($separator instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。enclosure_end:%s', Convert::toDebugString($separator, 2)));
        }

        static::$defaultSeparator = $separator;
        return get_called_class();
    }

    /**
     * クラスデフォルトのタブ幅を設定・取得します。
     *
     * @param   int|string|null $tab_width  クラスデフォルトのタブ幅
     * @return  string|int  このクラスパスまたはクラスデフォルトのタブ幅
     */
    public static function defaultTabWidth($tab_width = null)
    {
        if ($tab_width === null) {
            return static::$defaultTabWidth;
        }

        if (!is_int($tab_width) && !(is_string($tab_width) && filter_var($tab_width, \FILTER_VALIDATE_INT))) {
            throw new \Exception(sprintf('利用できない値を指定されました。tab_width:%s', Convert::toDebugString($tab_width, 2)));
        }

        static::$defaultTabWidth = $tab_width;
        return get_called_class();
    }

    /**
     * クラスデフォルトのインデントレベルを設定・取得します。
     *
     * @param   int|string|null $tab_width  クラスデフォルトのインデントレベル
     * @return  string|int  このクラスパスまたはクラスデフォルトのインデントレベル
     */
    public static function defaultIndenteLevel($indent_level = null)
    {
        if ($indent_level === null) {
            return static::$defaultIndenteLevel;
        }

        if (!is_int($indent_level) && !(is_string($indent_level) && filter_var($indent_level, \FILTER_VALIDATE_INT))) {
            throw new \Exception(sprintf('利用できない値を指定されました。indent_level:%s', Convert::toDebugString($indent_level, 2)));
        }

        static::$defaultIndenteLevel = $indent_level;
        return get_called_class();
    }

    /**
     * クラスデフォルトのエンコーディングを設定・取得します。
     *
     * @param   string|null $character_encoding クラスデフォルトのエンコーディング
     * @return  string|null このクラスパスまたはクラスデフォルトのエンコーディング
     */
    public static function defaultCharacterEncoding($character_encoding = null)
    {
        if ($character_encoding === null && func_num_args() === 0) {
            return static::$defaultCharacterEncoding;
        }

        if ($character_encoding === null) {
            static::$defaultCharacterEncoding = $character_encoding;
            return get_called_class();
        }

        if (!in_array($character_encoding, mb_list_encodings(), true)) {
            throw new InvalidArgumentException(sprintf('現在のシステムで利用できないエンコーディングを指定されました。character_encoding:%s', Convert::toDebugString($character_encoding)));
        }

        static::$defaultCharacterEncoding = $character_encoding;
        return get_called_class();
    }

    //==============================================
    // property accessors
    //==============================================
    /**
     * 設定を纏めて設定・取得します。
     *
     * @param   array|null  $settings   設定
     * @return  static|array    このインスタンスまたは設定
     */
    public function settings($settings = null)
    {
        if (!is_array($settings)) {
            return array(
                'header'                => $this->header(),
                'header_rule'           => $this->headerRule(),
                'rows'                  => $this->rows(),
                'row_rule'              => $this->rowRule(),
                'separator'             => $this->separator(),
                'enclosure_begin'       => $this->enclosureBegin(),
                'enclosure_end'         => $this->enclosureEnd(),
                'tab_width'             => $this->tabWidth(),
                'indent_level'          => $this->indenteLevel(),
                'character_encodingg'   => $this->characterEncoding(),
            );
        }

        if (isset($settings['header'])) {
            $this->header($settings['header']);
        }

        if (isset($settings['header_rule'])) {
            $this->headerRule($settings['header_rule']);
        }

        if (isset($settings['rows'])) {
            $this->rows($settings['rows']);
        }

        if (isset($settings['row_rule'])) {
            $this->rowRule($settings['row_rule']);
        }

        if (isset($settings['separator'])) {
            $this->separator($settings['separator']);
        }

        if (isset($settings['enclosure_start'])) {
            $this->enclosureBegin($settings['enclosure_start']);
        }

        if (isset($settings['enclosure_end'])) {
            $this->enclosureEnd($settings['enclosure_end']);
        }

        if (isset($settings['tab_width'])) {
            $this->tabWidth($settings['tab_width']);
        }

        if (isset($settings['indent_level'])) {
            $this->indenteLevel($settings['indent_level']);
        }

        if (isset($settings['character_encodingg'])) {
            $this->characterEncoding($settings['character_encodingg']);
        }

        return $this;
    }

    protected function initPreBuilding()
    {
        $this->preBuildeMmaxWidthMap    = null;
        $this->preBuildeCellMaxWidthMap = null;
    }

    /**
     * 文字列ビルダキャッシュ名を返します。
     *
     * @return  string  文字列ビルダキャッシュ名
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 行を追加します。
     *
     * @param   array   $row    行
     * @return  static  このインスタンス
     */
    public function addRow($row)
    {
        $this->initPreBuilding();

        $this->rows[]   = $row;
        return $this;
    }

    /**
     * 複数行を追加します。
     *
     * @param   array   $rows   複数行
     * @return  static  このインスタンス
     */
    public function addRows($rows)
    {
        $this->initPreBuilding();

        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new \Exception(sprintf('次元の足りない行を指定されました。row:%s', Convert::toDebugString($row, 2)));
            }

            $this->rows[]   = $row;
        }
        return $this;
    }

    /**
     * ヘッダを設定・取得します。
     *
     * @param   array|\Closure|null $header ヘッダ
     * @return  string|array|\Closure   このインスタンスまたはヘッダ
     */
    public function header($header = null)
    {
        if ($header === null) {
            return $this->header;
        }

        if (!is_array($header) && !($header instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。header:%s', Convert::toDebugString($header, 2)));
        }

        $this->initPreBuilding();

        $this->header  = $header;
        return $this;
    }

    /**
     * ヘッダ罫線を設定・取得します。
     *
     * @param   string|array|\Closure|null  $header_rule    ヘッダ罫線
     * @return  string|array|\Closure   このインスタンスまたはヘッダ罫線
     */
    public function headerRule($header_rule = null)
    {
        if ($header_rule === null) {
            return $this->headerRule;
        }

        if (!is_string($header_rule) && !is_array($header_rule) && !($header_rule instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。header_rule:%s', Convert::toDebugString($header_rule, 2)));
        }

        $this->initPreBuilding();

        $this->headerRule  = $header_rule;
        return $this;
    }

    /**
     * 行を設定・取得します。
     *
     * @param   array|\Closure|null $rows   行
     * @return  string|array|\Closure   このインスタンスまたは行
     */
    public function rows($rows = null)
    {
        if ($rows === null) {
            return $this->rows;
        }

        if (!is_array($rows) && !($rows instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。rows:%s', Convert::toDebugString($rows, 2)));
        }

        $this->initPreBuilding();

        $this->rows    = $rows;
        return $this;
    }

    /**
     * 罫線を設定・取得します。
     *
     * @param   string|array|\Closure|null  $row_rule   罫線
     * @return  string|array|\Closure   このインスタンスまたは罫線
     */
    public function rowRule($row_rule = null)
    {
        if ($row_rule === null) {
            return $this->rowRule;
        }

        if (!is_string($row_rule) && !is_array($row_rule) && !($row_rule instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。row_rule:%s', Convert::toDebugString($row_rule, 2)));
        }

        $this->initPreBuilding();

        $this->rowRule = $row_rule;
        return $this;
    }

    /**
     * 行エンクロージャを設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_begin    行開始文字列
     * @param   string|\Closure|null    $enclosure_end      行終了文字列
     * @return  string|array        このインスタンスまたは行エンクロージャ
     */
    public function enclosure($enclosure_begin = null, $enclosure_end = null)
    {
        if ($enclosure_begin === null) {
            return array(
                'begin' => $this->enclosureBegin,
                'end'   => $this->enclosureEnd,
            );
        }

        if (is_array($enclosure_begin)) {
            $enclosure          = $enclosure_begin;
            $enclosure_begin    = isset($enclosure['begin']) ? $enclosure['begin'] : (
                isset($enclosure[0]) ? $enclosure[0] : null
            );

            $enclosure_end      = isset($enclosure['end']) ? $enclosure['end'] : (
                isset($enclosure[1]) ? $enclosure[1] : null
            );
        }

        $this->enclosureBegin($enclosure_begin);
        $this->enclosureEnd($enclosure_end);
        return $this;
    }

    /**
     * 行開始文字列を設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_begin    行開始文字列
     * @return  string|\Closure このインスタンスまたは行開始文字列
     */
    public function enclosureBegin($enclosure_begin = null)
    {
        if ($enclosure_begin === null) {
            return $this->enclosureBegin;
        }

        if (!is_string($enclosure_begin) && !($enclosure_begin instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。enclosure_begin:%s', Convert::toDebugString($enclosure_begin, 2)));
        }

        $this->initPreBuilding();

        $this->enclosureBegin = $enclosure_begin;
        return $this;
    }

    /**
     * 行終了文字列を設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_end  行終了文字列
     * @return  string|\Closure このインスタンスまたは行終了文字列
     */
    public function enclosureEnd($enclosure_end = null)
    {
        if ($enclosure_end === null) {
            return $this->enclosureEnd;
        }

        if (!is_string($enclosure_end) && !is_array($enclosure_end) && !($enclosure_end instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。enclosure_end:%s', Convert::toDebugString($enclosure_end, 2)));
        }

        $this->initPreBuilding();

        $this->enclosureEnd = $enclosure_end;
        return $this;
    }

    /**
     * 行セパレータを設定・取得します。
     *
     * @param   string|\Closure|null    $enclosure_end  行セパレータ
     * @return  string|\Closure このインスタンスまたは行セパレータ
     */
    public function separator($separator = null)
    {
        if ($separator === null) {
            return $this->separator;
        }

        if (!is_string($separator) && !($separator instanceof \Closure)) {
            throw new \Exception(sprintf('利用できない値を指定されました。enclosure_end:%s', Convert::toDebugString($separator, 2)));
        }

        $this->initPreBuilding();

        $this->separator = $separator;
        return $this;
    }

    /**
     * タブ幅を設定・取得します。
     *
     * @param   int|string|null $tab_width  タブ幅
     * @return  string|int  このインスタンスまたはタブ幅
     */
    public function tabWidth($tab_width = null)
    {
        if ($tab_width === null) {
            return $this->tabWidth;
        }

        if (!is_int($tab_width) && !(is_string($tab_width) && filter_var($tab_width, \FILTER_VALIDATE_INT))) {
            throw new \Exception(sprintf('利用できない値を指定されました。tab_width:%s', Convert::toDebugString($tab_width, 2)));
        }

        $this->initPreBuilding();

        $this->tabWidth = $tab_width;
        return $this;
    }

    /**
     * インデントレベルを設定・取得します。
     *
     * @param   int|string|null $tab_width  インデントレベル
     * @return  string|int  このインスタンスまたはインデントレベル
     */
    public function indenteLevel($indent_level = null)
    {
        if ($indent_level === null) {
            return $this->indenteLevel;
        }

        if (!is_int($indent_level) && !(is_string($indent_level) && filter_var($indent_level, \FILTER_VALIDATE_INT))) {
            throw new \Exception(sprintf('利用できない値を指定されました。indent_level:%s', Convert::toDebugString($indent_level, 2)));
        }

        $this->initPreBuilding();

        $this->indenteLevel = $indent_level;
        return $this;
    }

    /**
     * エンコーディングを設定・取得します。
     *
     * @param   string|null $character_encoding エンコーディング
     * @return  string|null このインスタンスまたはエンコーディング
     */
    public function characterEncoding($character_encoding = null)
    {
        if ($character_encoding === null && func_num_args() === 0) {
            return $this->characterEncoding;
        }

        if ($character_encoding === null) {
            $this->characterEncoding = $character_encoding;
            return $this;
        }

        if (!in_array($character_encoding, mb_list_encodings(), true)) {
            throw new InvalidArgumentException(sprintf('現在のシステムで利用できないエンコーディングを指定されました。character_encoding:%s', Convert::toDebugString($character_encoding)));
        }

        $this->initPreBuilding();

        $this->characterEncoding = $character_encoding;
        return $this;
    }

    //==============================================
    // supporter
    //==============================================
    /**
     * 文字列幅を取得します。
     */
    public function stringWidth($string)
    {
        $convert_charrcter_encoding = $this->characterEncoding === 'UTF-8';

        if ($convert_charrcter_encoding) {
            $string = mb_convert_encoding($string, 'UTF-8', $this->characterEncoding);
        }

        $string_width = 0;
        for ($string_length = mb_strlen($string, 'UTF-8'), $i = 0;$i < $string_length;++$i) {
            $char   = mb_substr($string, $i, 1, 'UTF-8');

            if ($char !== mb_convert_encoding($char, 'UTF-8', 'UTF-8')) {
                return 0xFFFD;
            }

            $ret    = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');

            $char_code  = hexdec(bin2hex($ret));

            $width = 0;
            if (0x0000 <= $char_code && $char_code <= 0x0019) {
            } else if (0x0020 <= $char_code && $char_code <= 0x1FFF) {
                $width  = 1;
            } else if (0x2000 <= $char_code && $char_code <= 0xFF60) {
                $width  = 2;
            } else if (0xFF61 <= $char_code && $char_code <= 0xFF9F) {
                $width  = 1;
            } else if (0xFFA0 <= $char_code) {
                $width  = 2;
            }

            $string_width += $width;
        }

        return $string_width;
    }

    //==============================================
    // builder
    //==============================================
    /**
     * セル内の最大文字幅を返します。
     *
     * @return  array   セル内の最大文字幅
     */
    public function buildMaxWidthMap()
    {
        $indent_map     = [];
        $max_width_map  = [];

        foreach (array_values($this->header) as $idx => $node) {
            $max_width_map[$idx]    = $this->stringWidth($node);
        }

        $rows   = $this->rows;
        reset($rows);
        if (empty($rows)) {
            return $max_width_map;
        }

        if (empty($max_width_map)) {
            foreach (array_values(current($rows)) as $idx => $node) {
                $node_width = $this->stringWidth($node);
                if (isset($max_width_map[$idx])) {
                    $max_width_map[$idx] > $node_width ?: $max_width_map[$idx] = $node_width;
                } else {
                    $max_width_map[$idx]    = 0;
                }
            }
        }

        foreach ($rows as $row) {
            foreach (array_values($row) as $idx => $node) {
                $node_width = $this->stringWidth($node);
                $max_width_map[$idx] > $node_width ?: $max_width_map[$idx] = $node_width;
            }
        }

        $this->preBuildeMmaxWidthMap    = $max_width_map;

        return $max_width_map;
    }

    /**
     * セル幅を構築し返します。
     *
     * @param   string|int|null $base_indente   先行する文字列幅
     * @param   array           $max_width_map  セル内最大文字列幅マップ
     * @return number[]
     */
    public function buildCellWidthMap()
    {
        if ($this->preBuildeMmaxWidthMap === null) {
            $this->buildMaxWidthMap();
        }
        $max_width_map  = $this->preBuildeMmaxWidthMap;

        $base_indente   = 0;
        if (is_int($this->baseIndente)) {
            $base_indente   = $this->baseIndente;
        } elseif (is_string($this->baseIndente) && isset(static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente])) {
            $base_indente   = static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente];
        }

        $tab_width  = $this->tabWidth;
        $base_width = $base_indente + $this->indentLevel * $tab_width;

        $cell_max_width_map = [];
        foreach ($max_width_map as $idx => $cell_in_max_width) {
            $cell_max_width_map[$idx] = 0 === ($indente = ($base_width + $cell_in_max_width) % $tab_width) ? $cell_in_max_width + $tab_width : $cell_in_max_width + $tab_width - $indente;
        }

        $this->preBuildeCellMaxWidthMap = $cell_max_width_map;

        return $cell_max_width_map;
    }

    /**
     *
     * @param string $repart
     * @param number $base_indente
     * @param unknown $max_width_map
     */
    public function buildRepart($string, $idx, $repart = ' ', $vector = null)
    {
        if ($this->preBuildeCellMaxWidthMap === null) {
            $this->buildCellWidthMap();
        }
        $cell_max_width_map  = $this->preBuildeCellMaxWidthMap;

        return str_repeat($repart, $cell_max_width_map[$idx] - $this->stringWidth($string));
    }

    /**
     * ビルドします。
     *
     * @return  string  ビルド後の文字列
     */
    public function build()
    {
        if ($this->preBuildeCellMaxWidthMap === null) {
            $this->buildCellWidthMap();
        }
        $cell_max_width_map  = $this->preBuildeCellMaxWidthMap;

        $message    = [];

        $base_indente   = 0;
        if (is_int($this->baseIndente)) {
            $base_indente   = $this->baseIndente;
        } elseif (is_string($this->baseIndente) && isset(static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente])) {
            $base_indente   = static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente];
        }

        foreach ($this->header as $idx => $cell) {
            $messages[]  = sprintf('%s%s', $cell, $this->buildRepart($cell, $idx, ' ', null, $base_indente, $cell_max_width_map));
        }

        foreach ($this->rows as $row) {
            $message    = [];
            foreach (array_values($row) as $idx => $cell) {
                $messages[]  = sprintf('%s%s', $cell, $this->buildRepart($cell, $idx, ' ', null, $base_indente, $cell_max_width_map));
            }
            $messages[] = implode('', $message);
        }

        return $messages;
    }
}
