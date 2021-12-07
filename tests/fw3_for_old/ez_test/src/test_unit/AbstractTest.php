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

namespace fw3_for_old\ez_test\test_unit;

use fw3_for_old\ez_test\TestRunner;

/**
 * 抽象テスト実施クラス
 */
abstract class AbstractTest implements TestInterface
{
    /**
     * @var array   テストログ
     */
    protected $logs = array(
        'success'   => array(),
        'failed'    => array(),
        'error'     => array(),
    );

    const OPERATOR_EQUAL                    = '==';
    const OPERATOR_IDENTICAL                = '===';
    const OPERATOR_NOT_EQUAL                = '!=';
    const OPERATOR_NOT_EQUAL_ALIAS          = '<>';
    const OPERATOR_NOT_IDENTICAL            = '!==';
    const OPERATOR_LESS_THAN                = '<';
    const OPERATOR_GREATER_THAN             = '>';
    const OPERATOR_LESS_THAN_OR_EQUAL_TO    = '<=';
    const OPERATOR_GREATER_THAN_OR_EQUAL_TO = '>=';

    const OPERATOR_SAME                     = self::OPERATOR_EQUAL;
    const OPERATOR_NOT_AME                  = self::OPERATOR_NOT_IDENTICAL;

    const OP_EQ         = self::OPERATOR_EQUAL;
    const OP_SAME       = self::OPERATOR_IDENTICAL;
    const OP_N_EQ       = self::OPERATOR_NOT_EQUAL;
    const OP_ALIAS_N_EQ = self::OPERATOR_NOT_EQUAL_ALIAS;
    const OP_N_SAME     = self::OPERATOR_NOT_IDENTICAL;
    const OP_LT         = self::OPERATOR_LESS_THAN;
    const OP_GT         = self::OPERATOR_GREATER_THAN;
    const OP_LT_EQ      = self::OPERATOR_LESS_THAN_OR_EQUAL_TO;
    const OP_GT_EQ      = self::OPERATOR_GREATER_THAN_OR_EQUAL_TO;

    protected static $OPERATOR_LIST = array(
        self::OPERATOR_EQUAL                    => self::OPERATOR_EQUAL,
        self::OPERATOR_IDENTICAL                => self::OPERATOR_IDENTICAL,
        self::OPERATOR_NOT_EQUAL                => self::OPERATOR_NOT_EQUAL,
        self::OPERATOR_NOT_EQUAL_ALIAS          => self::OPERATOR_NOT_EQUAL_ALIAS,
        self::OPERATOR_NOT_IDENTICAL            => self::OPERATOR_NOT_IDENTICAL,
        self::OPERATOR_LESS_THAN                => self::OPERATOR_LESS_THAN,
        self::OPERATOR_GREATER_THAN             => self::OPERATOR_GREATER_THAN,
        self::OPERATOR_LESS_THAN_OR_EQUAL_TO    => self::OPERATOR_LESS_THAN_OR_EQUAL_TO,
        self::OPERATOR_GREATER_THAN_OR_EQUAL_TO => self::OPERATOR_GREATER_THAN_OR_EQUAL_TO,
    );

    private $contexts = array();

    protected $preparedException = null;

    protected $preparedExceptionMessage = null;

    final public function __construct($contexts)
    {
        $this->contexts = $contexts;
    }

    final protected function hasContext($name)
    {
        return isset($this->contexts[$name]) || array_key_exists($name, $this->contexts);
    }

    final protected function getContext($name)
    {
        return isset($this->contexts[$name]) ? $this->contexts[$name] : null;
    }

    final protected function getContexts()
    {
        return $this->contexts;
    }

    public function hasPreparedException()
    {
        return $this->preparedException !== null
        || $this->preparedExceptionMessage !== null;
    }

    public function cleanupPreparedException()
    {
        $this->preparedException        = null;
        $this->preparedExceptionMessage = null;
    }

