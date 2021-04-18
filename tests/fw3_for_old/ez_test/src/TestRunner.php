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

namespace fw3_for_old\ez_test;

use fw3_for_old\ez_test\reflectors\ReflectionTestMethod;
use fw3_for_old\ez_test\reflectors\ReflectionTestObject;
use fw3_for_old\ez_test\test_unit\AbstractTest;

/**
 * ez_test runner
 */
class TestRunner
{
    /**
     * @var string  実行モード：ダイレクトドライブ
     */
    const EXEC_MODE_DIRECT_DRIVE    = 'direct_drive';

    /**
     * @var string  実行モード：API
     */
    const EXEC_MODE_API             = 'api';

    /**
     * @var string  実行モード：プロセスフォーク
     */
    const EXEC_MODE_PROCESS_FORK    = 'fork';

    /**
     * @var string  実行モード：デフォルト
     */
    const EXEC_MODE_DEFAULT         = self::EXEC_MODE_DIRECT_DRIVE;

    /**
     * @var string  実行モードマップ
     */
    protected static $EXEC_MODE_MAP = array(
        self::EXEC_MODE_DIRECT_DRIVE    => self::EXEC_MODE_DIRECT_DRIVE,
        self::EXEC_MODE_API             => self::EXEC_MODE_API,
        self::EXEC_MODE_PROCESS_FORK    => self::EXEC_MODE_PROCESS_FORK,
    );

    /**
     * @var string  標準出力モード：無効
     */
    const STD_OUT_MODE_DISALBE  = 'disable';

    /**
     * @var string  標準出力モード：テキスト
     */
    const STD_OUT_MODE_TEXT     = 'text';

    /**
     * @var string  標準出力モード：JSON
     */
    const STD_OUT_MODE_JSON     = 'json';

    /**
     * @var string  標準出力モード：デフォルト
     */
    const STD_OUT_MODE_DEFAULT  = self::STD_OUT_MODE_TEXT;

    /**
     * @var string  標準出力モードマップ
     */
    protected static $STD_OUT_MODE_MAP  = array(
        self::STD_OUT_MODE_DISALBE  => self::STD_OUT_MODE_DISALBE,
        self::STD_OUT_MODE_TEXT     => self::STD_OUT_MODE_TEXT,
        self::STD_OUT_MODE_JSON     => self::STD_OUT_MODE_JSON,
    );

    /**
     * @var string  実行モード
     */
    protected $execMode = self::EXEC_MODE_DEFAULT;

    /**
     * @var string  テストクラスの指定
     */
    protected $targetTestClass   = null;

    /**
     * @var string  テストメソッドの指定
     */
    protected $targetTestMethod  = null;

    /**
     * @var string  テストケースルートディレクトリ
     */
    protected $testCaseRootDir  = null;

    /**
     * @var bool    アサーションに失敗した場合に当該のテストを停止するかどうか
     */
    protected $stopWithAssertionFailed  = false;

    /**
     * @var string  PHPバイナリパス
     */
    protected $phpBinaryPath    = null;

    /**
     * @var string  php.iniパス
     */
    protected $phpIniPath       = null;

    /**
     * @var string  標準出力モード
     */
    protected $stdOutMode    = self::STD_OUT_MODE_DEFAULT;

    /**
     * @var float   インスタンス生成時のタイムスタンプ
     */
    protected $startMicrotime   = 0;

    /**
     * @var array   TestRunnerがもつコンテキスト情報
     * TestRunner生成時のコンテキスト情報をテストクラスに渡したい場合に使う
     */
    protected $contexts = array();

    /**
     * @var null|array  実行結果
     */
    protected $result   = null;

    /**
     * constructor
     */
    protected function __construct()
    {
        $this->startMicrotime   = microtime(true);
    }

