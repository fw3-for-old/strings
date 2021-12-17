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

use fw3_for_old\strings\converter\Convert;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * 文字配列に対する表化を提供します。
 */
class Tabular
{
    //==============================================
    // constants
    //==============================================
    /**
     * @var string  ビルダキャッシュのデフォルト名
     */
    const DEFAULT_NAME                  = ':default:';

    /**
     * @const   string  エンコーディングのデフォルト値
     */
    const DEFAULT_CHARACTER_ENCODING    = 'UTF-8';

    /**
     * @var int     タブ幅のデフォルト値
     */
    const DEFAULT_TAB_WIDTH = 4;

    /**
     * @var int     インデントレベルのデフォルト値
     */
    const DEFAULT_INDENTE_LEVEL     = 0;

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

    /**
     * @var string  インデントで使用する文字
     */
    const INDENTE_CHAR  = ' ';

    //==============================================
    // static properties
    //==============================================
    /**
     * @var array   インスタンスキャッシュ
     */
    protected static $instanceCache  = array();

    /**
     * @var string  クラスデフォルトのエンコーディング
     */
    protected static $defaultCharacterEncoding   = self::DEFAULT_CHARACTER_ENCODING;

    /**
     * @var int     クラスデフォルトのタブ幅のデフォルト値
     */
    protected static $defaultTabWidth   = self::DEFAULT_TAB_WIDTH;

    /**
     * @var int     クラスデフォルトのインデントレベル
     */
    protected static $defaultIndenteLevel   = self::DEFAULT_INDENTE_LEVEL;

    /**
     * @var array   クラスデフォルトのヘッダ
     */
    protected static $defaultHeader = array();

    /**
     * @var array   クラスデフォルトのタブ化対象データ
     */
    protected static $defaultRows   = array();

    /**
     * @var bool    クラスデフォルトの行末スペーストリムを行うかどうか
     */
    protected static $defaultTrimEolSpace   = false;

    //==============================================
    // properties
    //==============================================
    /**
     * @var string|null Tabularキャッシュ名
     */
    protected $cacheName    = null;

    /**
     * @var string|null Tabular名
     */
    protected $name = null;

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
     * @var array   ヘッダ
     */
    protected $header   = array();

    /**
     * @var array   タブ化対象データ
     */
    protected $rows     = array();

    /**
     * @var null|int    ベースとなるインデント量
     */
    protected $baseIndente  = null;

    /**
     * @var null|array  列単位での最大幅マップ
     */
    protected $preBuildeMaxWidthMap     = null;

    /**
     * @var bool    列の全てがnullだった場合に列をスキップするかどうか
     */
    protected $nullColumnSkip   = false;

    /**
     * @var null|array  全てがnullでない列マップ
     */
    protected $notNullColumnMap = null;

    /**
     * @var null|array  セル単位での最大幅マップ
     */
    protected $preBuildeCellMaxWidthMap = null;

    /**
     * @var bool    行末スペーストリムを行うかどうか
     */
    protected $trimEolSpace = false;

    //==============================================
    // factory methods
    //==============================================
    /**
     * construct
     *
     * @param   string|null $cache_name     ビルダキャッシュ名
     * @param   int|null    $tab_width      タブ幅
     * @param   int|null    $indent_level   インデントレベル
     * @param   string|null $encoding       エンコーディング
     */
    protected function __construct($cache_name, $tab_width = null, $indent_level = null, $encoding = null)
    {
        $this->cacheName    = $cache_name;

        $this->tabWidth(isset($tab_width) ? $tab_width : static::$defaultTabWidth);
        $this->indenteLevel(isset($indent_level) ? $indent_level : static::$defaultIndenteLevel);

        $this->header(static::$defaultHeader);
        $this->rows(static::$defaultRows);

        $this->characterEncoding    = isset($encoding) ? $encoding : (
            isset(static::$defaultCharacterEncoding) ? static::$defaultCharacterEncoding :mb_internal_encoding()
        );

        $this->trimEolSpace(static::$defaultTrimEolSpace);
    }

