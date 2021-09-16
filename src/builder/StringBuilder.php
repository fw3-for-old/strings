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

namespace fw3_for_old\strings\builder;

use fw3_for_old\strings\builder\modifiers\ModifierInterface;
use fw3_for_old\strings\builder\modifiers\security\EscapeModifier;
use fw3_for_old\strings\builder\traits\converter\ConverterInterface;
use fw3_for_old\strings\converter\Convert;
use Closure;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * 変数展開と変数に対する修飾が可能な文字列ビルダーです。
 */
class StringBuilder
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
     * @const   string  変数部開始文字列のデフォルト値
     */
    const DEFAULT_ENCLOSURE_BEGIN       = '{:';

    /**
     * @const   string  変数部終了文字列のデフォルト値
     */
    const DEFAULT_ENCLOSURE_END         = '}';

    /**
     * @const   string  変数名セパレータのデフォルト値
     */
    const DEFAULT_NAME_SEPARATOR        = ':';

    /**
     * @const   string  修飾子セパレータのデフォルト値
     */
    const DEFAULT_MODIFIER_SEPARATOR    = '|';

    /**
     * @const   string  エスケープタイプ：HTML
     */
    const ESCAPE_TYPE_HTML          = Convert::ESCAPE_TYPE_HTML;

    /**
     * @const   string  エスケープタイプ：JavaScript
     */
    const ESCAPE_TYPE_JAVASCRIPT    = Convert::ESCAPE_TYPE_JAVASCRIPT;

    /**
     * @const   string  エスケープタイプ：JavaScript
     */
    const ESCAPE_TYPE_JS            = Convert::ESCAPE_TYPE_JS;

    /**
     * @const   string  エスケープタイプ：shell
     */
    const ESCAPE_TYPE_SHELL         = Convert::ESCAPE_TYPE_SHELL;

    /**
     * @const   array   修飾子セット
     */
    public static $DEFAULT_MODIFIER_SET = array(
        // datetime
        'date'          => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\DateModifier",
        'strtotime'     => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\StrtotimeModifier",
        // security
        'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        'e'             => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        // text
        'to_debug'          => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        'to_debug_str'      => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        'to_debug_string'   => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
    );

    /**
     * @const   array   スカラー値の文字列表現とスカラー値のマップ
     */
    private static $SCALAR_TEXT_MAP = array(
        'true'  => true,
        'false' => true,
        'TRUE'  => true,
        'FALSE' => true,
        'null'  => null,
        'NULL'  => null,
    );

    /**
     * @const   string  変数が存在しない場合の代替出力：空文字に置き換える
     */
    const SUBSTITUTE_EMPTY_STRING   = '';

    /**
     * @const   null    変数が存在しない場合の代替出力：変数名を出力する
     */
    const SUBSTITUTE_VAR_NAME       = null;

    /**
     * @const   ?string 変数が存在しない場合の代替出力のデフォルト値 空文字に置き換える
     */
    const DEFAULT_SUBSTITUTE        = self::SUBSTITUTE_EMPTY_STRING;

    //==============================================
    // static properties
    //==============================================
    /**
     * @var StringBuilderarray()   インスタンスキャッシュ
     */
    protected static $instanceCache  = array();

    /**
     * @var array   クラスデフォルトの変数値セット
     */
    protected static $defaultValues  = array();

    /**
     * @var ConverterInterface|Closure|string|null  クラスデフォルトのコンバータ
     */
    protected static $defaultConverter   = null;

    /**
     * @var string|null クラスデフォルトのエンコーディング
     */
    protected static $defaultCharacterEncoding   = self::DEFAULT_CHARACTER_ENCODING;

    /**
     * @var string  クラスデフォルトの変数部開始文字列
     */
    protected static $defaultEnclosureBegin      = self::DEFAULT_ENCLOSURE_BEGIN;

    /**
     * @var string  クラスデフォルトの変数部終了文字列
     */
    protected static $defaultEnclosureEnd        = self::DEFAULT_ENCLOSURE_END;

    /**
     * @var string  クラスデフォルトの変数名セパレータ
     */
    protected static $defaultNameSeparator       = self::DEFAULT_NAME_SEPARATOR;

    /**
     * @var string  クラスデフォルトの修飾子セパレータ
     */
    protected static $defaultModifierSeparator   = self::DEFAULT_MODIFIER_SEPARATOR;

    /**
     * @var array   クラスデフォルトの修飾子セット
     */
    protected static $defaultModifierSet  = array(
        // datetime
        'date'          => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\DateModifier",
        'strtotime'     => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\StrtotimeModifier",
        // security
        'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        'e'             => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        // text
        'to_debug'          => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        'to_debug_str'      => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        'to_debug_string'   => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
    );

    /**
     * @var ?string クラスデフォルトの変数が存在しない場合の代替出力。null:変数名をそのまま出力、string:指定した文字列を出力
     */
    protected static $defaultSubstitute  = self::DEFAULT_SUBSTITUTE;

    /**
     * @var bool    クラスデフォルトとしてエスケープするかどうか
     */
    protected static $defaultUseEscape  = false;

    /**
     * @var string  クラスデフォルトのエスケープタイプ
     */
    protected static $defaultEscapeType = Convert::ESCAPE_TYPE_HTML;

    /**
     * @var string  クラスデフォルトのメッセージ
     */
    protected static $defaultMessage    = 'メッセージが指定されていません。';

    /**
     * @var callable[]  クラスデフォルトのプレビルダ
     */
    protected static $defaultPreBuilder    = array();

    /**
     * @var callable[]  クラスデフォルトのポストビルダ
     */
    protected static $defaultPostBuilder   = array();

    //==============================================
    // properties
    //==============================================
    /**
     * @var string  文字列ビルダキャッシュ名
     */
    protected $cacheName    = null;

    /**
     * @var string  文字列ビルダ名
     */
    protected $name = null;

    /**
     * @var array   変数値セット
     */
    protected $values;

    /**
     * @var ConverterInterface|Closure|string|null  コンバータ
     */
    protected $converter;

    /**
     * @var string|null エンコーディング
     */
    protected $characterEncoding;

    /**
     * @var string  変数部開始文字列
     */
    protected $enclosureBegin = self::DEFAULT_ENCLOSURE_BEGIN;

    /**
     * @var int     変数部開始文字列長
     */
    protected $enclosureLengthBegin;

    /**
     * @var string  変数部終了文字列
     */
    protected $enclosureEnd = self::DEFAULT_ENCLOSURE_END;

    /**
     * @var int     変数部終了文字列長
     */
    protected $enclosureLengthEnd;

    /**
     * @var string  変数名セパレータ
     */
    protected $nameSeparator  = self::DEFAULT_NAME_SEPARATOR;

    /**
     * @var string  修飾子セパレータ
     */
    protected $modifierSeparator  = self::DEFAULT_MODIFIER_SEPARATOR;

    /**
     * @var array   修飾子セット
     */
    protected $modifierSet    = array(
        // datetime
        'date'          => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\DateModifier",
        'strtotime'     => "\\fw3_for_old\\strings\\builder\\modifiers\\datetime\\StrtotimeModifier",
        // security
        'escape'        => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        'e'             => "\\fw3_for_old\\strings\\builder\\modifiers\\security\\EscapeModifier",
        // text
        'to_debug'          => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        'to_debug_str'      => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
        'to_debug_string'   => "\\fw3_for_old\\strings\\builder\\modifiers\\strings\\ToDebugStringModifier",
    );

    /**
     * @var ?string 変数が存在しない場合の代替出力。null:変数名をそのまま出力、string:指定した文字列を出力
     */
    protected $substitute    = self::DEFAULT_SUBSTITUTE;

    /**
     * @var bool    デフォルトでエスケープするかどうか
     */
    protected $useEscape;

    /**
     * @var string  デフォルトのエスケープタイプ
     */
    protected $escapeType;

    /**
     * @var string  未指定時のメッセージ
     */
    protected $message  = '';

    /**
     * @var callable[]  プレビルダ
     */
    protected $preBuilder   = array();

    /**
     * @var callable[]  ポストビルダ
     */
    protected $postBuilder  = array();

    //==============================================
    // factory methods
    //==============================================
    /**
     * construct
     *
     * @param   string|null         $cache_name     文字列ビルダキャッシュ名
     * @param   array|object|null   $values         変数値セット
     * @param   array|null          $modifier_set   修飾子セット
     * @param   string|null         $encoding       エンコーディング
     */
    protected function __construct($cache_name, $values = null, $modifier_set = null, $encoding = null)
    {
        $this->cacheName    = $cache_name;

        $this->values(isset($values) ? $values : static::$defaultValues);

        $this->converter    = static::$defaultConverter;

        $this->characterEncoding    = isset($encoding) ? $encoding : (
            isset(static::$defaultCharacterEncoding) ? static::$defaultCharacterEncoding :mb_internal_encoding()
        );

        $this->enclosureBegin(static::$defaultEnclosureBegin);
        $this->enclosureEnd(static::$defaultEnclosureEnd);

        $this->nameSeparator(static::$defaultNameSeparator);

        $this->modifierSeparator(static::$defaultModifierSeparator);
        $this->modifierSet(isset($modifier_set) ? $modifier_set : static::$defaultModifierSet);

        $this->substitute(static::$defaultSubstitute);

        $this->useEscape(static::$defaultUseEscape);
        $this->escapeType(static::$defaultEscapeType);

        $this->message(static::$defaultMessage);

        $this->preBuilder(static::$defaultPreBuilder);
        $this->postBuilder(static::$defaultPostBuilder);
    }

    /**
     * factory
     *
     * @param   string|array        $name           文字列ビルダ名
     * @param   array|object|null   $values         変数値セット
     * @param   array|null          $modifier_set   修飾子セット
     * @param   string|null         $encoding       エンコーディング
     * @return  static  このインスタンス
     */
    public static function factory($name = self::DEFAULT_NAME, $values = null, $modifier_set = null, $encoding = null)
    {
        $cache_name = is_array($name) ? implode('::', $name) : $name;

        if (!isset(static::$instanceCache[$cache_name])) {
            static::$instanceCache[$cache_name] = new static($cache_name, $values, $modifier_set, $encoding);
            static::$instanceCache[$cache_name]->setName($name);
        }

        return static::$instanceCache[$cache_name];
    }

    /**
     * インスタンスをキャッシュしない使い捨てファクトリです。
     *
     * @param   array|object|null   $values         変数値セット
     * @param   array|null          $modifier_set   修飾子セット
     * @param   string|null         $encoding       エンコーディング
     * @return  static  このインスタンス
     */
    public static function disposableFactory($values = null, $modifier_set = null, $encoding = null)
    {
        return new static(null, $values, $modifier_set, $encoding);
    }

    //==============================================
    // static methods
    //==============================================
    /**
     * 指定されたビルダ名に紐づくビルダインスタンスを返します。
     *
     * @param   string|array    $name   ビルダ名
     * @return  static  このインスタンス
     */
    public static function get($name = self::DEFAULT_NAME)
    {
        $cache_name = is_array($name) ? implode('::', $name) : $name;

        if (!isset(static::$instanceCache[$cache_name])) {
            throw new OutOfBoundsException(sprintf('StringBuilderキャッシュに無いキーを指定されました。name:%s', Convert::toDebugString($name)));
        }

        return static::$instanceCache[$cache_name];
    }

    /**
     * 指定されたビルダ名に紐づくビルダキャッシュを削除します。
     *
     * @param   string|array    $name   ビルダ名
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
                'values'                => static::defaultValues(),
                'converter'             => static::defaultConverter(),
                'character_encodingg'   => static::defaultCharacterEncoding(),
                'enclosure_start'       => static::defaultEnclosureBegin(),
                'enclosure_end'         => static::defaultEnclosureEnd(),
                'name_separator'        => static::defaultNameSeparator(),
                'modifier_separator'    => static::defaultModifierSeparator(),
                'modifier_set'          => static::defaultModifierSet(),
                'substitute'            => static::defaultSubstitute(),
                'use_escape'            => static::defaultUseEscape(),
                'escape_type'           => static::defaultEscapeType(),
                'message'               => static::defaultMessage(),
                'pre_builder'           => static::defaultPreBuilder(),
                'post_builder'          => static::defaultPostBuilder(),
            );
        }

        if (isset($default_settings['values'])) {
            static::defaultValues($default_settings['values']);
        }

        if (isset($default_settings['converter'])) {
            static::defaultConverter($default_settings['converter']);
        }

        if (isset($default_settings['character_encodingg'])) {
            static::defaultCharacterEncoding($default_settings['character_encodingg']);
        }

        if (isset($default_settings['enclosure_start'])) {
            static::defaultEnclosureBegin($default_settings['enclosure_start']);
        }

        if (isset($default_settings['enclosure_end'])) {
            static::defaultEnclosureEnd($default_settings['enclosure_end']);
        }

        if (isset($default_settings['name_separator'])) {
            static::defaultNameSeparator($default_settings['name_separator']);
        }

        if (isset($default_settings['modifier_separator'])) {
            static::defaultModifierSeparator($default_settings['modifier_separator']);
        }

        if (isset($default_settings['modifier_set'])) {
            static::defaultModifierSet($default_settings['modifier_set']);
        }

        if (isset($default_settings['substitute'])) {
            static::defaultSubstitute($default_settings['substitute']);
        }

        if (isset($default_settings['use_escape'])) {
            static::defaultUseEscape($default_settings['use_escape']);
        }

        if (isset($default_settings['escape_type'])) {
            static::defaultEscapeType($default_settings['escape_type']);
        }

        if (isset($default_settings['message'])) {
            static::defaultMessage($default_settings['message']);
        }

        if (isset($default_settings['pre_builder'])) {
            static::defaultPreBuilder($default_settings['pre_builder']);
        }

        if (isset($default_settings['post_builder'])) {
            static::defaultPostBuilder($default_settings['post_builder']);
        }

        return get_called_class();
    }

    /**
     * クラスデフォルトの変数値セットを設定・取得します。
     *
     * @param   array|object|null   $values クラスデフォルトの変数値セット
     * @return  string|array        このクラスパスまたはクラスデフォルトの変数値セット
     */
    public static function defaultValues($values = null)
    {
        if ($values === null) {
            return static::$defaultValues;
        }

        if (is_array($values)) {
            static::$defaultValues = $values;
            return get_called_class();
        }

        static::$defaultValues = array();
        foreach ($values as $name => $value) {
            static::$defaultValues[$name] = $value;
        }

        return get_called_class();
    }

    /**
     * クラスデフォルトの変数値をセットします。
     *
     * @param   string          $name   変数名
     * @param   string|Closure  $value  変数値
     * @return  string          このクラスパス
     */
    public static function setDefaultValue($name, $value)
    {
        static::$defaultValues[$name] = $value;
        return get_called_class();
    }

    /**
     * クラスデフォルトの変数値を除去します。
     *
     * @param   string   $name   変数名
     * @return  string   このクラスパス
     */
    public static function removeDefaultValue($name)
    {
        unset(static::$defaultValues[$name]);
        return get_called_class();
    }

    /**
     * クラスデフォルトのコンバータを設定・取得します。
     *
     * @param   ConverterInterface|Closure|string|null  $converter  クラスデフォルトのコンバータ
     * @return  ConverterInterface|Closure|string|null  このクラスパスまたはクラスデフォルトのコンバータ
     */
    public static function defaultConverter($converter = null)
    {
        if ($converter === null && func_num_args() === 0) {
            return static::$defaultConverter;
        }

        static::$defaultConverter = $converter;
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
     * クラスデフォルトの変数部エンクロージャを設定・取得します。
     *
     * @param   string|null|array   $enclosure_begin    クラスデフォルトの変数部開始文字列
     * @param   string|null         $enclosure_end      クラスデフォルトの変数部終了文字列
     * @return  string|array        このクラスパスまたはクラスデフォルトの変数部エンクロージャ
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

        if (!\is_string($enclosure_begin)) {
            throw new InvalidArgumentException(sprintf('有効な変数部開始文字列を取得できませんでした。enclosure:%s', Convert::toDebugString($enclosure, 2)));
        }

        if (!\is_string($enclosure_end)) {
            throw new InvalidArgumentException(sprintf('有効な変数部終了文字列を取得できませんでした。enclosure:%s', Convert::toDebugString($enclosure, 2)));
        }

        static::defaultEnclosureBegin($enclosure_begin);
        static::defaultEnclosureEnd($enclosure_end);
        return get_called_class();
    }

    /**
     * クラスデフォルトの変数部開始文字列を設定・取得します。
     *
     * @param   string|null $enclosure_begin    クラスデフォルトの変数部開始文字列
     * @return  string      このクラスパスまたはクラスデフォルトの変数部開始文字列
     */
    public static function defaultEnclosureBegin($enclosure_begin = null)
    {
        if ($enclosure_begin === null) {
            return static::$defaultEnclosureBegin;
        }

        if ($enclosure_begin === static::$defaultNameSeparator)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部開始文字列にクラスデフォルトの変数名セパレータと同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        if ($enclosure_begin === static::$defaultModifierSeparator)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部開始文字列にクラスデフォルトの修飾子セパレータと同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        if ($enclosure_begin === static::$defaultSubstitute)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部開始文字列にクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        if ($enclosure_begin === static::$defaultEnclosureEnd)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部開始文字列にクラスデフォルトの変数部終了文字列と同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        static::$defaultEnclosureBegin = $enclosure_begin;
        return get_called_class();
    }

    /**
     * クラスデフォルトの変数部終了文字列を設定・取得します。
     *
     * @param   string|null $enclosure_end  クラスデフォルトの変数部終了文字列
     * @return  string      このクラスパスまたはクラスデフォルトの変数部終了文字列
     */
    public static function defaultEnclosureEnd($enclosure_end = null)
    {
        if ($enclosure_end === null) {
            return static::$defaultEnclosureEnd;
        }

        if ($enclosure_end === static::$defaultNameSeparator)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部終了文字列にクラスデフォルトの変数名セパレータと同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        if ($enclosure_end === static::$defaultModifierSeparator)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部終了文字列にクラスデフォルトの修飾子セパレータと同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        if ($enclosure_end === static::$defaultSubstitute)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部終了文字列にクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        if ($enclosure_end === static::$defaultEnclosureBegin)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数部終了文字列にクラスデフォルトの変数部開始文字列と同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        static::$defaultEnclosureEnd = $enclosure_end;
        return get_called_class();
    }

    /**
     * クラスデフォルトの変数名セパレータを設定・取得します。
     *
     * @param   string|null $name_separator クラスデフォルトの変数名セパレータ
     * @return  string      このクラスパスまたはクラスデフォルトの変数名セパレータ
     */
    public static function defaultNameSeparator($name_separator = null)
    {
        if ($name_separator === null) {
            return static::$defaultNameSeparator;
        }

        if ($name_separator === static::$defaultModifierSeparator)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの修飾子セパレータと同じ値を設定しようとしています。value:%s', $name_separator));
        }

        if ($name_separator === static::$defaultSubstitute)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $name_separator));
        }

        if ($name_separator === static::$defaultEnclosureBegin)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの変数部開始文字列と同じ値を設定しようとしています。value:%s', $name_separator));
        }

        if ($name_separator === static::$defaultEnclosureEnd)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの変数部終了文字列と同じ値を設定しようとしています。value:%s', $name_separator));
        }

        static::$defaultNameSeparator = $name_separator;
        return get_called_class();
    }

    /**
     * クラスデフォルトの修飾子セパレータを設定・取得します。
     *
     * @param   string|null $modifier_separator クラスデフォルトの修飾子セパレータ
     * @return  string      このクラスパスまたはクラスデフォルトの修飾子セパレータ
     */
    public static function defaultModifierSeparator($modifier_separator = null)
    {
        if ($modifier_separator === null) {
            return static::$defaultModifierSeparator;
        }

        if ($modifier_separator === static::$defaultNameSeparator)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの修飾子セパレータにクラスデフォルトの変数名セパレータと同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        if ($modifier_separator === static::$defaultSubstitute)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの修飾子セパレータにクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        if ($modifier_separator === static::$defaultEnclosureBegin)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの修飾子セパレータにクラスデフォルトの変数部開始文字列と同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        if ($modifier_separator === static::$defaultEnclosureEnd)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの修飾子セパレータにクラスデフォルトの変数部終了文字列と同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        static::$defaultModifierSeparator = $modifier_separator;
        return get_called_class();
    }

    /**
     * クラスデフォルトの修飾子セットを設定・取得します。
     *
     * @param   array|null      $modifier_set   クラスデフォルトの修飾子セット
     * @return  string|array    このクラスパスまたはクラスデフォルトの修飾子セット
     */
    public static function defaultModifierSet($modifier_set = null)
    {
        if ($modifier_set === null) {
            return static::$defaultModifierSet;
        }

        foreach ($modifier_set as $name => $modifier) {
            if (is_subclass_of($modifier, "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface")) {
                continue;
            }

            if ($modifier instanceof \Closure) {
                continue;
            }

            throw new InvalidArgumentException(sprintf('使用できない型の修飾子です。name:%s, modifier:%s', $name, Convert::toDebugString($modifier, 1)));
        }

        static::$defaultModifierSet = $modifier_set;
        return get_called_class();
    }

    /**
     * クラスデフォルトの修飾子をセットします。
     *
     * @param   string                              $name       修飾子名
     * @param   string|Closure|ModifierInterface    $modifier   修飾子
     * @return  string   このクラスパス
     */
    public static function setDefaultModifier($name, $modifier)
    {
        if (\is_string($modifier) && !is_subclass_of($modifier, "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface")) {
            throw new InvalidArgumentException(sprintf('使用できない型の修飾子です。name:%s, modifier:%s', $name, Convert::toDebugString($modifier, 1)));
        }

        static::$defaultModifierSet[$name] = $modifier;
        return get_called_class();
    }

    /**
     * クラスデフォルトの修飾子を除去します。
     *
     * @param   string   $name       修飾子名
     * @return  string   このクラスパス
     */
    public static function removeDefaultModifier($name)
    {
        unset(static::$defaultModifierSet[$name]);
        return get_called_class();
    }

    /**
     * クラスデフォルトの変数が存在しない場合の代替出力を設定・取得します。
     *
     * @param   string|null $substitute クラスデフォルトの変数が存在しない場合の代替出力 null:変数名をそのまま出力、string:指定した文字列を出力
     * @return  string      このクラスパスまたはクラスデフォルトの変数が存在しない場合の代替出力
     */
    public static function defaultSubstitute($substitute = null)
    {
        if ($substitute === null && func_num_args() === 0) {
            return static::$defaultSubstitute;
        }

        if ($substitute === static::$defaultModifierSeparator)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの修飾子セパレータと同じ値を設定しようとしています。value:%s', $substitute));
        }

        if ($substitute === static::$defaultSubstitute)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $substitute));
        }

        if ($substitute === static::$defaultEnclosureBegin)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの変数部開始文字列と同じ値を設定しようとしています。value:%s', $substitute));
        }

        if ($substitute === static::$defaultEnclosureEnd)  {
            throw new InvalidArgumentException(sprintf('クラスデフォルトの変数名セパレータにクラスデフォルトの変数部終了文字列と同じ値を設定しようとしています。value:%s', $substitute));
        }

        static::$defaultSubstitute = $substitute;
        return get_called_class();
    }

    /**
     * クラスデフォルトとしてエスケープするかどうかを設定・取得します。
     *
     * @param   bool    $use_escape クラスデフォルトとしてエスケープするかどうか
     * @return  string|bool このクラスパスまたはクラスデフォルトとしてエスケープするかどうか
     */
    public static function defaultUseEscape($use_escape = null)
    {
        if ($use_escape === null && func_num_args() === 0) {
            return static::$defaultUseEscape;
        }

        static::$defaultUseEscape  = $use_escape;
        return get_called_class();
    }

    /**
     * クラスデフォルトのエスケープタイプを設定・取得します。
     *
     * @param   string|null $escape_type   クラスデフォルトのエスケープタイプ
     * @return  string      このクラスパスまたはクラスデフォルトのエスケープタイプ
     */
    public static function defaultEscapeType($escape_type = null)
    {
        if ($escape_type === null && func_num_args() === 0) {
            return static::$defaultEscapeType;
        }

        if (!Convert::validEscapeType($escape_type)) {
            throw new \InvalidArgumentException(sprintf('利用できないエスケープタイプを指定されました。escape_type:%s', $escape_type));
        }

        static::$defaultEscapeType  = $escape_type;
        return get_called_class();
    }

    /**
     * クラスデフォルトのメッセージを設定・取得します。
     *
     * @param   string|null $message    クラスデフォルトのメッセージ
     * @return  string      このクラスパスまたはクラスデフォルトのメッセージ
     */
    public static function defaultMessage($message = null)
    {
        if ($message === null && func_num_args() === 0) {
            return static::$defaultMessage;
        }

        static::$defaultMessage = $message;
        return get_called_class();
    }

    /**
     * クラスデフォルトのプレビルダを設定・取得します。
     *
     * @param   callable|null   $pre_builder    クラスデフォルトのプレビルダ
     * @return  string|callable このクラスパスまたはクラスデフォルトのプレビルダ
     */
    public static function defaultPreBuilder($pre_builder = null)
    {
        if ($pre_builder === null && func_num_args() === 0) {
            return static::$defaultPreBuilder;
        }

        static::$defaultPreBuilder = $pre_builder;
        return get_called_class();
    }

    /**
     * クラスデフォルトのポストビルダを設定・取得します。
     *
     * @param   callable|null   $pre_builder    クラスデフォルトのポストビルダ
     * @return  string|callable このクラスパスまたはクラスデフォルトのポストビルダ
     */
    public static function defaultPostBuilder($post_builder = null)
    {
        if ($post_builder === null && func_num_args() === 0) {
            return static::$defaultPostBuilder;
        }

        static::$defaultPostBuilder = $post_builder;
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
                'values'                => $this->values(),
                'converter'             => $this->converter(),
                'character_encodingg'   => $this->characterEncoding(),
                'enclosure_start'       => $this->enclosureBegin(),
                'enclosure_end'         => $this->enclosureEnd(),
                'name_separator'        => $this->nameSeparator(),
                'modifier_separator'    => $this->modifierSeparator(),
                'modifier_set'          => $this->modifierSet(),
                'substitute'            => $this->substitute(),
                'use_escape'            => $this->useEscape(),
                'escape_type'           => $this->escapeType(),
                'message'               => $this->meessage(),
                'pre_builder'           => $this->preBuilder(),
                'post_builder'          => $this->postBuilder(),
            );
        }

        if (isset($settings['values'])) {
            $this->values($settings['values']);
        }

        if (isset($settings['converter'])) {
            $this->converter($settings['converter']);
        }

        if (isset($settings['character_encodingg'])) {
            $this->characterEncoding($settings['character_encodingg']);
        }

        if (isset($settings['enclosure_start'])) {
            $this->enclosureBegin($settings['enclosure_start']);
        }

        if (isset($settings['enclosure_end'])) {
            $this->enclosureEnd($settings['enclosure_end']);
        }

        if (isset($settings['name_separator'])) {
            $this->nameSeparator($settings['name_separator']);
        }

        if (isset($settings['modifier_separator'])) {
            $this->modifierSeparator($settings['modifier_separator']);
        }

        if (isset($settings['modifier_set'])) {
            $this->modifierSet($settings['modifier_set']);
        }

        if (isset($settings['substitute'])) {
            $this->substitute($settings['substitute']);
        }

        if (isset($settings['use_escape'])) {
            $this->useEscape($settings['use_escape']);
        }

        if (isset($settings['escape_type'])) {
            $this->escapeType($settings['escape_type']);
        }

        if (isset($settings['message'])) {
            $this->message($settings['message']);
        }

        if (isset($settings['pre_builder'])) {
            $this->preBuilder($settings['pre_builder']);
        }

        if (isset($settings['post_builder'])) {
            $this->postBuilder($settings['post_builder']);
        }

        return $this;
    }

    /**
     * 文字列ビルダキャッシュ名を返します。
     *
     * @return  string|null 文字列ビルダキャッシュ名
     */
    public function getCacheName()
    {
        return $this->cacheName;
    }

    /**
     * 文字列ビルダ名を設定します。
     *
     * @return  string  文字列ビルダ名
     */
    protected function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 文字列ビルダ名を返します。
     *
     * @return  string|null 文字列ビルダ名
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 変数値セットを設定・取得します。
     *
     * @param   array|object|null   $values 変数値セット
     * @return  static|array        このインスタンスまたは変数値セット
     */
    public function values($values = null)
    {
        if ($values === null) {
            return $this->values;
        }

        if (is_array($values)) {
            $this->values = $values;
            return $this;
        }

        $this->values = array();
        foreach ($values as $name => $value) {
            $this->values[$name] = $value;
        }

        return $this;
    }

    /**
     * 変数値をセットします。
     *
     * @param   string          $name   変数名
     * @param   string|Closure  $value  変数値
     * @return  static          このインスタンス
     */
    public function setValue($name, $value)
    {
        $this->values[$name] = $value;
        return $this;
    }

    /**
     * 変数値を除去します。
     *
     * @param   string   $name   変数名
     * @return  static   このインスタンス
     */
    public function removeValue($name)
    {
        unset($this->values[$name]);
        return $this;
    }

    /**
     * コンバータを設定・取得します。
     *
     * @param   ConverterInterface|Closure|string|null  $converter  コンバータ
     * @return  static|ConverterInterface|Closure|string|null   このインスタンスまたはコンバータ
     */
    public function converter($converter = null)
    {
        if ($converter === null && func_num_args() === 0) {
            return $this->converter;
        }

        $this->converter = $converter;
        return $this;
    }

    /**
     * エンコーディングを設定・取得します。
     *
     * @param   string|null         $character_encoding エンコーディング
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
            throw new InvalidArgumentException(sprintf('現在のシステムで利用できないエンコーディングを指定されました。character_encoding:%s', $character_encoding));
        }

        $this->characterEncoding = $character_encoding;
        return $this;
    }

    /**
     * 変数部エンクロージャを設定・取得します。
     *
     * @param   string|null|array   $enclosure_begin    変数部開始文字列
     * @param   string|null         $enclosure_end      変数部終了文字列
     * @return  static|array        このインスタンスまたは変数部エンクロージャ
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

        if (!\is_string($enclosure_begin)) {
            throw new InvalidArgumentException(sprintf('有効な変数部開始文字列を取得できませんでした。enclosure:%s', Convert::toDebugString($enclosure, 2)));
        }


        if (!\is_string($enclosure_end)) {
            throw new InvalidArgumentException(sprintf('有効な変数部終了文字列を取得できませんでした。enclosure:%s', Convert::toDebugString($enclosure, 2)));
        }

        $this->enclosureBegin($enclosure_begin);
        $this->enclosureEnd($enclosure_end);

        return $this;
    }

    /**
     * 変数部開始文字列を設定・取得します。
     *
     * @param   string|null     $enclosure_begin    変数部開始文字列
     * @return  static|string   このインスタンスまたは変数部開始文字列
     */
    public function enclosureBegin($enclosure_begin = null)
    {
        if ($enclosure_begin === null) {
            return $this->enclosureBegin;
        }

        if ($enclosure_begin === $this->nameSeparator)  {
            throw new InvalidArgumentException(sprintf('変数部開始文字列に変数名セパレータと同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        if ($enclosure_begin === $this->modifierSeparator)  {
            throw new InvalidArgumentException(sprintf('変数部開始文字列に修飾子セパレータと同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        if ($enclosure_begin === $this->substitute)  {
            throw new InvalidArgumentException(sprintf('変数部開始文字列に変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        if ($enclosure_begin === $this->enclosureEnd)  {
            throw new InvalidArgumentException(sprintf('変数部開始文字列に変数部終了文字列と同じ値を設定しようとしています。value:%s', $enclosure_begin));
        }

        $this->enclosureBegin       = $enclosure_begin;
        $this->enclosureLengthBegin = mb_strlen($this->enclosureBegin, $this->characterEncoding);
        return $this;
    }

    /**
     * 変数部終了文字列を設定・取得します。
     *
     * @param   string|null     $enclosure_end  変数部終了文字列
     * @return  static|string   このインスタンスまたは変数部終了文字列
     */
    public function enclosureEnd($enclosure_end = null)
    {
        if ($enclosure_end === null) {
            return $this->enclosureEnd;
        }

        if ($enclosure_end === $this->nameSeparator)  {
            throw new InvalidArgumentException(sprintf('変数部終了文字列に変数名セパレータと同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        if ($enclosure_end === $this->modifierSeparator)  {
            throw new InvalidArgumentException(sprintf('変数部終了文字列に修飾子セパレータと同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        if ($enclosure_end === $this->substitute)  {
            throw new InvalidArgumentException(sprintf('変数部終了文字列に変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        if ($enclosure_end === $this->enclosureBegin)  {
            throw new InvalidArgumentException(sprintf('変数部終了文字列に変数部開始文字列と同じ値を設定しようとしています。value:%s', $enclosure_end));
        }

        $this->enclosureEnd         = $enclosure_end;
        $this->enclosureLengthEnd   = mb_strlen($this->enclosureEnd, $this->characterEncoding);
        return $this;
    }

    /**
     * 変数名セパレータを設定・取得します。
     *
     * @param   string|null     $name_separator 変数名セパレータ
     * @return  static|string   このインスタンスまたは変数名セパレータ
     */
    public function nameSeparator($name_separator = null)
    {
        if ($name_separator === null) {
            return $this->nameSeparator;
        }

        if ($name_separator === $this->modifierSeparator)  {
            throw new InvalidArgumentException(sprintf('変数名セパレータに修飾子セパレータと同じ値を設定しようとしています。value:%s', $name_separator));
        }

        if ($name_separator === $this->substitute)  {
            throw new InvalidArgumentException(sprintf('変数名セパレータに変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $name_separator));
        }

        if ($name_separator === $this->enclosureBegin)  {
            throw new InvalidArgumentException(sprintf('変数名セパレータに変数部開始文字列と同じ値を設定しようとしています。value:%s', $name_separator));
        }

        if ($name_separator === $this->enclosureEnd)  {
            throw new InvalidArgumentException(sprintf('変数名セパレータに変数部終了文字列と同じ値を設定しようとしています。value:%s', $name_separator));
        }

        $this->nameSeparator = $name_separator;
        return $this;
    }

    /**
     * 修飾子セパレータを設定・取得します。
     *
     * @param   string|null     $modifier_separator 修飾子セパレータ
     * @return  static|string   このインスタンスまたは修飾子セパレータ
     */
    public function modifierSeparator($modifier_separator = null)
    {
        if ($modifier_separator === null) {
            return $this->modifierSeparator;
        }

        if ($modifier_separator === $this->nameSeparator)  {
            throw new InvalidArgumentException(sprintf('修飾子セパレータに変数名セパレータと同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        if ($modifier_separator === $this->substitute)  {
            throw new InvalidArgumentException(sprintf('修飾子セパレータに変数が存在しない場合の代替出力と同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        if ($modifier_separator === $this->enclosureBegin)  {
            throw new InvalidArgumentException(sprintf('修飾子セパレータに変数部開始文字列と同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        if ($modifier_separator === $this->enclosureEnd)  {
            throw new InvalidArgumentException(sprintf('修飾子セパレータに変数部終了文字列と同じ値を設定しようとしています。value:%s', $modifier_separator));
        }

        $this->modifierSeparator = $modifier_separator;
        return $this;
    }

    /**
     * 修飾子セットを設定・取得します。
     *
     * @param   array|null      $modifier_set   修飾子セット
     * @return  static|array    このインスタンスまたは修飾子セット
     */
    public function modifierSet($modifier_set = null)
    {
        if ($modifier_set === null) {
            return $this->modifierSet;
        }

        foreach ($modifier_set as $name => $modifier) {
            if (is_subclass_of($modifier, "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface")) {
                continue;
            }

            if ($modifier instanceof \Closure) {
                continue;
            }

            throw new InvalidArgumentException(sprintf('使用できない型の修飾子です。name:%s, modifier:%s', $name, Convert::toDebugString($modifier, 1)));
        }

        $this->modifierSet = $modifier_set;
        return $this;
    }

    /**
     * 修飾子をセットします。
     *
     * @param   string                              $name       修飾子名
     * @param   string|Closure|ModifierInterface    $modifier   修飾子
     * @return  static   このインスタンス
     */
    public function setModifier($name, $modifier)
    {
        if (\is_string($modifier) && !is_subclass_of($modifier, "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface")) {
            throw new InvalidArgumentException(sprintf('使用できない型の修飾子です。name:%s, modifier:%s', $name, Convert::toDebugString($modifier, 1)));
        }

        if (is_object($modifier)) {
            if (!($modifier instanceof \Closure) && !is_subclass_of($modifier, "\\fw3_for_old\\strings\\builder\\modifiers\\ModifierInterface")) {
                throw new InvalidArgumentException(sprintf('使用できない型の修飾子です。name:%s, modifier:%s', $name, Convert::toDebugString($modifier, 1)));
            }
        }

        $this->modifierSet[$name] = $modifier;
        return $this;
    }

    /**
     * 修飾子を除去します。
     *
     * @param   string   $name       修飾子名
     * @return  static   このインスタンス
     */
    public function removeModifier($name)
    {
        unset($this->modifierSet[$name]);
        return $this;
    }

    /**
     * 変数が存在しない場合の代替出力を設定・取得します。
     *
     * @param   string|null         $substitute 変数が存在しない場合の代替出力 null:変数名をそのまま出力、string:指定した文字列を出力
     * @return  static|string|null  このインスタンスまたは変数が存在しない場合の代替出力
     */
    public function substitute($substitute = null)
    {
        if ($substitute === null && func_num_args() === 0) {
            return $this->substitute;
        }

        $this->substitute = $substitute;
        return $this;
    }

    /**
     * デフォルトとしてエスケープするかどうかを設定・取得します。
     *
     * @param   bool    $use_escape デフォルトとしてエスケープするかどうか
     * @return  static|bool このインスタンスまたはデフォルトとしてエスケープするかどうか
     */
    public function useEscape($use_escape = false)
    {
        if ($use_escape === false && func_num_args() === 0) {
            return $this->useEscape;
        }

        $this->useEscape    = $use_escape;
        return $this;
    }

    /**
     * デフォルトのエスケープタイプを設定・取得します。
     *
     * @param   string  $escape_type    デフォルトのエスケープタイプ
     * @return  static|string   このインスタンスまたはデフォルトのエスケープタイプ
     */
    public function escapeType($escape_type = null)
    {
        if ($escape_type === null && func_num_args() === 0) {
            return $this->escapeType;
        }

        if (!Convert::validEscapeType($escape_type)) {
            throw new \InvalidArgumentException(sprintf('利用できないエスケープタイプを指定されました。escape_type:%s', $escape_type));
        }

        $this->escapeType   = $escape_type;
        return $this;
    }

    /**
     * デフォルトのメッセージを設定・取得します。
     *
     * @param   string  $message    デフォルトのメッセージ
     * @return  static|string   このインスタンスまたはデフォルトのメッセージ
     */
    public function message($message = null)
    {
        if ($message === null && func_num_args() === 0) {
            return $this->message;
        }

        $this->message  = $message;
        return $this;
    }

    /**
     * プレビルダを設定・取得します。
     *
     * @param   callable|null   $pre_builder    プレビルダ
     * @return  static|callable このクラスパスまたはプレビルダ
     */
    public function preBuilder($pre_builder = null)
    {
        if ($pre_builder === null && func_num_args() === 0) {
            return $this->preBuilder;
        }

        $this->preBuilder   = $pre_builder;
        return $this;
    }

    /**
     * ポストビルダを設定・取得します。
     *
     * @param   callable|null   $pre_builder    ポストビルダ
     * @return  static|callable このクラスパスまたはポストビルダ
     */
    public function postBuilder($post_builder = null)
    {
        if ($post_builder === null && func_num_args() === 0) {
            return $this->postBuilder;
        }

        $this->postBuilder  = $post_builder;
        return $this;
    }

    //==============================================
    // supporter
    //==============================================
    /**
     * shell用ポストビルダー
     *
     * @param   string  $message    メッセージ
     * @param   array   $values     値
     * @param   array   $converter  コンバータ
     * @param   StringBuilder   $stringBuilder  ストリングビルダインスタンス
     * @return  string  メッセージ
     */
    public static function postBuilderForShell($message, $values, $converter, $stringBuilder)
    {
        return escapeshellcmd($message);
    }

    //==============================================
    // modifier
    //==============================================
    /**
     * 文字列を修飾して返します。
     *
     * @param   string  $replace        修飾する文字列
     * @param   array   $modifier_list  適用する修飾処理リスト
     * @return  string  修飾済みの文字列
     */
    public function modify($replace, array $modifier_list)
    {
        $context    = array(
            'encoding'  => $this->characterEncoding,
        );

        $use_raw    = false;
        foreach ($modifier_list as $modifier_name => $parameters) {
            $modifier   = isset($this->modifierSet[$modifier_name]) ? $this->modifierSet[$modifier_name] : null;

            if (\is_string($modifier)) {
                if ($modifier === 'raw') {
                    $use_raw    = true;
                    continue;
                }

                $replace    = $modifier::modify($replace, $parameters, $context);
            } elseif (is_object($modifier)) {
                $replace    = $modifier($replace, $parameters, $context);
            }
        }

        if (!$use_raw && $this->useEscape) {
            $replace    = EscapeModifier::modify($replace, array('type' => $this->escapeType), $context);
        }

        return $replace;
    }

    //==============================================
    // builder
    //==============================================
    /**
     * ビルドします。
     *
     * @param   array|object                            $values     変数
     * @param   Closure|ConverterInterface|string|null  $converter  コンバータ
     * @return  string  ビルド後のメッセージ
     */
    public function build($values = array(), $converter = null)
    {
        return $this->buildMessage($this->message, $values, $converter);
    }

    /**
     * メッセージを指定してビルドします。
     *
     * @param   string|null                             $message    ビルドするメッセージ
     * @param   array|object                            $values     変数
     * @param   Closure|ConverterInterface|string|null  $converter  コンバータ
     * @return  string  ビルド後のメッセージ
     */
    public function buildMessage($message, $values = array(), $converter = null)
    {
        if (!empty($this->preBuilder)) {
            $pre_builders   = $this->preBuilder;

            if (is_callable($pre_builders)) {
                $pre_builders   = array($pre_builders);
            }

            foreach ($pre_builders as $pre_builder) {
                $message    = $pre_builder($message, $values, $converter, $this);
            }
        }

        $converter              = isset($converter) ? $converter : $this->converter;
        $enable_converter       = $converter instanceof Closure || \is_subclass_of($converter, "\\fw3_for_old\\strings\\builder\\traits\\converter\\ConverterInterface");
        $is_invokable_converter = $enable_converter && is_object($converter);

        $modifier_separator_length  = \mb_strlen($this->modifierSeparator, $this->characterEncoding);

        $tmp_values = $this->values;
        foreach ($values as $name => $value) {
            $tmp_values[$name]  = $value;
        }
        $values = $tmp_values;

        $pos            = 0;
        $name           = '';
        $before_message = '';
        $before_pos     = 0;

        $begin  = 0;

        for (;false !== ($begin = \mb_strrpos($message, $this->enclosureBegin, $pos, $this->characterEncoding)) && false !== ($end = \mb_strpos($message, $this->enclosureEnd, $begin, $this->characterEncoding));) {
            if ($before_message === $message) {
                $message_length = \mb_strlen($message, $this->characterEncoding);
                $pos    = $begin - $message_length - 1;

                if ($pos + $message_length < 0) {
                    $pos = -$message_length;
                }

                if ($before_pos === $pos) {
                    break;
                }

                $before_pos = $pos;

                $before_message = '';
                continue;
            }
            $before_pos = $pos;

            $name_begin  = $begin + $this->enclosureLengthBegin;
            $name_end    = $end - $begin - $this->enclosureLengthBegin;

            $tag_begin  = $begin;
            $tag_end    = $end - $begin + $this->enclosureLengthEnd;

            $name   = \mb_substr($message, $name_begin, $name_end, $this->characterEncoding);
            $search = \mb_substr($message, $tag_begin, $tag_end, $this->characterEncoding);

            if (false !== \mb_strpos($name, $this->enclosureBegin, 0, $this->characterEncoding)) {
                $before_message = $message;
                $name       = $this->buildMessage($name, $values, $converter);
                $message    = \str_replace($search, $name, $message);
                continue;
            }

            $modifier_list    = array();
            if (false !== ($modifier_begin = \mb_strpos($name, $this->modifierSeparator, 0, $this->characterEncoding))) {
                $modifier_name          = null;
                $modifier_in_ellipsis   = false;
                $modifier_in_array      = false;
                $modifier_stack         = array();
                $modifier_parameter_name    = null;

                foreach (\token_get_all('<?php ' . \mb_substr($name, $modifier_begin + $modifier_separator_length)) as $token) {
                    if (\is_string($token)) {
                        $token_id   = $token;
                        $token_text = $token;
                    } else {
                        $token_id   = $token[0];
                        $token_text = $token[1];
                    }

                    if ($token_id === T_OPEN_TAG) {
                        continue;
                    }

                    if (!$modifier_in_ellipsis) {
                        if ($token_id === '(') {
                            $modifier_in_ellipsis = true;
                            continue;
                        }

                        if ($token_id === T_STRING) {
                            $modifier_list[$modifier_name = $token_text] = array();
                            continue;
                        }

                        continue;
                    }

                    if (!$modifier_in_array) {
                        if ($token_id === '[') {
                            $modifier_in_array    = true;
                            $modifier_stack       = array();
                            continue;
                        }
                    }

                    if ($token_id === ']') {
                        $modifier_in_array = false;
                        if ($modifier_parameter_name !== null) {
                            $modifier_list[$modifier_name][$modifier_parameter_name]  = $modifier_stack;
                        } else {
                            $modifier_list[$modifier_name][]    = $modifier_stack;
                        }
                        $modifier_stack           = array();
                        $modifier_parameter_name  = null;
                        continue;
                    }

                    if ($token_id === ')') {
                        if (!empty($modifier_stack)) {
                            if ($modifier_parameter_name !== null) {
                                $modifier_list[$modifier_name][$modifier_parameter_name]  = $modifier_stack;
                            } else {
                                $modifier_list[$modifier_name][]    = $modifier_stack;
                            }
                        }
                        $modifier_stack           = array();
                        $modifier_in_ellipsis     = false;
                        $modifier_parameter_name  = null;
                        continue;
                    }

                    if ($token_id === ',') {
                        if (!empty($modifier_stack)) {
                            if ($modifier_parameter_name !== null) {
                                $modifier_list[$modifier_name][$modifier_parameter_name]  = $modifier_stack;
                            } else {
                                $modifier_list[$modifier_name][]    = $modifier_stack;
                            }
                        }
                        $modifier_parameter_name  = null;
                        $modifier_stack           = array();
                        continue;
                    }

                    if ($token_id === T_WHITESPACE) {
                        continue;
                    }

                    if ($token_id === '-') {
                        $modifier_stack[] = '-';
                        continue;
                    }

                    if ($token_id === T_STRING) {
                        if (isset(self::$SCALAR_TEXT_MAP[$token_text])) {
                            $modifier_stack   = self::$SCALAR_TEXT_MAP[$token_text];
                        } else {
                            $modifier_parameter_name  = $token_text;
                        }
                        continue;
                    }

                    if ($token_id === T_LNUMBER) {
                        $token_text = (int) $token_text;
                        $modifier_stack   = \end($modifier_stack) === '-' ? -1 * $token_text : $token_text;
                        continue;
                    }

                    if ($token_id === T_DNUMBER) {
                        $token_text = (float) $token_text;
                        $modifier_stack   = \end($modifier_stack) === '-' ? -1.0 * $token_text : $token_text;
                        continue;
                    }

                    if ($token_id === T_CONSTANT_ENCAPSED_STRING) {
                        $modifier_stack   = \mb_substr($token_text, 1, -1, $this->characterEncoding);
                        continue;
                    }

                    if ($token_id === T_NAME_FULLY_QUALIFIED) {
                        $modifier_stack[] = $token_text;
                        continue;
                    }

                    if ($token_id === T_DOUBLE_COLON) {
                        $modifier_stack[] = $token_text;
                        continue;
                    }

                    if ($token_id === T_CLASS) {
                        $modifier_stack[] = $token_text;
                        $modifier_stack   = \implode('', $modifier_stack);
                        continue;
                    }
                }

                $name    = \mb_substr($name, 0, $modifier_begin, $this->characterEncoding);
            }

            $names      = false === \mb_strpos($name, $this->nameSeparator, 0, $this->characterEncoding) ? (array) $name : \explode($this->nameSeparator, $name);
            $replace    = null;

            foreach ($names as $name) {
                if ($enable_converter) {
                    $replace = $is_invokable_converter ? $converter($name, $search, $values) : $converter::convert($name, $search, $values);

                    if ($replace === null && isset($values[$name]) || \array_key_exists($name, $values)) {
                        $replace    = $values[$name];
                    }

                    if (!empty($modifier_list)) {
                        if (\is_string($replace) && false !== ($replace_begin = \mb_strrpos($replace, $this->enclosureBegin, 0, $this->characterEncoding)) && false !== \mb_strpos($replace, $this->enclosureEnd, $replace_begin, $this->characterEncoding)) {
                            $replace    = $this->buildMessage($replace, $values, $converter);
                        }
                        $replace = $this->modify($replace, $modifier_list);
                    }

                    if (\is_string($replace)) {
                        $before_message = $message;
                        $message = \str_replace($search, $replace, $message);
                        continue 2;
                    }
                }

                if (isset($values[$name]) || \array_key_exists($name, $values)) {
                    $replace    = $values[$name];
                    break;
                }
            }

            $replace = isset($replace) ? $replace : (
                isset($this->substitute) ? $this->substitute : $search
            );

            if ($replace instanceof Closure) {
                $replace = $replace($name, $search, $values, $replace);
            }

            if (!empty($modifier_list)) {
                if (\is_string($replace) && false !== ($replace_begin = \mb_strrpos($replace, $this->enclosureBegin, 0, $this->characterEncoding)) && false !== \mb_strpos($replace, $this->enclosureEnd, $replace_begin, $this->characterEncoding)) {
                    $replace    = $this->buildMessage($replace, $values, $converter);
                }
                $replace = $this->modify($replace, $modifier_list);
            }

            $before_message = $message;
            $message = \str_replace($search, $replace, $message);
        }

        if (!empty($this->postBuilder)) {
            $post_builders  = $this->postBuilder;

            if (\is_callable($post_builders)) {
                $post_builders  = array($post_builders);
            }

            foreach ($post_builders as $post_builder) {
                $message    = $post_builder($message, $values, $converter, $this);
            }
        }

        return $message;
    }
}