    /**
     * 値を型判定付きでsameアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertSame($expected, $actual, $message = null)
    {
        $this->log($expected === $actual, $expected, $actual, $message);
    }

    /**
     * 値を型判定付きでnot sameアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertNotSame($expected, $actual, $message = null)
    {
        $this->log($expected !== $actual, $expected, $actual, $message);
    }

    /**
     * 値を型判定無しでequalアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertEquals($expected, $actual, $message = null)
    {
        $this->log($expected == $actual, $expected, $actual, $message);
    }

    /**
     * 値を型判定無しでnot equalアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertNotEquals($expected, $actual, $message = null)
    {
        $this->log($expected != $actual, $expected, $actual, $message);
    }

    /**
     * 値を比較アサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $operator   オペレータ
     * @param   string  $message    追加のメッセージ
     */
    protected function assertComparisons($expected, $actual, $operator, $message = null)
    {
        if (!isset(static::$OPERATOR_LIST[$operator])) {
            throw new \Exception(sprintf('未知のオペレータを指定されました。operator:%s', TestRunner::toText($operator, 2)));
        }
        $operator   = static::$OPERATOR_LIST[$operator];

        switch ($operator) {
            case self::OPERATOR_EQUAL:
                $status = $actual ==   $expected;
                break;
            case self::OPERATOR_IDENTICAL:
                $status = $actual ===  $expected;
                break;
            case self::OPERATOR_NOT_EQUAL:
                $status = $actual !=   $expected;
                break;
            case self::OPERATOR_NOT_EQUAL_ALIAS:
                $status = $actual <>   $expected;
                break;
            case self::OPERATOR_NOT_IDENTICAL:
                $status = $actual !==  $expected;
                break;
            case self::OPERATOR_LESS_THAN:
                $status = $actual <    $expected;
                break;
            case self::OPERATOR_GREATER_THAN:
                $status = $actual >    $expected;
                break;
            case self::OPERATOR_LESS_THAN_OR_EQUAL_TO:
                $status = $actual <=   $expected;
                break;
            case self::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
                $status = $actual >=   $expected;
                break;
        }

        $this->log($status, $expected, $actual, $message);
    }

    /**
     * 実際の値が予想される値より少ないかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertLessThan($expected, $actual, $message = null)
    {
        $this->log($actual < $expected, $expected, $actual, $message);
    }

    /**
     * 実際の値が予想される値より大きいかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertGreaterThan($expected, $actual, $message = null)
    {
        $this->log($actual > $expected, $expected, $actual, $message);
    }

    /**
     * 実際の値が予想される値より少ないか等しいかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertLessThanOrEqualTo($expected, $actual, $message = null)
    {
        $this->log($actual <= $expected, $expected, $actual, $message);
    }

    /**
     * 実際の値が予想される値より大きいか等しいかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertGreaterThanOrEqualTo($expected, $actual, $message = null)
    {
        $this->log($actual >= $expected, $expected, $actual, $message);
    }

    /**
     * 値がbool trueかアサーションします。
     *
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertTrue($actual, $message = null)
    {
        $this->log(true === $actual, true, $actual, $message);
    }

    /**
     * 値がbool falseかアサーションします。
     *
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertFalse($actual, $message = null)
    {
        $this->log(false === $actual, false, $actual, $message);
    }

    /**
     * 値にキーが含まれているかアサーションします。
     *
     * @param   int|string  $key        キー
     * @param   array       $array      配列
     * @param   string      $message    追加のメッセージ
     */
    protected function assertArrayHasKey($key, $array, $message = null)
    {
        $this->log(isset($array[$key]) || array_key_exists($key, $array), $key, $array, $message);
    }

    /**
     * 値にキーが含まれていないかアサーションします。
     *
     * @param   int|string  $key    キー
     * @param   array       $array  配列
     * @param   string  $message    追加のメッセージ
     */
    protected function assertArrayNotHasKey($key, $array, $message = null)
    {
        $this->log(!(isset($array[$key]) || array_key_exists($key, $array)), $key, $array, $message);
    }

    /**
     * 値が期待するクラスインスタンスかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertInstanceOf($expected, $actual, $message = null)
    {
        $this->log($actual instanceof $expected, $expected, $actual, $message);
    }

    /**
     * 値が期待する子クラスかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function assertIsSubclassOf($expected, $actual, $message = null)
    {
        $this->log(is_subclass_of($actual, $expected), $expected, $actual, $message);
    }

    /**
     * 事前準備済みの例外をアサーションします。
     *
     * @param   mixed   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    public function assertPreparedException($actual, $message = null)
    {
        if ($this->preparedException !== null) {
            $this->assertException($this->preparedException, $actual, $message);
        }

        if ($this->preparedExceptionMessage !== null) {
            $this->assertExceptionMessage($this->preparedExceptionMessage, $actual, $message);
        }
    }

    /**
     * 例外をアサーションします。
     *
     * @param   string      $expected   予想される値
     * @param   \Exception  $actual     実際の例外
     * @param   string      $message    追加のメッセージ
     */
    protected function assertException($expected, $actual = null, $message = null)
    {
        if ($actual !== null) {
            $this->log($actual instanceof $expected, $expected, $actual, $message);
        } else {
            $this->preparedException    = $expected;
        }
    }