    /**
     * factory
     *
     * @param   string||array|null  $name           ビルダ名
     * @param   int|null            $tab_width      タブ幅
     * @param   int|null            $indent_level   インデントレベル
     * @param   string|null         $encoding       エンコーディング
     * @return  static  このインスタンス
     */
    public static function factory($name = self::DEFAULT_NAME, $tab_width = null, $indent_level = null, $encoding = null)
    {
        $cache_name = is_array($name) ? implode('::', $name) : $name;

        if (!isset(static::$instanceCache[$cache_name])) {
            static::$instanceCache[$cache_name] = new static($cache_name, $tab_width, $indent_level, $encoding);
            static::$instanceCache[$cache_name]->setName($name);
        }

        return static::$instanceCache[$cache_name];
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
     * 指定されたビルダ名に紐づくビルダインスタンスを返します。
     *
     * @param   string||array|null  $name   ビルダ名
     * @return  static  このインスタンス
     */
    public static function get($name = self::DEFAULT_NAME)
    {
        $cache_name = is_array($name) ? implode('::', $name) : $name;

        if (!isset(static::$instanceCache[$cache_name])) {
            throw new OutOfBoundsException(sprintf('Tabularキャッシュに無いキーを指定されました。name:%s', Convert::toDebugString($name, 2)));
        }

        return static::$instanceCache[$cache_name];
    }

    /**
     * 指定されたビルダキャッシュ名に紐づくビルダキャッシュを削除します。
     *
     * @param   string  $name   ビルダ名
     * @return  string  このクラスパス
     */
    public static function remove($name)
    {
        $cache_name = is_array($name) ? implode('::', $name) : $name;

        unset(static::$instanceCache[$cache_name]);

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
                'rows'                  => static::defaultRows(),
                'tab_width'             => static::defaultTabWidth(),
                'indent_level'          => static::defaultIndenteLevel(),
                'character_encodingg'   => static::defaultCharacterEncoding(),
                'trim_eol_space'        => static::defaultTrimEolSpace(),
            );
        }

        if (isset($default_settings['header'])) {
            static::defaultHeader($default_settings['header']);
        }