    /**
     * テストランナーインスタンスを返します。
     *
     * @return  TestRunner  テストランナーインスタンス
     */
    public static function factory($options = array())
    {
        $instance   = new static();

        if (isset($options['php_binary_path'])) {
            $instance->phpBinaryPath($options['php_binary_path']);
        }

        if (isset($options['php_ini_path'])) {
            $instance->phpIniPath($options['php_ini_path']);
        }

        if (isset($options['case_root_dir'])) {
            $instance->testCaseRootDir($options['case_root_dir']);
        } else {
            $backtrace  = debug_backtrace(false);
            reset($backtrace);
            $backtrace  = current($backtrace);

            $base_dir   = \dirname($backtrace['file']);

            $other_than_windows = \substr(\PHP_OS, 0, 3) !== 'WIN';

            $case_root_dir  = $base_dir;

            foreach (array('cases', 'case') as $dir_name) {
                $case_dir   = \sprintf('%s/%s', $base_dir, $dir_name);

                if (!\file_exists($case_dir)) {
                    continue;
                }

                if (!\is_readable($case_dir)) {
                    continue;
                }

                if ($other_than_windows && !\is_executable($case_dir)) {
                    continue;
                }

                $case_root_dir  = $case_dir;
                break;
            }

            $instance->testCaseRootDir($case_root_dir);
        }

        $cli_options    = \getopt('', array(
            'mode:',
            'class:',
            'method:',
            'stop_with_assertion_failed:',
        ));

        if (isset($cli_options['mode'])) {
            $instance->execMode($cli_options['mode']);
        }

        if ($instance->isExecModeProcessFork()) {
            if (!isset($cli_options['class']) || !isset($cli_options['method'])) {
                throw new \Exception('実行モードがプロセスフォークの場合、classとmethodを同時に指定する必要があります。');
            }

            $instance->targetTestClass  = $cli_options['class'];
            $instance->targetTestMethod = $cli_options['method'];

            $instance->stdOutMode(static::STD_OUT_MODE_JSON);
        } elseif ($instance->isExecModeApi()) {
            $instance->stdOutMode(static::STD_OUT_MODE_JSON);
        }

        if (isset($cli_options['stop_with_assertion_failed'])) {
            $instance->stopWithAssertionFailed($cli_options['stop_with_assertion_failed']);
        }

        return $instance;
    }