    /**
     * 例外メッセージをアサーションします。
     *
     * @param   string      $expected   予想される値
     * @param   \Exception  $actual     実際の例外
     * @param   string      $message    追加のメッセージ
     */
    protected function assertExceptionMessage($expected, $actual = null, $message = null)
    {
        if ($actual !== null) {
            $actual = $actual->getMessage();
            $this->log($expected === $actual, $expected, $actual, $message);
        } else {
            $this->preparedExceptionMessage = $expected;
        }
    }

    /**
     * 与えられたストリームで書き込み時のストリームフィルタをアサーションします。
     *
     * @param   string          $expected       予想される値
     * @param   string          $value          実行する値
     * @param   array|string    $stream_specs   ストリームスペック
     * @param   string          $message        追加のメッセージ
     */
    protected function assertWriteStreamFilterSame($expected, $value, $stream_specs, $message = null)
    {
        $fp     = @\fopen($stream_specs, 'ab');
        @\fwrite($fp, $value);

        \fseek($fp, 0, \SEEK_END);
        $length = \ftell($fp);

        @\rewind($fp);

        $actual = @\fread($fp, $length);

        @\fclose($fp);

        $this->assertSame($expected, $actual, $message);
    }

    /**
     * 与えられたストリームで書き込み時のストリームフィルタが異なる結果になる事をアサーションします。
     *
     * @param   string          $expected       予想される値
     * @param   string          $value          実行する値
     * @param   array|string    $stream_specs   ストリームスペック
     * @param   string          $message        追加のメッセージ
     */
    protected function assertWriteStreamFilterNotSame($expected, $value, $stream_specs, $message = null)
    {
        $fp     = @\fopen($stream_specs, 'ab');
        @\fwrite($fp, $value);

        \fseek($fp, 0, \SEEK_END);
        $length = \ftell($fp);

        @\rewind($fp);

        $actual = @\fread($fp, $length);

        @\fclose($fp);

        $this->assertNotSame($expected, $actual, $message);
    }

    /**
     * 与えられたストリームでCSV入力をアサーションします。
     *
     * @param   array           $expected           予想される値
     * @param   string          $csv_text           実行する値
     * @param   int             $stream_chunk_size  ストリームラッパーのチャンクサイズ
     * @param   string|array    $stream_specs       ストリームスペック
     * @param   string          $message            追加のメッセージ
     */
    protected function assertCsvInputStreamFilterSame($expected, $csv_text, $stream_chunk_size, $stream_specs, $message = null)
    {
        $fp     = @\fopen($stream_specs, 'ab');

        @\fwrite($fp, $csv_text);

        @\rewind($fp);

        if (function_exists('stream_set_chunk_size')) {
            \stream_set_chunk_size($fp, $stream_chunk_size);
        }

        if (function_exists('stream_set_read_buffer')) {
            \stream_set_read_buffer($fp, $stream_chunk_size);
        }

        $actual = array();
        for (;($row = \fgetcsv($fp, 1024)) !== FALSE;$actual[] = $row);

        @\fclose($fp);

        $this->assertSame($expected, $actual, $message);
    }

    /**
     * 与えられたストリームでCSV出力をアサーションします。
     *
     * @param   string          $expected           予想される値
     * @param   array           $csv_data           実行する値
     * @param   int             $stream_chunk_size  ストリームラッパーのチャンクサイズ
     * @param   string|array    $stream_specs       ストリームスペック
     * @param   string          $message            追加のメッセージ
     */
    protected function assertCsvOutputStreamFilterSame($expected, $csv_data, $stream_chunk_size, $stream_specs, $message = null)
    {
        $fp     = @\fopen($stream_specs, 'ab');

        if (function_exists('stream_set_chunk_size')) {
            \stream_set_chunk_size($fp, $stream_chunk_size);
        }

        if (function_exists('stream_set_read_buffer')) {
            \stream_set_read_buffer($fp, $stream_chunk_size);
        }

        foreach ($csv_data as $data) {
            @\fputcsv($fp, $data);
        }

        @\rewind($fp);

        $actual = '';
        while ($row = \fread($fp, 1024)) {
            $actual .= $row;
        }

        @\fclose($fp);

        $this->assertSame($expected, $actual, $message);
    }

