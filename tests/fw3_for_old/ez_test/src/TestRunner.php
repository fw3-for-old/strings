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

/**
 * ez_test runner
 */
class TestRunner
{
    /**
     * @var string  ケースルートディレクトリ
     */
    protected $caseRootDir  = null;

    /**
     * @var float   インスタンス生成時のタイムスタンプ
     */
    protected $startMicrotime   = 0;

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

        if (isset($options['case_root_dir'])) {
            $instance->caseRootDir($options['case_root_dir']);
        } else {
            $backtrace  = debug_backtrace(false);
            reset($backtrace);
            $backtrace  = current($backtrace);

            $base_dir   = dirname($backtrace['file']);

            $other_than_windows = \substr(\PHP_OS, 0, 3) !== 'WIN';

            $case_root_dir  = $base_dir;

            foreach (array('cases', 'case') as $dir_name) {
                $case_dir   = sprintf('%s/%s', $base_dir, $dir_name);

                if (!file_exists($case_dir)) {
                    continue;
                }

                if (!is_readable($case_dir)) {
                    continue;
                }

                if ($other_than_windows && !is_executable($case_dir)) {
                    continue;
                }

                $case_root_dir  = $case_dir;
                break;
            }

            $instance->caseRootDir($case_root_dir);
        }

        return $instance;
    }

    /**
     * テストを実行します。
     */
    public function run()
    {
        if ($this->caseRootDir === null) {
            throw new \Exception('テストケースのルートディレクトリが指定されていません。');
        }

        $start_time = explode('.', $this->startMicrotime);
        $start_time = sprintf('%s.%s', date('Y/m/d H:i:s', $start_time[0]), isset($start_time[1]) ? $start_time[1] : 0);

        echo '================================================', \PHP_EOL;
        echo ' fw3_for_old/ez_test.', \PHP_EOL;
        echo sprintf(' target test cases => %s', $this->caseRootDir), \PHP_EOL;
        echo '================================================', \PHP_EOL;
        echo sprintf(' start time  : %s', $start_time), \PHP_EOL;

        ob_start();
        $test_case_paths    = $this->pickupTestCase();
        $result             = $this->test($test_case_paths);

        $end_mts    = microtime(true);
        $end_time   = explode('.', $end_mts);
        $end_time   = sprintf('%s.%s', date('Y/m/d H:i:s', $end_time[0]), isset($end_time[1]) ? $end_time[1] : 0);

        $exec_time  = $end_mts - $this->startMicrotime;

        $std_out    = ob_get_clean();

        echo sprintf(' end time    : %s', $end_time), \PHP_EOL;
        echo sprintf(' exec time   : %ssec', $exec_time), \PHP_EOL;
        echo '================================================', \PHP_EOL;
        echo \PHP_EOL;

        if ($std_out !== '') {
            echo '================================================', \PHP_EOL;
            echo ' std out.', \PHP_EOL;
            echo '================================================';
            echo $std_out;
            echo '================================================', \PHP_EOL;
            echo \PHP_EOL;
        }

        $parsed_result  = $this->resultParse($result);
        $is_error       = $parsed_result['is_error'];
        $success_total  = $parsed_result['success_total'];
        $failed_total   = $parsed_result['failed_total'];
        $detail_message = $parsed_result['detail_message'];

        echo '================================================', \PHP_EOL;
        echo \sprintf(' test result: %s (%s / %s)', $is_error ? 'failed' : 'success', $success_total, $success_total + $failed_total), \PHP_EOL;
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
     * テストケースのルートディレクトリを取得・設定します。
     *
     * @param  null|string  $case_root_dir  テストケースのルートディレクトリ
     * @return string|\fw3_for_old\ez_test\TestRunner   テストケースのルートディレクトリまたはこのインスタンス
     */
    public function caseRootDir($case_root_dir = null)
    {
        if ($case_root_dir === null && func_num_args() === 0) {
            return $this->caseRootDir;
        }

        $this->caseRootDir  = $case_root_dir;
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
            new \RecursiveDirectoryIterator($this->caseRootDir, \FilesystemIterator::CURRENT_AS_FILEINFO),
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

        $result = array();

        foreach (\array_diff(\get_declared_classes(), $loaded_classes) as $added_class) {
            if (\substr($added_class, -4) !== 'Test') {
                continue;
            }

            if (!is_subclass_of($added_class, "\\fw3_for_old\\ez_test\\test_unit\\TestInterface")) {
                continue;
            }

            $rc = new \ReflectionClass($added_class);
            if (!$rc->isInstantiable()) {
                continue;
            }

            $test_class = new $added_class();

            try {
                $test_class->test();
                $result[\get_class($test_class)]    = $test_class->getLogs();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $result;
    }

    /**
     * テスト結果を解析します。
     *
     * @param   array   $result テスト結果
     */
    protected function resultParse($result)
    {
        $success_total  = 0;
        $failed_total   = 0;

        $detail_message = array();
        $is_error       = false;

        foreach ($result as $class => $test_result) {
            $success    = count($test_result['success']);
            $failed     = count($test_result['failed']);
            $total      = $success + $failed;

            $success_total  += $success;
            $failed_total   += $failed;

            if ($failed > 0) {
                $is_error = true;
            }

            $message    = array(
                \sprintf('  test class:%s (%s / %s)', $class, $success, $total),
            );

            foreach ($test_result['failed'] as $failed) {
                $expected   = static::toText($failed['expected'], 999);
                $actual     = static::toText($failed['actual'], 999);

                $message[]    = \sprintf('    %s', $failed['backtrace']);
                $message[]    = \sprintf('      expected: %s', $expected);
                $message[]    = \sprintf('      actual:   %s', $actual);
            }

            $detail_message[]   = \implode(\PHP_EOL, $message);
        }

        return array(
            'detail_message'    => $detail_message,
            'success_total'     => $success_total,
            'failed_total'      => $failed_total,
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
                    $modifier   = $property->isPublic() ? 'public' : ($property->isProtected() ? 'protected' : ($property->isPrivate() ? 'private' : 'unkown modifier'));
                    $tmp_properties[$state][$modifier][]    = $property;
                }

                $properties = array();
                foreach (array('static', 'dynamic') as $state) {
                    $state_text = $state === 'static' ? 'static ' : '';
                    foreach (array('public', 'protected', 'private', 'unkown modifier') as $modifier) {
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