    /**
     * テストを実行します。
     *
     * @return  \fw3_for_old\ez_test\TestRunner このインスタンス
     */
    public function run()
    {
        if ($this->testCaseRootDir === null) {
            throw new \Exception('テストケースのルートディレクトリが指定されていません。');
        }

        $start_time = \explode('.', $this->startMicrotime);
        $start_time = \sprintf('%s.%s', \date('Y/m/d H:i:s', $start_time[0]), isset($start_time[1]) ? $start_time[1] : 0);

        if ($this->stdOutMode === self::STD_OUT_MODE_TEXT) {
            echo '================================================', \PHP_EOL;
            echo ' fw3_for_old/ez_test.', \PHP_EOL;
            echo \sprintf(' target test cases => %s', $this->testCaseRootDir), \PHP_EOL;
            echo '================================================', \PHP_EOL;
            echo \sprintf(' start time  : %s', $start_time), \PHP_EOL;
        }

        \ob_start();
        $logs       = $this->test($this->pickupTestCase());

        $end_mts    = \microtime(true);
        $end_time   = \explode('.', $end_mts);
        $end_time   = \sprintf('%s.%s', \date('Y/m/d H:i:s', $end_time[0]), isset($end_time[1]) ? $end_time[1] : 0);

        $exec_time  = $end_mts - $this->startMicrotime;

        $std_out    = \ob_get_clean();

        if ($this->stdOutMode === self::STD_OUT_MODE_TEXT) {
            echo \sprintf(' end time    : %s', $end_time), \PHP_EOL;
            echo \sprintf(' exec time   : %ssec', $exec_time), \PHP_EOL;
            echo '================================================', \PHP_EOL;
            echo \PHP_EOL;

            if ($std_out !== '') {
                echo '================================================', \PHP_EOL;
                echo ' std out.', \PHP_EOL;
                echo '================================================', \PHP_EOL;
                echo $std_out;
                echo '================================================', \PHP_EOL;
                echo \PHP_EOL;
            }
        }

        $this->result = array(
            'test_case_root_dir'    => $this->testCaseRootDir,
            'time'                  => array(
                'start_microtime'   => $this->startMicrotime,
                'start_datetime'    => $start_time,
                'end_microtime'     => $end_mts,
                'end_datetime'      => $end_time,
                'exec_time'         => $exec_time,
            ),
            'std_out'               => $std_out,
            'logs'                  => $logs,
        );

        if ($this->stdOutMode === self::STD_OUT_MODE_TEXT) {
            $parsed_logs    = static::logParse($logs);

            $is_error       = $parsed_logs['is_error'];
            $total          = $parsed_logs['total'];
            $success_total  = $parsed_logs['success_total'];
            $failed_total   = $parsed_logs['failed_total'];
            $error_total    = $parsed_logs['error_total'];
            $detail_message = $parsed_logs['detail_message'];

            echo '================================================', \PHP_EOL;
            echo \sprintf(' test result: %s (%d / %d)', $is_error ? 'failed' : 'success', $success_total, $total), \PHP_EOL;
            echo '------------------------------------------------', \PHP_EOL;
            echo ' details', \PHP_EOL;
            echo '------------------------------------------------', \PHP_EOL;
            echo \implode(\PHP_EOL, $detail_message), \PHP_EOL;
            echo '================================================', \PHP_EOL;
            echo \PHP_EOL;

            echo '================================================', \PHP_EOL;
            echo ' test has been finished.', \PHP_EOL;
            echo '================================================', \PHP_EOL;
        }

        if ($this->stdOutMode === self::STD_OUT_MODE_JSON) {
            echo \json_encode($this->result, \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT);
        }

        return $this;
    }

    /**
     * テスト結果を元に表示を行います。
     *
     * 通常このメソッドは複数のテストをapiで実行した結果を纏めて表示する場合に使います。
     *
     * @param   array   $result テスト結果
     */
    public static function resultRender($result)
    {
        $test_case_root_dir = $result['test_case_root_dir'];

        $time               = $result['time'];
        $start_microtime    = $time['start_microtime'];
        $start_time         = $time['start_datetime'];
        $end_mts            = $time['end_microtime'];
        $end_time           = $time['end_datetime'];
        $exec_time          = $time['exec_time'];

        echo '================================================', \PHP_EOL;
        echo ' fw3_for_old/ez_test.', \PHP_EOL;
        echo \sprintf(' target test cases => %s', $test_case_root_dir), \PHP_EOL;
        echo '================================================', \PHP_EOL;
        echo \sprintf(' start time  : %s', $start_time), \PHP_EOL;

        echo \sprintf(' end time    : %s', $end_time), \PHP_EOL;
        echo \sprintf(' exec time   : %ssec', $exec_time), \PHP_EOL;
        echo '================================================', \PHP_EOL;
        echo \PHP_EOL;

        if (isset($result['std_out']) && $result['std_out'] !== '') {
            echo '================================================', \PHP_EOL;
            echo ' std out.', \PHP_EOL;
            echo '================================================', \PHP_EOL;
            echo $result['std_out'];
            echo '================================================', \PHP_EOL;
            echo \PHP_EOL;
        }

        $parsed_logs    = static::logParse($result['logs']);

        $is_error       = $parsed_logs['is_error'];
        $total          = $parsed_logs['total'];
        $success_total  = $parsed_logs['success_total'];
        $failed_total   = $parsed_logs['failed_total'];
        $error_total    = $parsed_logs['error_total'];
        $detail_message = $parsed_logs['detail_message'];

        echo '================================================', \PHP_EOL;
        echo \sprintf(' test result: %s (%d / %d)', $is_error ? 'failed' : 'success', $success_total, $total), \PHP_EOL;
        echo '------------------------------------------------', \PHP_EOL;
        echo ' details', \PHP_EOL;
        echo '------------------------------------------------', \PHP_EOL;
        echo \implode(\PHP_EOL, $detail_message), \PHP_EOL;
        echo '================================================', \PHP_EOL;
        echo \PHP_EOL;

        echo '================================================', \PHP_EOL;
        echo ' test has been finished.', \PHP_EOL;
        echo '================================================', \PHP_EOL;
    }

