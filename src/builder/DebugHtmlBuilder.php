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

use fw3_for_old\strings\converter\Convert;
use fw3_for_old\strings\html\Html;
use fw3_for_old\strings\html\HtmlElement;
use ReflectionObject;

/**
 * 変数デバッグ用HTML構築ビルダーです。
 */
class DebugHtmlBuilder
{
    /**
     * @var int     デフォルトの階層構造展開深さ
     */
    const DEFAULT_DEPTH                 = 10;

    /**
     * @var int     デフォルトのデフォルトで開いて表示してする階層構図の深さ
     */
    const DEFAULT_DEFAULT_OPEN_DEPTH    = 1;

    /**
     * @var bool    CSSを既に表示したかどうか
     */
    protected static $displayedCss  = false;

    /**
     * @var array   表示対象変数
     */
    protected $vars;

    /**
     * @var array   ファイルパスの先頭からオミットすべき文字列
     */
    protected $omitFilePathPrefix   = array();

    /**
     * @var null|HtmlElement    親となるノード
     */
    protected $baseNode = null;

    /**
     * @var int     バックトレースの開始深さ
     */
    protected $startBacktraceDepth      = 0;

    /**
     * @var bool    デフォルトCSSを使用しないかどうか
     */
    protected $disalbedDefaultCss   = false;

    /**
     * @var string  インラインとして展開するCSS
     */
    protected $inlineCss    = null;

    /**
     * @var string|array    追加で読み込むCSS URL
     */
    protected $cssUrl       = null;

    /**
     * @var bool    オブジェクトの詳細を出力するかどうか
     */
    protected $objectDetail = true;

    /**
     * @var array   読み込み済みのオブジェクト情報
     */
    protected $loadedObjects    = array();

    /**
     * @var int     ビルダのコールスタック深さ
     */
    protected $depth            = self::DEFAULT_DEPTH;

    /**
     * @var int     デフォルトで開いて表示してする階層構図の深さ
     */
    protected $defaultOpenDepth = self::DEFAULT_DEFAULT_OPEN_DEPTH;

    /**
     * @var array   インスタンス構築時のバックトレーススタック
     */
    protected $backTraceList    = array();

    /**
     * ファクトリ
     *
     * @param   mixed   $var    デバッグ情報として表示したい変数
     * @return  static  このインスタンス
     */
    public static function factory($var)
    {
        if (\func_num_args() === 1) {
            $instance   = new static($var);
            $instance->setStartBacktraceDepth(1);
        } else {
            $rc         = new \ReflectionClass(\get_called_class());
            $instance   = $rc->newInstanceArgs(\func_get_args());
            $instance->setStartBacktraceDepth(2);
        }

        return $instance;
    }

    /**
     * コンストラクタ
     *
     * @param   mixed   $var    デバッグ情報として表示したい変数
     */
    public function __construct($var)
    {
        $this->vars             = \func_get_args();
        $this->backTraceList    = \debug_backtrace();
    }

    /**
     * ファイルパスの先頭からオミットすべき文字列を設定します。
     *
     * @param   array|string    $omit_file_path_prefix  ファイルパスの先頭からオミットすべき文字列
     * @return  static  このインスタンス
     */
    public function setOmitFilePathPrefix($omit_file_path_prefix)
    {
        $this->omitFilePathPrefix   = $omit_file_path_prefix;

        return $this;
    }

    /**
     * 基底とするHTMLElementを設定します。
     *
     * @param   HtmlElement $htmlElement    基底とするHTMLElement
     * @return  static  このインスタンス
     */
    public function setBaseNode($htmlElement)
    {
        $this->baseNode = $htmlElement;

        return $this;
    }

    /**
     * バックトレースの開始深さを設定します。
     *
     * @param   int     $start_backtrace_depth  バックトレースの開始深さ
     * @return  static  このインスタンス
     */
    public function setStartBacktraceDepth($start_backtrace_depth)
    {
        $this->startBacktraceDepth  = $start_backtrace_depth;

        return $this;
    }