        if (isset($default_settings['rows'])) {
            static::defaultRows($default_settings['rows']);
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

        if (isset($default_settings['trim_eol_space'])) {
            static::defaultTrimEolSpace($default_settings['trim_eol_space']);
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

    /**
     * クラスデフォルトの行末スペーストリムを行うかどうかを設定・取得します。
     *
     * @param   bool|null   $trim_eol_space クラスデフォルトの行末スペーストリムを行うかどうか
     * @return  bool|null   このクラスパスまたはクラスデフォルトの行末スペーストリムを行うかどうか
     */
    public static function defaultTrimEolSpace($trim_eol_space = false)
    {
        if ($trim_eol_space === false && func_num_args() === 0) {
            return static::$defaultTrimEolSpace;
        }

        if (!is_bool($trim_eol_space)) {
            throw new \InvalidArgumentException(sprintf('利用できない値を指定されました。trim_eol_space:%s', Convert::toDebugString($trim_eol_space, 2)));
        }

        static::$defaultTrimEolSpace    = $trim_eol_space;
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
                'rows'                  => $this->rows(),
                'tab_width'             => $this->tabWidth(),
                'indent_level'          => $this->indenteLevel(),
                'character_encodingg'   => $this->characterEncoding(),
                'trim_eol_space'        => $this->trimEolSpace(),
            );
        }

        if (isset($settings['header'])) {
            $this->header($settings['header']);
        }

        if (isset($settings['rows'])) {
            $this->rows($settings['rows']);
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

        if (isset($settings['trim_eol_space'])) {
            $this->trimEolSpace($settings['trim_eol_space']);
        }

        return $this;
    }

    /**
     * 事前ビルド状態を初期化します。
     *
     * @void
     */
    protected function initPreBuilding()
    {
        $this->preBuildeMaxWidthMap     = null;
        $this->preBuildeCellMaxWidthMap = null;
        $this->notNullColumnMap         = null;
    }

    /**
     * ビルダキャッシュ名を返します。
     *
     * @return  string  ビルダキャッシュ名
     */
    public function getCacheName()
    {
        return $this->cacheName;
    }

    /**
     * ビルダ名を返します。
     *
     * @return  string  ビルダ名
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * ビルダ名を設定します。
     *
     * @param   string  ビルダ名
     * @return  static  このインスタンス
     */
    protected function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @return  static|array|\Closure   このインスタンスまたはヘッダ
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
     * 行を設定・取得します。
     *
     * @param   array|\Closure|null $rows   行
     * @return  static|array|\Closure   このインスタンスまたは行
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
     * 空の状態かどうかを返します。
     *
     * @return  bool    空の状態かどうか
     */
    public function isEmpty()
    {
        return $this->isHeaderEmpty() && $this->isRowEmpty();
    }

    /**
     * ヘッダが空かどうかを返します。
     *
     * @return  bool    ヘッダが空かどうか
     */
    public function isHeaderEmpty()
    {
        return empty($this->header);
    }

    /**
     * 行が空かどうかを返します。
     *
     * @return  bool    行が空かどうか
     */
    public function isRowEmpty()
    {
        return empty($this->rows);
    }

    /**
     * タブ幅を設定・取得します。
     *
     * @param   int|string|null $tab_width  タブ幅
     * @return  static|int|string   このインスタンスまたはタブ幅
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
     * @return  static|int|string   このインスタンスまたはインデントレベル
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
     * @return  static|string|null  このインスタンスまたはエンコーディング
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

    /**
     * クラスデフォルトの行末スペーストリムを行うかどうかを設定・取得します。
     *
     * @param   bool|null   $trim_eol_space 行末スペーストリムを行うかどうか
     * @return  static|bool|null    このクラスパスまたは行末スペーストリムを行うかどうか
     */
    public function trimEolSpace($trim_eol_space = false)
    {
        if ($trim_eol_space === false && func_num_args() === 0) {
            return $this->trimEolSpace;
        }

        if (!is_bool($trim_eol_space)) {
            throw new \InvalidArgumentException(sprintf('利用できない値を指定されました。trim_eol_space:%s', Convert::toDebugString($trim_eol_space, 2)));
        }

        $this->trimEolSpace = $trim_eol_space;
        return $this;
    }

    /**
     * 列の全てがnullだった場合に列をスキップするかどうかを設定・取得します。
     *
     * @param   bool    $null_column_skip   列の全てがnullだった場合に列をスキップするかどうか
     * @return  static|bool このインスタンスまたは列の全てがnullだった場合に列をスキップするかどうか
     */
    public function nullColumnSkip($null_column_skip = false)
    {
        if ($null_column_skip === false && func_num_args() === 0) {
            return $this->nullColumnSkip;
        }

        $this->nullColumnSkip   = $null_column_skip;
        return $this;
    }

    //==============================================
    // supporter
    //==============================================
    /**
     * 文字列幅を取得します。
     *
     * @param   string  幅を取得したい文字列
     * @return  int     文字列幅
     */
    public function stringWidth($string)
    {
        $convert_charrcter_encoding = $this->characterEncoding !== 'UTF-8';

        if ($convert_charrcter_encoding) {
            $string = mb_convert_encoding($string, 'UTF-8', $this->characterEncoding);
        }

        $string_width = 0;
        for ($string_length = mb_strlen($string, 'UTF-8'), $i = 0;$i < $string_length;++$i) {
            $char   = mb_substr($string, $i, 1, 'UTF-8');

            if ($char !== mb_convert_encoding($char, 'UTF-8', 'UTF-8')) {
                $char_code  = 0xFFFD;
            } else {
                $ret    = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
                $char_code  = hexdec(bin2hex($ret));
            }

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
        $max_width_map      = array();
        $not_null_col_map   = array();

        foreach (array_values($this->header) as $idx => $node) {
            if ($node !== null) {
                $not_null_col_map[$idx] = $idx;
            }

            $max_width_map[$idx]    = $this->stringWidth($node);
        }

        $rows   = $this->rows;
        reset($rows);
        if (empty($rows)) {
            return $max_width_map;
        }

        if (empty($max_width_map)) {
            foreach (array_values(current($rows)) as $idx => $node) {
                if ($node !== null) {
                    $not_null_col_map[$idx] = $idx;
                }

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
                if ($node !== null) {
                    $not_null_col_map[$idx] = $idx;
                }

                $node_width = $this->stringWidth($node);
                $max_width_map[$idx] > $node_width ?: $max_width_map[$idx] = $node_width;
            }
        }

        $this->notNullColumnMap     = $not_null_col_map;
        $this->preBuildeMaxWidthMap = $max_width_map;

        return $max_width_map;
    }

    /**
     * インデントを加味したセル幅マップを構築し返します。
     *
     * @return  array   インデントを加味したセル幅マップ
     */
    public function buildCellWidthMap()
    {
        if ($this->preBuildeMaxWidthMap === null) {
            $this->buildMaxWidthMap();
        }
        $max_width_map  = $this->preBuildeMaxWidthMap;

        $base_indente   = 0;
        if (is_int($this->baseIndente)) {
            $base_indente   = $this->baseIndente;
        } elseif (is_string($this->baseIndente) && isset(static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente])) {
            $base_indente   = static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente];
        }

        $tab_width  = $this->tabWidth;
        $base_width = $base_indente + $this->indentLevel * $tab_width;

        $cell_max_width_map = array();

        if (is_array($max_width_map)) {
            foreach ($max_width_map as $idx => $cell_in_max_width) {
                $cell_max_width_map[$idx] = 0 === ($indente = ($base_width + $cell_in_max_width) % $tab_width) ? $cell_in_max_width + $tab_width : $cell_in_max_width + $tab_width - $indente;
            }
        }

        $this->preBuildeCellMaxWidthMap = $cell_max_width_map;

        return $cell_max_width_map;
    }

