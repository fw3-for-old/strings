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
class ReflectionTestMethod extends \ReflectionMethod
{
    const ANNOTATION_PROCESS_FORK               = '@processFork';
    const ANNOTATION_INSTANCE_FORK              = '@instanceFork';
    const ANNOTATION_TEST                       = '@test';
    const ANNOTATION_EXPECTED_EXCEPTION         = '@expectedException';
    const ANNOTATION_EXPECTED_EXCEPTION_MESSAGE = '@expectedExceptionMessage';
    const ANNOTATION_DATA_PROVIDER              = '@dataProvider';

    protected static $ANNOTATION_MAP   = array(
        self::ANNOTATION_PROCESS_FORK               => self::ANNOTATION_PROCESS_FORK,
        self::ANNOTATION_INSTANCE_FORK              => self::ANNOTATION_INSTANCE_FORK,
        self::ANNOTATION_TEST                       => self::ANNOTATION_TEST,
        self::ANNOTATION_EXPECTED_EXCEPTION         => self::ANNOTATION_EXPECTED_EXCEPTION,
        self::ANNOTATION_EXPECTED_EXCEPTION_MESSAGE => self::ANNOTATION_EXPECTED_EXCEPTION_MESSAGE,
        self::ANNOTATION_DATA_PROVIDER              => self::ANNOTATION_DATA_PROVIDER,
    );

    protected $annotationList   = array();

    protected $reflectionTestClass;

    protected $isTestMethod;

    public static function factory($reflectionTestClass, $method)
    {
        $instance   = new static($reflectionTestClass->getName(), $method);

        $instance->reflectionTestClass  = $reflectionTestClass;

        $instance->annotationList       = \array_merge($reflectionTestClass->getAnnotationList(), $instance->parseDocComment($instance->getDocComment()));

        $instance->isTestMethod         = isset($instance->annotationList[static::ANNOTATION_TEST]) || \strpos($instance->name, 'test') === 0;

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
                $annotation         = \explode(' ', $annotation, 2);
                $annotation_name    = $annotation[0];

                if (!isset(static::$ANNOTATION_MAP[$annotation_name])) {
                    continue;
                }

                $options    = isset($annotation[1]) ? \str_getcsv($annotation[1], ' ', '\'', "\\") : array();

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

    public function isTestMethod()
    {
        return $this->isTestMethod;
    }

    public function useProcessFork()
    {
        return $this->useableByKey(self::ANNOTATION_PROCESS_FORK);
    }

    public function useInstanceFork()
    {
        return $this->useableByKey(self::ANNOTATION_INSTANCE_FORK);
    }

    protected function useableByKey($key)
    {
        if (!isset($this->annotationList[$key])) {
            return false;
        }

        if (isset($this->annotationList[$key]['options'][0])) {
            return $this->annotationList[$key]['options'][0] === true;
        }

        return true;
    }
}