    /**
     * バックトレースの開始深さを一段浅くします。
     *
     * @return  static  このインスタンス
     */
    public function incrementStartBacktraceDepth()
    {
        ++$this->startBacktraceDepth;

        return $this;
    }

    /**
     * デフォルトCSSを使用しないかどうかを設定ます。
     *
     * @param   bool    $disalbed_default_css   デフォルトCSSを使用しないかどうか
     * @return  static  このインスタンス
     */
    public function setDisalbedDefaultCss($disalbed_default_css)
    {
        $this->disalbedDefaultCss = $disalbed_default_css;

        return $this;
    }

    /**
     * インラインとして展開するCSSを設定します。
     *
     * @param   string  $inline_css インラインとして展開するCSS
     * @return  static  このインスタンス
     */
    public function setInlineCss($inline_css)
    {
        $this->inlineCss = $inline_css;

        return $this;
    }

    /**
     * 追加で読み込むCSS URLを設定します。
     *
     * @param   string|array    $css_url    追加で読み込むCSS URL
     * @return  static  このインスタンス
     */

    public function setCssUrl($css_url)
    {
        $this->cssUrl   = $css_url;

        return $this;
    }

    /**
     * オブジェクトの詳細を出力するかどうかを設定します。
     *
     * @param   bool    $object_detail  オブジェクトの詳細を出力するかどうか
     * @return  static  このインスタンス
     */
    public function setObjectDetail($object_detail)
    {
        $this->objectDetail = $object_detail;

        return $this;
    }

    /**
     * ビルダのコールスタック深さを設定します。
     *
     * @param   int     $depth  ビルダのコールスタック深さ
     * @return  static  このインスタンス
     */
    public function setDepth($depth)
    {
        $this->depth    = $depth;

        return $this;
    }

    /**
     * デフォルトで開いて表示してする階層構図の深さを設定します。
     *
     * @param   int     $default_open_depth デフォルトで開いて表示してする階層構図の深さ
     * @return  static  このインスタンス
     */
    public function setDefaultOpenDepth($default_open_depth)
    {
        $this->defaultOpenDepth = $default_open_depth;

        return $this;
    }

    /**
     * 現在の内容でHTMLを構築し返します。
     *
     * @return  string  現在の内容でのHTML
     */
    public function toHtml()
    {
        return $this->build()->toHtml();
    }

    /**
     * 現在の内容でHTMLを構築し表示します。
     *
     * @return  void
     */
    public function display()
    {
        echo $this->build()->toHtml();
    }

    /**
     * 変数からHtmlElementを構築します。
     *
     * @return  HtmlElement HtmlElement
     */
    public function build()
    {
        /** @var HtmlElement $baseNode */
        if ($this->baseNode instanceof HtmlElement) {
            $baseNode   = clone $this->baseNode;
            $baseNode->cssClass('debug-html');
        } else {
            $baseNode   = Html::div()->cssClass('debug-html');
        }

        if (!static::$displayedCss) {
            static::$displayedCss   = true;

            if (!$this->disalbedDefaultCss) {
                $baseNode->appendNode(Html::style(\file_get_contents(\implode(\DIRECTORY_SEPARATOR, array(__DIR__, 'resources', 'debug_html_builder', 'to_debug_html.css')))));
            }

            if (\is_string($this->inlineCss)) {
                $baseNode->appendNode(Html::style($this->inlineCss));
            }

            if ($this->cssUrl !== null) {
                foreach (\is_array($this->cssUrl) ? $this->cssUrl : array($this->cssUrl) as $css_url) {
                    $baseNode->appendNode(Html::link()->attr(array(
                        'rel'   => 'stylesheet',
                        'href'  => $css_url,
                    )));
                }
            }
        }

        $backtrace_depth    = $this->startBacktraceDepth;

        $backtrace  = $this->backTraceList;
        $file_path  = $backtrace[$backtrace_depth]['file'];
        $line       = $backtrace[$backtrace_depth]['line'];

        $omit_file_path_prefix  = $this->omitFilePathPrefix;
        if (!\is_array($omit_file_path_prefix)) {
            $omit_file_path_prefix  = array($omit_file_path_prefix);
        }

        \array_multisort(\array_map('mb_strlen', $omit_file_path_prefix), \SORT_DESC, $omit_file_path_prefix);

        $caller_file_path   = $file_path;
        foreach ($omit_file_path_prefix as $omit_file_path) {
            if ($omit_file_path === \mb_substr($caller_file_path, 0, $omit_end_point = \mb_strlen($omit_file_path))) {
                $caller_file_path   = \mb_substr($caller_file_path, $omit_end_point);
                break;
            }
        }

        $caller = \sprintf('%s(%s)', $caller_file_path, $line);

        $call_line      = null;
        $target_line    = $line - 1;
        foreach (new \SplFileObject($file_path, 'r') as $idx => $row) {
            if ($target_line === $idx) {
                $call_line  = \trim($row);
                break;
            }
        }

        $baseNode->appendNode(Html::div(\sprintf('%s: %s', $caller, $call_line))->cssClass('call'));

        foreach ($this->vars as $var) {
            if (\is_array($var)) {
                $baseNode->appendNode(Html::div(array(
                    Html::div(Html::span(\sprintf('Array(%s)', \count($var)))->cssClass('array-chip'))->cssClass('width-full'),
                    $this->buildChildNode($var, true, $this->depth)
                )));
            } elseif (\is_object($var)) {
                $baseNode->appendNode(Html::div(array(
                    Html::div(Html::span($this->buildObjectStatus($var))->cssClass('object-chip'))->cssClass('width-full'),
                    $this->buildChildNode($var, true, $this->depth)
                )));
            } else {
                $baseNode->appendNode(Html::div(
                    $this->buildChildNode($var, true, $this->depth)
                ));
            }
        }

        return $baseNode;
    }