    /**
     * 整数値で表現されたコードポイントをUTF-8文字に変換する。
     *
     * @param   int     $code_point UTF-8文字に変換したいコードポイント
     * @return  string  コードポイントから作成したUTF-8文字
     */
    protected function int2utf8($code_point) {
        //UTF-16コードポイント内判定
        if ($code_point < 0) {
            throw new \Exception(sprintf('%1$s is out of range UTF-16 code point (0x000000 - 0x10FFFF)', $code_point));
        }
        if (0x10FFFF < $code_point) {
            throw new \Exception(sprintf('0x%1$X is out of range UTF-16 code point (0x000000 - 0x10FFFF)', $code_point));
        }

        //サロゲートペア判定
        if (0xD800 <= $code_point && $code_point <= 0xDFFF) {
            throw new \Exception(sprintf('0x%X is in of range surrogate pair code point (0xD800 - 0xDFFF)', $code_point));
        }

        //1番目のバイトのみでchr関数が使えるケース
        if ($code_point < 0x80) {
            return \chr($code_point);
        }

        //2番目のバイトを考慮する必要があるケース
        if ($code_point < 0xA0) {
            return \chr(0xC0 | $code_point >> 6) . \chr(0x80 | $code_point & 0x3F);
        }

        //数値実体参照表記からの変換
        return \html_entity_decode('&#'. $code_point .';');
    }

    /**
     * テストをスキップします。
     *
     * @param   string  $message    スキップ事由
     */
    protected function skipTest($message)
    {
        $backtrace          = \debug_backtrace();

        $idx = 1;
        do {
            if ($backtrace[$idx]['class'] !== get_class()) {
                $backtrace_detail   = \sprintf(
                    '%s%s%s() in line %d',
                    $backtrace[$idx]['class'],
                    $backtrace[$idx]['type'],
                    $backtrace[$idx]['function'],
                    $backtrace[$idx - 1]['line']
                    );
                break;
            }
            ++$idx;
        } while (true);

        $this->logs['skip'][] = array(
            'backtrace' => $backtrace_detail,
            'message'   => $message,
        );
    }

    /**
     * アサーションの実行内容をログに保存します。
     *
     * @param   bool    $status     実行時の検証結果
     * @param   mixed   $expected   予想される値
     * @param   mixed   $actual     実際の値
     * @param   string  $message    追加のメッセージ
     */
    protected function log($status, $expected, $actual, $message = null)
    {
        $backtrace          = \debug_backtrace();

        $idx = 2;
        do {
            if ($backtrace[$idx]['class'] !== get_class()) {
                $backtrace_detail   = \sprintf(
                    '%s%s%s() in line %d',
                    $backtrace[$idx]['class'],
                    $backtrace[$idx]['type'],
                    $backtrace[$idx]['function'],
                    $backtrace[$idx - 1]['line']
                );
                break;
            }
            ++$idx;
        } while (true);

        $key = $status ? 'success' : 'failed';
        $this->logs[$key][] = array(
            'backtrace' => $backtrace_detail,
            'actual'    => $actual,
            'expected'  => $expected,
            'message'   => $message,
        );
    }

    /**
     * Stream Wrapper設定を文字列表現に変換します。
     *
     * @param   array   $steram_wrapper ストリームラッパー設定
     * @return  string  ストリームラッパー設定
     */
    protected function convertSteramWrapper($steram_wrapper)
    {
        $stack  = array();
        foreach ($steram_wrapper as $key => $context) {
            $stack[]    = \sprintf('%s=%s', $key, \implode('|', (array) $context));
        }

        return \sprintf('php://filter/%s', \implode('/', $stack));
    }

    /**
     * 実行されたアサーションが一つも無かったかどうかを返します。
     *
     * @return  bool    実行されたアサーションが一つも無かったかどうか
     */
    public function isNoAssertions()
    {
        return empty($this->logs['success'])
         && empty($this->logs['skip'])
         && empty($this->logs['failed'])
         && empty($this->logs['error']);
    }

    /**
     * 現在までに保存されたログを返します。
     *
     * @return  array   現在までに保存されたログ
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * ログをmergeします。
     *
     * @param   array   $logs   ログ
     * @return  AbstractTest    このインスタンス
     */
    public function mergeLogs($logs)
    {
        foreach ($logs as $status => $log) {
            foreach ($log as $detail) {
                $this->logs[$status][] = $detail;
            }
        }
        return $this;
    }

    /**
     * 同一プロセス中で一度だけ実行される初期化処理
     */
    public function initialize()
    {
    }

    /**
     * テストクラスセットアップ
     */
    public function setupTestClass()
    {
    }

    /**
     * テストセットアップ
     */
    public function setupTest()
    {
    }

    /**
     * テストティアダウン
     */
    public function teardownTest()
    {
    }

    /**
     * テストクラスティアダウン
     */
    public function teardownTestClass()
    {
    }


    /**
     * 同一プロセス中で一度だけ実行される終了処理
     */
    public function finalize()
    {
    }
}
