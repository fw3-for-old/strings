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

    protected $preparedException = null;

    protected $preparedExceptionMessage = null;

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
     * 値を型判定付きでアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     */
    protected function assertSame($expected, $actual)
    {
        $this->log($expected === $actual, $expected, $actual);
    }

    /**
     * 値を型判定付きでnoteアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     */
    protected function assertNotSame($expected, $actual)
    {
        $this->log($expected !== $actual, $expected, $actual);
    }

    /**
     * 値を型判定無しでアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     */
    protected function assertEquals($expected, $actual)
    {
        $this->log($expected == $actual, $expected, $actual);
    }

    /**
     * 値を型判定無しでnoteアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     */
    protected function assertNotEquals($expected, $actual)
    {
        $this->log($expected != $actual, $expected, $actual);
    }

    /**
     * 値がbool trueかアサーションします。
     *
     * @param   array   $actual     実際の値
     */
    protected function assertTrue($actual)
    {
        $this->log(true === $actual, true, $actual);
    }

    /**
     * 値がbool falseかアサーションします。
     *
     * @param   array   $actual     実際の値
     */
    protected function assertFalse($actual)
    {
        $this->log(false === $actual, false, $actual);
    }

    /**
     * 値にキーが含まれているかアサーションします。
     *
     * @param   int|string  $key    キー
     * @param   array       $array  配列
     */
    protected function assertArrayHasKey($key, $array)
    {
        $this->log(isset($array[$key]) || array_key_exists($key, $array), $key, $array);
    }

    /**
     * 値にキーが含まれていないかアサーションします。
     *
     * @param   int|string  $key    キー
     * @param   array       $array  配列
     */
    protected function assertArrayNotHasKey($key, $array)
    {
        $this->log(!(isset($array[$key]) || array_key_exists($key, $array)), $key, $array);
    }

    /**
     * 値が期待するクラスインスタンスかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     */
    protected function assertInstanceOf($expected, $actual)
    {
        $this->log($actual instanceof $expected, $expected, $actual);
    }

    /**
     * 値が期待する子クラスかアサーションします。
     *
     * @param   array   $expected   予想される値
     * @param   array   $actual     実際の値
     */
    protected function assertIsSubclassOf($expected, $actual)
    {
        $this->log(is_subclass_of($actual, $expected), $expected, $actual);
    }

    /**
     * 事前準備済みの例外をアサーションします。
     *
     * @param   mixed   $actual 実際の値
     */
    public function assertPreparedException($actual)
    {
        if ($this->preparedException !== null) {
            $this->assertException($this->preparedException, $actual);
        }

        if ($this->preparedExceptionMessage !== null) {
            $this->assertExceptionMessage($this->preparedExceptionMessage, $actual);
        }
    }

    /**
     * 例外をアサーションします。
     *
     * @param   string      $expected   予想される値
     * @param   \Exception  $actual     実際の例外
     */
    protected function assertException($expected, $actual = null)
    {
        if ($actual !== null) {
            $this->log($actual instanceof $expected, $expected, $actual);
        } else {
            $this->preparedException    = $expected;
        }
    }

    /**
     * 例外メッセージをアサーションします。
     *
     * @param   string      $expected   予想される値
     * @param   \Exception  $actual     実際の例外
     */
    protected function assertExceptionMessage($expected, $actual = null)
    {
        if ($actual !== null) {
            $actual = $actual->getMessage();
            $this->log($expected === $actual, $expected, $actual);
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
     */
    protected function assertWriteStreamFilterSame($expected, $value, $stream_specs)
    {
        $fp     = @\fopen($stream_specs, 'ab');
        @\fwrite($fp, $value);

        \fseek($fp, 0, \SEEK_END);
        $length = \ftell($fp);

        @\rewind($fp);

        $actual = @\fread($fp, $length);

        @\fclose($fp);

        $this->assertSame($expected, $actual);
    }

    /**
     * 与えられたストリームで書き込み時のストリームフィルタが異なる結果になる事をアサーションします。
     *
     * @param   string          $expected       予想される値
     * @param   string          $value          実行する値
     * @param   array|string    $stream_specs   ストリームスペック
     */
    protected function assertWriteStreamFilterNotSame($expected, $value, $stream_specs)
    {
        $fp     = @\fopen($stream_specs, 'ab');
        @\fwrite($fp, $value);

        \fseek($fp, 0, \SEEK_END);
        $length = \ftell($fp);

        @\rewind($fp);

        $actual = @\fread($fp, $length);

        @\fclose($fp);

        $this->assertNotSame($expected, $actual);
    }

    /**
     * 与えられたストリームでCSV入力をアサーションします。
     *
     * @param   array           $expected           予想される値
     * @param   string          $csv_text           実行する値
     * @param   int             $stream_chunk_size  ストリームラッパーのチャンクサイズ
     * @param   string|array    $stream_specs       ストリームスペック
     */
    protected function assertCsvInputStreamFilterSame($expected, $csv_text, $stream_chunk_size, $stream_specs)
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

        $this->assertSame($expected, $actual);
    }

    /**
     * 与えられたストリームでCSV出力をアサーションします。
     *
     * @param   string          $expected           予想される値
     * @param   array           $csv_data           実行する値
     * @param   int             $stream_chunk_size  ストリームラッパーのチャンクサイズ
     * @param   string|array    $stream_specs       ストリームスペック
     */
    protected function assertCsvOutputStreamFilterSame($expected, $csv_data, $stream_chunk_size, $stream_specs)
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

        $this->assertSame($expected, $actual);
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
     * アサーションの実行内容をログに保存します。
     *
     * @param   bool    $status     実行時の検証結果
     * @param   mixed   $expected   予想される値
     * @param   mixed   $actual     実際の値
     */
    protected function log($status, $expected, $actual)
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
