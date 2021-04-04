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

namespace fw3_for_old\ez_test\reflectors;

/**
 * test case reflector
 */
class ReflectionTestObject extends \ReflectionClass implements \IteratorAggregate
{
    const ANNOTATION_PROCESS_FORK               = '@processFork';
    const ANNOTATION_INSTANCE_FORK              = '@instanceFork';

    protected static $ANNOTATION_MAP    = array(
        self::ANNOTATION_PROCESS_FORK   => self::ANNOTATION_PROCESS_FORK,
        self::ANNOTATION_INSTANCE_FORK  => self::ANNOTATION_INSTANCE_FORK,
    );

    protected $annotationList;

    protected $testMethodList;

    public static function factory($objectOrClass)
    {
        $instance   = is_subclass_of($objectOrClass, "\\fw3_for_old\\ez_test\\test_unit\\AbstractTest") ? new static($objectOrClass) : null;

        if ($instance === null) {
            return null;
        }

        $instance->annotationList       = $instance->parseDocComment($instance->getDocComment());

        foreach ($instance->getMethods() as $method) {
            $method = ReflectionTestMethod::factory($instance, $method->name);
            if (!$method->isTestMethod()) {
                continue;
            }

            $instance->testMethodList[$method->name]    = $method;
        }

        return $instance;
    }

    protected function parseDocComment($doc_comment)
    {
        $matcheds   = null;
        $ret        = \preg_match_all("/^ *\*? *((?:@.+)|(?:(?<!@).+))$/mu", $doc_comment, $matcheds);

        if ($ret !== false && $ret === 0) {
            return array();
        }

        $annotation_list    = array();
        $stack              = array();

        foreach ($matcheds[1] as $annotation) {
            if ($annotation === '/**') {
                continue;
            } elseif ($annotation === '*') {
                continue;
            } elseif ($annotation === '/') {
                continue;
            }

            if (\mb_substr($annotation, 0, 1) === '@') {
                $annotation         = explode(' ', $annotation, 2);
                $annotation_name    = $annotation[0];

                if (!isset(static::$ANNOTATION_MAP[$annotation_name])) {
                    continue;
                }

                $options    = isset($annotation[1]) ? str_getcsv($annotation[1], ' ', '\'', "\\") : array();

                $annotation_list[$annotation_name]  = array(
                    'name'      => \mb_substr($annotation_name, 1),
                    'options'   => $options,
                    'input'     => $stack,
                );

                $stack  = array();
            } else {
                $stack[]    = $annotation;
            }
        }

        return $annotation_list;
    }

    public function getAnnotationList()
    {
        return $this->annotationList;
    }

    public function useProcessFork()
    {
        return isset($this->annotationList[self::ANNOTATION_PROCESS_FORK]);
    }

    public function useInstanceFork()
    {
        return isset($this->annotationList[self::ANNOTATION_INSTANCE_FORK]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->testMethodList);
    }
}

//     /**
//      * テストを実施します。
//      *
//      * @param   array   $test_case_paths    テストケースパス
//      * @return  array   テスト実行結果
//      */
//     protected function test($test_case_paths)
//     {
//         foreach (\array_diff(\get_declared_classes(), $loaded_classes) as $added_class) {
//             if (\substr($added_class, -4) !== 'Test') {
//                 continue;
//             }

//             if (!is_subclass_of($added_class, "\\fw3_for_old\\ez_test\\test_unit\\TestInterface")) {
//                 continue;
//             }

//             $rc = new \ReflectionClass($added_class);
//             if (!$rc->isInstantiable()) {
//                 continue;
//             }

//             $rc->getDocComment();

//             $test_class = new $added_class();

//             try {
//                 $test_class->setup();

//                 $init       = $rc->hasMethod('init') ? $rc->getMethod('init') : null;
//                 $cleanUp    = $rc->hasMethod('cleanUp') ? $rc->getMethod('cleanUp') : null;

//                 foreach ($rc->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
//                     $this->parseMethodDocComment($method);

//                     if (\substr($method->name, 0, 4) !== 'test') {
//                         $init === null ?: $init->invoke($test_class);
//                         $method->invoke($test_class);
//                         $cleanUp === null ?: $cleanUp->invoke($test_class);
//                     }
//                 }

//                 $test_class->tearDown();


//                 $result[\get_class($test_class)]    = $test_class->getLogs();
//             } catch (\Exception $e) {
//                 throw $e;
//             }
//         }

//         return $result;
//     }


// foreach (ReflectionTestMethod::$ANNOTATION_MAP as $method_level_annotation) {
//     if (isset($instance->annotationList[$method_level_annotation])) {
//         $method_level_base_annotation[$method_level_annotation] = $instance->annotationList[$method_level_annotation];
//     }
// }