    /**
     * 指定された変数からHtmlElementを構築します。
     *
     * @param   mixed   $var    変数
     * @param   bool    $odd    奇数行かどうか
     * @param   int     $depth  スタックの深さ
     * @return  HtmlElement HtmlElement
     */
    public function buildChildNode($var, $odd, $depth)
    {
        switch (\gettype($var)) {
            case 'boolean':
                $node   = Html::span()->cssClass('label');
                return $var ? $node->context('true')->appendClass('true') : $node->context('false')->appendClass('false');
            case 'integer':
                return Html::span((string) $var)->cssClass('int');
            case 'double':
                return Html::span()->cssClass('float')->context(false === mb_strpos((string) $var, '.') ? \sprintf('%s.0', $var) : \sprintf('%s.0', $var));
            case 'string':
                return Html::span(\sprintf('\'%s\'', $var))->cssClass('string');
            case 'array':
                if ($depth < 1) {
                    return Html::span(\sprintf('Array(%s)%s', \count($var), empty($var) ? '' : ' [omitted]'))->cssClass('array');
                }

                --$depth;

                $details = Html::details(array(
                    Html::summary(Html::span('Array details')->cssClass('array-details')),
                    Html::div(
                        Html::table(
                            /** @var HtmlElement $tbody */
                            $tbody  = Html::tbody()
                        )
                    )->cssClass('details-div')
                ));

                if ($depth + $this->defaultOpenDepth >= $this->depth) {
                    $details->attr('open', null);
                }

                if (empty($var)) {
                    $tbody->appendNode(Html::tr(Html::td('[]')->cssClass('array')->colspan('2')));
                } else {
                    foreach ($var as $key => $value) {
                        $tbody->appendNode($tr = Html::tr(array(
                            Html::td($this->buildChildNode($key, $odd, $depth)),
                            Html::td('=>')->cssClass('arrow')
                        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

                        if (\is_array($value)) {
                            $tr->appendNode(
                                Html::td(Html::span(\sprintf('Array(%s)', \count($value)))->cssClass('array-chip'))->cssClass('width-full')
                            );

                            if (!empty($value)) {
                                $tbody->appendNode(Html::tr(
                                    Html::td($this->buildChildNode($value, $odd, $depth))->cssClass('width-full')->colspan(3)
                                )->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));
                            }
                        } elseif (\is_object($value)) {
                            $tr->appendNode(
                                Html::td(Html::span($this->buildObjectStatus($value))->cssClass('object-chip'))->cssClass('width-full')
                            );

                            if (!empty($value)) {
                                $tbody->appendNode(Html::tr(
                                    Html::td($this->buildChildNode($value, $odd, $depth))->cssClass('width-full')->colspan(3)
                                )->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));
                            }
                        } else {
                            $tr->appendNode(
                                Html::td($this->buildChildNode($value, $odd, $depth))->cssClass('width-full')
                            );
                        }
                    }
                }

                return $details;
            case 'object':
                if (\version_compare(\PHP_VERSION, '8.0.0', '>=')) {
                    if (\is_a($var, "\CurlHandle")) {
                        return $this->buildCurlResourceHtml($var, $odd, $depth, null);
                    }
                }

                $object_status  = $this->buildObjectStatus($var);

                if ($depth < 1 || !$this->objectDetail) {
                    return Html::span(\sprintf('%s [omitted]', $object_status))->id($object_status)->cssClass('object');
                }

                if (isset($this->loadedObjects[$object_status])) {
                    return Html::a()->href(\sprintf('#%s', \str_replace('#', '-', $object_status)))->context(\sprintf('%s [displayed]', $object_status))->cssClass('object');
                }
                $this->loadedObjects[$object_status]    = $object_status;

                --$depth;

                $ro = new ReflectionObject($var);

                $tmp_properties = array();
                foreach ($ro->getProperties() as $property) {
                    $state      = $property->isStatic() ? 'static' : 'dynamic';
                    $modifier   = $property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : ($property->isPrivate() ? 'private' : 'unknown modifier'));

                    if ($ro->hasProperty($property->name)) {
                        $property->setAccessible(true);
                        $property_name  = $property->getName();
                        $property_value = $property->getValue($var);

                        $tmp_properties[$state][$modifier][] = array(
                            'name'  => $property_name,
                            'value' => $property_value
                        );
                    }
                }

                if (\version_compare(\PHP_VERSION, '7.2.0', '<')) {
                    foreach ($var as $property_name => $property_value) {
                        if (\is_int($property_name)) {
                            $tmp_properties['dynamic']['public'][] = array(
                                'name'  => $property_name,
                                'value' => $property_value
                            );
                        }
                    }
                }

                $details = Html::details(array(
                    Html::summary(Html::span('Object details')->cssClass('array-details')),
                    $div = Html::div()->cssClass('details-div')
                ))->id(\str_replace('#', '-', $object_status));

                if ($depth + $this->defaultOpenDepth >= $this->depth) {
                    $details->attr('open', null);
                }

                if (empty($tmp_properties)) {
                    $div->appendNode(
                        Html::table(
                            Html::tbody(
                                Html::tr(
                                    Html::td('{}')->cssClass('object')->colspan('2')
                                )
                            )
                        )
                    );
                } else {
                    foreach (array('static', 'dynamic') as $state) {
                        $is_static  = $state === 'static';

                        $div->appendNode(Html::table($tbody = Html::tbody()));

                        foreach (array('public', 'protected', 'private', 'unknown modifier') as $modifier) {
                            foreach (isset($tmp_properties[$state][$modifier]) ? $tmp_properties[$state][$modifier] : array() as $property) {
                                $modifier_color = array('access_modifier', 'label');
                                switch ($modifier) {
                                    case 'public':
                                        $modifier_color[] = 'public';
                                        break;
                                    case 'protected':
                                        $modifier_color[] = 'protected';
                                        break;
                                    case 'private':
                                        $modifier_color[] = 'private';
                                        break;
                                    case 'unknown modifier':
                                        $modifier_color[] = 'unknown';
                                        break;
                                }

                                $tdName = Html::td();
                                if ($is_static) {
                                    $tdName->appendNode(Html::span('static')->cssClass(array('scope_static', 'label')));
                                }
                                $tdName->appendNode(Html::span($modifier)->cssClass($modifier_color));

                                if ($invalid_property_name = \is_int($property['name'])) {
                                    $propertyName   = $this->buildChildNode($property['name'], $odd, $depth);
                                } else {
                                    if (\ctype_digit($property['name'])) {
                                        $propertyName   = Html::span(\sprintf('{\'%s\'}', $property['name']))->cssClass('property_name');
                                    } else {
                                        $propertyName   = Html::span(\sprintf('$%s', $property['name']))->cssClass('property_name');
                                    }
                                }

                                $tbody->appendNode($tr = Html::tr(array(
                                    $tdName,
                                    Html::td($propertyName),
                                    Html::td('=')->cssClass('arrow'),
                                ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

                                $value  = $property['value'];

                                $property_value = array();
                                if ($invalid_property_name) {
                                    $property_value[]   = Html::div(Html::span('PHP7.2.0未満ではarrayをobjectキャストした場合に発生する整数キーの値にアクセスする事はできません。')->cssClass('property_name'));
                                }

                                if (\is_array($value)) {
                                    $tr->appendNode(
                                        Html::td(Html::span(\sprintf('Array(%s)', \count($value)))->cssClass('array-chip'))->cssClass('width-full')
                                    );

                                    if (!empty($value)) {
                                        $property_value[]   = $this->buildChildNode($value, $odd, $depth);
                                        $tbody->appendNode(Html::tr(
                                            Html::td($property_value)->cssClass('width-full')->colspan('4')
                                        )->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));
                                    }
                                } elseif (\is_object($value)) {
                                    $tr->appendNode(
                                        Html::td(Html::span($this->buildObjectStatus($value))->cssClass('object-chip'))->cssClass('width-full')
                                    );

                                    $property_value[]   = $this->buildChildNode($value, $odd, $depth);
                                    $tbody->appendNode(Html::tr(
                                        Html::td($property_value)->cssClass('width-full')->colspan('4')
                                    )->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));
                                } else {
                                    $property_value[]   = $this->buildChildNode($value, $odd, $depth);

                                    $tr->appendNode(
                                        Html::td($property_value)->cssClass('width-full')
                                    );
                                }
                            }
                        }
                    }
                }

                return $details;
            case 'resource':
                $resource_type  = \get_resource_type($var);

                if ($resource_type === 'curl') {
                    return $this->buildCurlResourceHtml($var, $odd, $depth, $resource_type);
                }

                return $this->buildFileResourceHtml($var, $odd, $depth, $resource_type);
            case 'resource (closed)':
                return Html::span(\sprintf('resource (closed) %s', $var))->cssClass(array('label', 'null'));
            case 'NULL':
                return Html::span('NULL')->cssClass(array('label', 'null'));
            case 'unknown type':
            default:
                return Html::span('unknown type')->cssClass(array('label', 'null'));
        }
    }

    /**
     * file stream resourceからHtmlElementを構築します。
     *
     * @param   mixed   $var    変数
     * @param   bool    $odd    奇数行かどうか
     * @param   int     $depth  スタックの深さ
     * @return  HtmlElement HtmlElement
     */
    public function buildFileResourceHtml($var, $odd, $depth, $resource_type)
    {
        $fstat          = \fstat($var);

        $resource_type  = Html::span(\sprintf('%s %s', $resource_type, $var))->cssClass(array('label', 'resource'));

        if (!$fstat) {
            return array(
                $resource_type,
                Html::span(' [could not get fstat]')
            );
        }

        $details = Html::details(array(
            Html::summary($resource_type),
            Html::div(
                Html::table(
                    /** @var HtmlElement $tbody */
                    $tbody  = Html::tbody()
                )
            )->cssClass('details-div')
        ));

        if ($depth + $this->defaultOpenDepth >= $this->depth) {
            $details->attr('open', null);
        }

        // ファイルが格納されているデバイスの識別子
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('dev')->title('ファイルが格納されているデバイスの識別子')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['dev'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルのinode番号
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('ino')->title('ファイルのinode番号')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['ino'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルのモード
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('mode')->title('ファイルのモード')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span(\sprintf('%s (%s)', \decoct($fstat['mode']), $fstat['mode']))->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルのハードリンク数
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('nlink')->title('ファイルのハードリンク数')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['nlink'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルの所有者のユーザーID
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('uid')->title('ファイルの所有者のユーザーID')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['uid'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルの所有者のグループID
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('gid')->title('ファイルの所有者のグループID')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['gid'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルが特殊なデバイスを表す場合に、そのデバイスの識別子
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('rdev')->title('ファイルが特殊なデバイスを表す場合に、そのデバイスの識別子')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['rdev'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルのサイズ
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('size')->title('ファイルのサイズ')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span(\sprintf('%s (%sB)', Convert::toUnitByteSize($fstat['size']), \number_format($fstat['size'])))->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルが最後に読み込まれた日時
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('atime')->title('ファイルが最後に読み込まれた日時')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span(\sprintf('%s (%s)', date('Y-m-d H:i:s', $fstat['atime']), $fstat['atime']))->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルが最後に変更された日時
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('mtime')->title('ファイルが最後に変更された日時')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span(\sprintf('%s (%s)', date('Y-m-d H:i:s', $fstat['mtime']), $fstat['mtime']))->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルが最後にステータスが変更された日時
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('ctime')->title('ファイルが最後にステータスが変更された日時')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span(\sprintf('%s (%s)', date('Y-m-d H:i:s', $fstat['ctime']), $fstat['ctime']))->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルシステムが使用するブロックサイズ
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('blksize')->title('ファイルシステムが使用するブロックサイズ')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['blksize'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        // ファイルが占めるブロック数
        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('blocks')->title('ファイルが占めるブロック数')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($fstat['blocks'])->cssClass('int'))->cssClass('width-full')
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        return $details;
    }

    /**
     * cURL resourceからHtmlElementを構築します。
     *
     * @param   mixed   $var    変数
     * @param   bool    $odd    奇数行かどうか
     * @param   int     $depth  スタックの深さ
     * @return  HtmlElement HtmlElement
     */
    public function buildCurlResourceHtml($var, $odd, $depth, $resource_type)
    {
        if (\is_resource($var)) {
            $resource_type  = Html::span(\sprintf('%s %s', $resource_type, $var))->cssClass(array('label', 'resource'));
        } else {
            $resource_type  = Html::span($this->buildObjectStatus($var))->cssClass(array('label', 'resource'));
        }

        $details = Html::details(array(
            Html::summary($resource_type),
            Html::div(
                Html::table(
                    /** @var HtmlElement $tbody */
                    $tbody  = Html::tbody()
                )
            )->cssClass('details-div')
        ));

        if ($depth + $this->defaultOpenDepth >= $this->depth) {
            $details->attr('open', null);
        }

        --$depth;

        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('curl error no')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($this->buildChildNode(\curl_errno($var), $odd, $depth)))->cssClass('width-full'),
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('curl error')),
            Html::td(':')->cssClass('arrow'),
            Html::td(Html::span($this->buildChildNode(\curl_errno($var) === 0 ? '現時点でエラーはありません。' : \curl_error($var), $odd, $depth)))->cssClass('width-full'),
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('curl version')),
            Html::td(':')->cssClass('arrow'),
            Html::td()
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span($this->buildChildNode(\curl_version(), $odd, $depth)))->cssClass('width-full')->colspan(3)
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span('curl info')),
            Html::td(':')->cssClass('arrow'),
            Html::td()
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        $tbody->appendNode(Html::tr(array(
            Html::td(Html::span($this->buildChildNode(\curl_getinfo($var), $odd, $depth)))->cssClass('width-full')->colspan(3)
        ))->cssClass(!($odd = !$odd) ? 'row-odd' : 'row-even'));

        return $details;
    }

    /**
     * オブジェクトステータス文字列を構築して返します。
     *
     * @param   Object  $var    オブジェクト
     * @return  string  オブジェクトステータス文字列
     */
    public function buildObjectStatus($var)
    {
        if (!function_exists('spl_object_id')) {
            \ob_start();
            \var_dump($var);
            $object_status = \ob_get_clean();
            $object_status = \substr($object_status, 0, \strpos($object_status, ' ('));
            return \sprintf('object%s', \substr($object_status, 6));
        }

        return \sprintf('object(%s)#%d', \get_class($var), \spl_object_id($var));
    }
}
