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

namespace fw3_for_old\ez_test\reflectors\annotations\abstracts;

/**
 * Abstract Annotation
 */
abstract class AbstractAnnotation
{
    /**
     * @var mixed   値
     */
    protected $value;

    /**
     * @var string  オプション値
     */
    protected $options;

    /**
     * @var array   二行目以降のオプション値
     */
    protected $stacks;

    /**
     * @var array   実行時パラメータ
     */
    protected $parameters;

    /**
     * constract
     *
     * @param   string  $options  オプション値
     * @param   array   $stacks   二つ目以降のオプション値
     * @param   array   $parameters   実行時パラメータ
     * @return  static  このインスタンス
     */
    protected function __construct($options, $stacks, $parameters)
    {
        $this->options      = $options;
        $this->stacks       = $stacks;
        $this->parameters   = $parameters;

        $this->value        = $this->parse();
    }

    /**
     * factory
     *
     * @param   string  $options  オプション値
     * @param   array   $stacks   二つ目以降のオプション値
     * @param   array   $parameters   実行時パラメータ
     * @return  static  このインスタンス
     */
    public static function factory($options, $stacks, $parameters)
    {
        return new static($options, $stacks, $parameters);
    }

    /**
     * 値を返します。
     *
     * @return  mixed   値
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 与えられた状態をパースして返します。
     *
     * @return  mixed   パース結果
     */
    abstract protected function parse();

    /**
     * このアノテーションが使用可能かどうかを返します。
     *
     * @return  bool    このアノテーションが使用可能かどうか
     */
    public function useable()
    {
        return true;
    }
}