    /**
     * 複数のテストをAPIとして呼び出した場合の結果をレンダリング可能な形に変換して返します。
     *
     * @param   array   $multi_api_results  複数のテストをAPIとして呼び出した場合の結果
     * @return  array   static::resultRenderで利用できる形式のデータ
     */
    public static function convertMultiApiResultForResultRender($multi_api_results)
    {
        $test_case_root_dir_list    = array();

        $start_microtime    = array();
        $start_datetime     = array();
        $end_microtime      = array();
        $end_datetime       = array();
        $exec_time          = array();

        $std_out    = array();

        $logs   = array();

        foreach ($multi_api_results as $result) {
            $test_case_root_dir         = $result['test_case_root_dir'];
            $test_case_root_dir_list[]  = $test_case_root_dir;

            $time   = $result['time'];
            $start_microtime[]  = $time['start_microtime'];
            $start_datetime[]   = $time['start_datetime'];
            $end_microtime[]    = $time['end_microtime'];
            $end_datetime[]     = $time['end_datetime'];
            $exec_time[]        = $time['exec_time'];

            if (isset($result['std_out']) && $result['std_out'] !== '') {
                $std_out[]  = $result['std_out'];
            }

            foreach ($result['logs'] as $test_case => $log) {
                $log_total  = 0;

                foreach ($log as $status => $messages) {
                    foreach ($messages as $set) {
                        $logs[$test_case][$status][]    = $set;
                        ++$log_total;
                    }
                }

                if ($log_total === 0) {
                    $logs[$test_case]   = array();
                }
            }
        }

        \sort($start_microtime);
        \sort($start_datetime);
        \sort($end_microtime);
        \sort($end_datetime);

        return array(
            'test_case_root_dir'    => \implode(\sprintf('%s target test cases =>', \PHP_EOL), $test_case_root_dir_list),
            'time'                  => array(
                'start_microtime'   => \reset($start_microtime),
                'start_datetime'    => \reset($start_datetime),
                'end_microtime'     => \end($end_microtime),
                'end_datetime'      => \end($end_datetime),
                'exec_time'         => \array_sum($exec_time),
            ),
            'std_out'               => !empty($std_out) ? \implode(\PHP_EOL, $std_out) : null,
            'logs'                  => $logs,
        );
    }

    /**
     * 終了ステータスを返します。
     *
     * @return  int     終了ステータス
     */
    public function getExitStatus()
    {
        return isset($this->result['log']['is_error']) && $this->result['log']['is_error'] === false ? 0 : 1;
    }

    /**
     * 現在の実行モードがAPIかどうかを返します。
     *
     * @return  bool    現在の実行モードがAPIかどうか
     */
    public function isExecModeApi()
    {
        return $this->execMode === static::EXEC_MODE_API;
    }

    /**
     * 現在の実行モードがプロセスフォークかどうかを返します。
     *
     * @return  bool    現在の実行モードがプロセスフォークかどうか
     */
    public function isExecModeProcessFork()
    {
        return $this->execMode === static::EXEC_MODE_PROCESS_FORK;
    }

    /**
     * テストケースのルートディレクトリを取得・設定します。
     *
     * @param   null|string $case_root_dir  テストケースのルートディレクトリ
     * @return  string|TestRunner  テストケースのルートディレクトリまたはこのインスタンス
     */
    public function testCaseRootDir($case_root_dir = null)
    {
        if ($case_root_dir === null && \func_num_args() === 0) {
            return $this->testCaseRootDir;
        }

        $this->testCaseRootDir  = $case_root_dir;
        return $this;
    }