    /**
     * フィル用のリパート文字列を作成し返します。
     *
     * @param   string  $string 元の文字列
     * @param   int     $idx    列番号
     * @param   string  $repart リパート文字
     * @return  string  フィル用のリパート文字列
     */
    public function buildRepart($string, $idx, $repart = self::INDENTE_CHAR)
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
     * @return  array   ビルド後の文字配列スタック
     */
    public function build()
    {
        if ($this->preBuildeMaxWidthMap === null) {
            $this->buildMaxWidthMap();
        }

        $stack  = array();

        $base_indente   = 0;
        if (is_int($this->baseIndente)) {
            $base_indente   = str_repeat(static::INDENTE_CHAR, ($this->indenteLevel * $this->tabWidth) + $this->baseIndente);
        } elseif (is_string($this->baseIndente) && isset(static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente])) {
            $base_indente   = str_repeat(static::INDENTE_CHAR, static::$INDENTE_BASE_LENGTH_MAP[$this->baseIndente]);
        } else {
            $base_indente   = str_repeat(static::INDENTE_CHAR, $this->indenteLevel * $this->tabWidth);
        }

        foreach ($this->header as $idx => $cell) {
            $header = sprintf('%s%s', $cell, $this->buildRepart($cell, $idx));
            $stack[]    = sprintf('%s%s', $base_indente, $this->trimEolSpace ? rtrim($header, static::INDENTE_CHAR) : $header);
        }

        foreach ($this->rows as $row) {
            $message    = array();
            foreach (array_values($row) as $idx => $cell) {
                if ($this->nullColumnSkip && !isset($this->notNullColumnMap[$idx])) {
                    continue;
                }
                $message[]  = sprintf('%s%s', $cell, $this->buildRepart($cell, $idx));
            }
            $message    = implode('', $message);
            $stack[]    = sprintf('%s%s', $base_indente, $this->trimEolSpace ? rtrim($message, static::INDENTE_CHAR) : $message);
        }

        return $stack;
    }
}