    /**
     * 実行モードを取得・設定します。
     *
     * @param   string  $exec_mode  実行モード
     * @return  string|TestRunner  実行モードまたはこのインスタンス
     */
    public function execMode($exec_mode = null)
    {
        if ($exec_mode === null && \func_num_args() === 0) {
            return $this->execMode;
        }

        if (!isset(static::$EXEC_MODE_MAP[$exec_mode])) {
            throw new \Exception(\spintf('未知の実行モードを指定されました。mode:%s', static::toText($exec_mode)));
        }

        $this->execMode = $exec_mode;
        return $this;
    }

    /**
     * アサーションに失敗した場合に当該のテストを停止するかどうかを取得・設定します。
     *
     * @param   bool    $stop_with_assertion_failed アサーションに失敗した場合に当該のテストを停止するかどうか
     * @return  string|TestRunner   アサーションに失敗した場合に当該のテストを停止するかどうかまたはこのインスタンス
     */
    public function stopWithAssertionFailed($stop_with_assertion_failed = null)
    {
        if ($exec_mode === null && \func_num_args() === 0) {
            return $this->stopWithAssertionFailed;
        }

        $filtered_stop_with_assertion_failed    = filter_var($stop_with_assertion_failed, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);
        if (!is_bool($filtered_stop_with_assertion_failed)) {
            throw new \Exception(\spintf('解釈できないフラグを指定されました。stop_with_assertion_failed:%s', static::toText($filtered_stop_with_assertion_failed, 2)));
        }

        $this->stopWithAssertionFailed  = $filtered_stop_with_assertion_failed;
        return $this;
    }

    /**
     * PHPバイナリのパスを取得・設定します。
     *
     * @param   string  $php_binary_path    PHPバイナリのパス
     * @return  string|TestRunner  PHPバイナリのパスまたはこのインスタンス
     */
    public function phpBinaryPath($php_binary_path = null)
    {
        if ($php_binary_path === null && \func_num_args() === 0) {
            return $this->phpBinaryPath;
        }

        $this->phpBinaryPath    = $php_binary_path;
        return $this;
    }

    /**
     * php.iniのパスを取得・設定します。
     *
     * @param   string  $php_ini_path   php.iniのパス
     * @return  string|TestRunner  php.iniのパスまたはこのインスタンス
     */
    public function phpIniPath($php_ini_path = null)
    {
        if ($php_ini_path === null && \func_num_args() === 0) {
            return $this->phpIniPath;
        }

        $this->phpIniPath   = $php_ini_path;
        return $this;
    }

    /**
     * 標準出力モードを取得・設定します。
     *
     * @param   string  $php_binary_path    標準出力モード
     * @return  string|TestRunner   標準出力モードまたはこのインスタンス
     */
    public function stdOutMode($std_out_mode = null)
    {
        if ($std_out_mode === null && \func_num_args() === 0) {
            return $this->stdOutMode;
        }

        if (!isset(static::$STD_OUT_MODE_MAP[$std_out_mode])) {
            throw new \Exception(\sprintf('未知の標準出力モードを指定されました。std_out_mode:%s', self::toText($std_out_mode)));
        }

        $this->stdOutMode   = $std_out_mode;
        return $this;
    }

    /**
     * コンテキスト情報を取得・設定します。
     *
     * @param   array   $contexts   コンテキスト情報
     * @return  array|TestRunner    コンテキスト情報またはこのインスタンス
     */
    public function contexts($contexts = null)
    {
        if ($contexts === null && \func_num_args() === 0) {
            return $this->contexts;
        }

        $this->contexts = $contexts;
        return $this;
    }

    /**
     * テストケースをピックアップします。
     *
     * @return  array   テストケースファイルパス
     */
    protected function pickupTestCase()
    {
        $test_case_paths    = array();

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->testCaseRootDir, \FilesystemIterator::CURRENT_AS_FILEINFO),
            \RecursiveIteratorIterator::LEAVES_ONLY
        ) as $fileinfo) {
            if ($fileinfo->isFile() && \substr($fileinfo->getBasename(), -8) === 'Test.php') {
                $test_case_paths[]  = $fileinfo->getPathname();
            }
        }

        return $test_case_paths;
    }

    /**
     * テストを実施します。
     *
     * @param   array   $test_case_paths    テストケースパス
     * @return  array   テスト実行結果
     */
    protected function test($test_case_paths)
    {
        $loaded_classes = \get_declared_classes();

        foreach ($test_case_paths as $test_case_path) {
            require_once $test_case_path;
        }

        $php_binary     = null;
        $php_ini_path   = null;

        $result = array();

        $is_proccess_fork   = $this->isExecModeProcessFork();

        foreach (\array_diff(\get_declared_classes(), $loaded_classes) as $test_class) {
            if ($is_proccess_fork && $this->targetTestClass !== $test_class) {
                continue;
            }

            if (\substr($test_class, -4) !== 'Test') {
                continue;
            }

            if (!is_subclass_of($test_class, "\\fw3_for_old\\ez_test\\test_unit\\TestInterface")) {
                continue;
            }

            if ($test_class === 'fw3_for_old\ez_test\test_unit\AbstractTest') {
                continue;
            }

            /** @var AbstractTest $testClass */
            $testClass  = new $test_class($this->contexts);
            $testClass->initialize();
            $testClass->setupTest();

            /** @var AbstractTest $baseTestClass */
            $baseTestClass  = $testClass;

            $reflectionTestObject    = ReflectionTestObject::factory($testClass);

            $base_use_proccess_fork = !$is_proccess_fork && $reflectionTestObject->useProcessFork();
            $base_use_instance_fork = $reflectionTestObject->useInstanceFork();

            $need_teardown_test_class   = true;

            /** @var ReflectionTestMethod $reflectionTestMethod */
            foreach ($reflectionTestObject as $reflectionTestMethod) {
                if ($is_proccess_fork && $this->targetTestMethod !== $reflectionTestMethod->name) {
                    continue;
                }

                if (!$is_proccess_fork && $reflectionTestMethod->useProcessFork() || $base_use_proccess_fork) {
                    if ($php_binary === null) {
                        if (\version_compare(PHP_VERSION, '5.4.0', '<')) {
                            if (!\is_string($this->phpBinaryPath) || !is_file($this->phpBinaryPath)) {
                                throw new \Exception(\sprintf('PHP5.4.0未満でプロセスフォークを利用するにはphpBinaryPathに有効なPHPバイナリパスを設定してください。phpBinaryPath:%s', static::toText($this->phpBinaryPath)));
                            }
                        }

                        $php_binary = isset($this->phpBinaryPath) ? $this->phpBinaryPath : \constant('PHP_BINARY');
                    }

                    if ($php_ini_path === null) {
                        $php_ini_path   = \is_string($this->phpIniPath) ? $this->phpIniPath : \sprintf('%s/php.ini', \dirname($php_binary));

                        if (\is_readable($php_ini_path)) {
                            $php_ini_path   = \sprintf('--php-ini %s', \escapeshellarg($php_ini_path));
                        } else {
                            $php_ini_path   = '';
                        }
                    }

                    if ($this->targetTestClass === $test_class && $this->targetTestMethod === $reflectionTestMethod->name) {
                        throw new \Exception(sprintf('プロセスフォークモード時のクラス指定とメソッド指定が呼び出し元と同一です。target_class:%s, target_method:%s', $this->targetTestClass, $this->targetTestMethod));
                    }

                    $command    = \sprintf('%s 2>&1', \escapeshellcmd(\sprintf(
                        '%s %s %s --mode %s --class %s --method %s',
                        \escapeshellarg($php_binary),
                        $php_ini_path,
                        \escapeshellarg($_SERVER['argv'][0]),
                        \escapeshellarg(static::EXEC_MODE_PROCESS_FORK),
                        \escapeshellarg($test_class),
                        \escapeshellarg($reflectionTestMethod->name)
                    )));

                    $output     = null;
                    $return_var = null;

                    \exec($command, $output, $return_var);

                    if ($return_var === 0 && isset($output[0]) && null !== ($logs = \json_decode($output[0], true))) {
                        $testClass->mergeLogs($logs['logs'][$test_class]);
                    } else {
                        $testClass->mergeLogs(array('error' => array(
                            \implode(\PHP_EOL, $output),
                        )));
                    }

                    $need_teardown_test_class   = true;

                    continue;
                }

                $use_instance_fork  = $reflectionTestMethod->useInstanceFork() || $base_use_instance_fork;
                if ($use_instance_fork) {
                    $testClass  = new $test_class();
                    $testClass->setupTestClass();
                }

                $testClass->setupTest();

                try {
                    $testClass->{$reflectionTestMethod->name}();
                } catch (\Exception $e) {
                    if ($testClass->hasPreparedException()) {
                        $testClass->assertPreparedException($e);
                    } else {
                        throw $e;
                    }
                }

                $testClass->teardownTest();

                if ($use_instance_fork) {
                    $testClass->teardownTestClass();

                    $baseTestClass->mergeLogs($testClass->getLogs());

                    $testClass  = $baseTestClass;

                    $need_teardown_test_class   = false;
                } else {
                    $need_teardown_test_class   = true;
                }
            }

            if ($need_teardown_test_class) {
                $testClass->teardownTestClass();
            }

            $testClass->finalize();

            $result[$test_class]    = $testClass->getLogs();
        }

        return $result;
    }

    /**
     * テストログを解析します。
     *
     * @param   array   $logs   テストログ
     */
    protected static function logParse($logs)
    {
        $success_total  = 0;
        $failed_total   = 0;
        $error_total    = 0;
        $skip_total     = 0;

        $detail_message = array();

        foreach ($logs as $class => $test_log) {
            $success_count  = !empty($test_log['success']) ? \count($test_log['success']) : 0;
            $failed_count   = !empty($test_log['failed']) ? \count($test_log['failed']) : 0;
            $error_count    = !empty($test_log['error']) ? \count($test_log['error']) : 0;
            $skip_count     = !empty($test_log['skip']) ? \count($test_log['skip']) : 0;

            $total          = $success_count + $failed_count + $error_count + $skip_count;

            $success_total  += $success_count;
            $failed_total   += $failed_count;
            $error_total    += $error_count;
            $skip_total     += $skip_count;

            $is_error   = $failed_count !== 0 || $error_count !== 0;

            $message    = array();

            if ($total === 0) {
                $error_message[]    = \sprintf('  Notice: No assertions test => %s', $class);
                ++$error_total;
                $is_error   = true;
            } else {
                if ($is_error) {
                    $message[]  = '------------------------------------------------';
                }

                $message[]  = \sprintf(
                    '  test %s: %s / %s%s%s => %s',
                    $is_error ? 'failed ' : 'success',
                    $success_count + $skip_count,
                    $total,
                    $skip_count !== 0 ? \sprintf(' (skiped: %d)', $skip_count) : '',
                    $is_error ? \sprintf(' (PHP Error: %d, failed: %d)', $error_count, $failed_count) : '',
                    $class
                );

                if (!empty($test_log['skip'])) {
                    $message[]  = \sprintf('    skip tests => %s', $class);

                    foreach ($test_log['skip'] as $idx => $skip) {
                        $message[]  = \sprintf(
                            '      #%d %s%s', $idx, $skip['backtrace'],
                            isset($skip['message']) ? \sprintf(' reason: %s', \trim($skip['message'])) : ''
                        );
                    }
                }

                $error_message  = array();

                if (!empty($test_log['error'])) {
                    $error_message[]  = '------------------------------------------------';
                    $error_message[]  = \sprintf('    PHP Error details => %s', $class);

                    foreach ($test_log['error'] as $idx => $error) {
                        $error_message[]    = \sprintf('#%d %s', $idx, \trim($error));
                    }
                }

                if (!empty($test_log['failed'])) {
                    if (!empty($error_message)) {
                        $error_message[]    = '';
                        $error_message[]  = '------------------------------------------------';
                    }

                    $error_message[]    = \sprintf('    test failed details => %s', $class);

                    foreach ($test_log['failed'] as $idx => $failed) {
                        $expected   = static::toText($failed['expected'], 999);
                        $actual     = static::toText($failed['actual'], 999);

                        $error_message[]    = '';
                        $error_message[]    = \sprintf('      #%d %s', $idx, $failed['backtrace']);
                        $error_message[]    = \sprintf('        expected: %s', $expected);
                        $error_message[]    = \sprintf('        actual:   %s', $actual);
                    }
                }

                if ($is_error) {
                    $error_message[]  = '------------------------------------------------';
                }
            }

            $detail_message[]   = \implode(\PHP_EOL, array_merge($message, $error_message));
        }

        return array(
            'detail_message'    => $detail_message,
            'total'             => $success_total + $skip_total + $failed_total + $error_total,
            'success_total'     => $success_total,
            'skip_total'        => $skip_total,
            'failed_total'      => $failed_total,
            'error_total'       => $error_total,
            'is_error'          => $is_error,
        );
    }

    /**
     * 変数の型情報付きの文字列表現を返します。
     *
     * @param   mixed   $var    文字列表現化したい変数
     * @param   int     $depth  文字列表現化する階層
     * @return  string  文字列表現化した変数
     */
    public static function toText($var, $depth = 0)
    {
        $type   = \gettype($var);
        switch ($type) {
            case 'boolean':
                return $var ? 'TRUE' : 'FALSE';
            case 'integer':
                return (string) $var;
            case 'double':
                return false === \strpos($var, '.') ? \sprintf('%s.0', $var) : (string) $var;
            case 'string':
                return \sprintf('\'%s\'', $var);
            case 'array':
                if ($depth < 1) {
                    return 'Array';
                }

                --$depth;
                $ret = array();

                foreach ($var as $key => $value) {
                    $ret[] = \sprintf('%s => %s', static::toText($key), static::toText($value, $depth));
                }

                return \sprintf('[%s]', \implode(', ', $ret));
            case 'object':
                \ob_start();
                \var_dump($var);
                $object_status  = \ob_get_clean();

                $object_status  = \substr($object_status, 0, \strpos($object_status, ' ('));
                $object_status  = \sprintf('object(%s)', \substr($object_status, 6));

                if ($depth < 1) {
                    return $object_status;
                }

                --$depth;

                $ro         = new \ReflectionObject($var);

                $tmp_properties = array();
                foreach ($ro->getProperties() as $property) {
                    $state      = $property->isStatic() ? 'static' : 'dynamic';
                    $modifier   = $property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : ($property->isPrivate() ? 'private' : 'unknown modifier'));
                    $tmp_properties[$state][$modifier][]    = $property;
                }

                $properties = array();
                foreach (array('static', 'dynamic') as $state) {
                    $state_text = $state === 'static' ? 'static ' : '';
                    foreach (array('public', 'protected', 'private', 'unknown modifier') as $modifier) {
                        foreach (isset($tmp_properties[$state][$modifier]) ? $tmp_properties[$state][$modifier] : array() as $property) {
                            $property->setAccessible(true);
                            $properties[] = \sprintf('%s%s %s = %s', $state_text, $modifier, static::toText($property->getName()), static::toText($property->getValue($var), $depth));
                        }
                    }
                }

                return \sprintf('%s {%s}', $object_status, \implode(', ', $properties));
            case 'resource':
                return \sprintf('%s %s', \get_resource_type($var), $var);
            case 'resource (closed)':
                return \sprintf('resource (closed) %s', $var);
            case 'NULL':
                return 'NULL';
            case 'unknown type':
            default:
                return 'unknown type';
        }
    }
}
