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
    const ANNOTATION_STOP_WITH_ASSERTION_FAILED = '@stopWithAssertionFailed';
    const ANNOTATION_TEST                       = '@test';
    const ANNOTATION_EXPECTED_EXCEPTION         = '@expectedException';
    const ANNOTATION_EXPECTED_EXCEPTION_MESSAGE = '@expectedExceptionMessage';
    const ANNOTATION_DATA_PROVIDER              = '@dataProvider';
    const ANNOTATION_GROUP                      = '@group';
    const ANNOTATION_EXCLUSION_GROUP            = '@exclusionGroup';

    protected static $ANNOTATION_MAP   = array(
        self::ANNOTATION_PROCESS_FORK               => self::ANNOTATION_PROCESS_FORK,
        self::ANNOTATION_INSTANCE_FORK              => self::ANNOTATION_INSTANCE_FORK,
        self::ANNOTATION_STOP_WITH_ASSERTION_FAILED => self::ANNOTATION_STOP_WITH_ASSERTION_FAILED,
        self::ANNOTATION_TEST                       => self::ANNOTATION_TEST,
        self::ANNOTATION_EXPECTED_EXCEPTION         => self::ANNOTATION_EXPECTED_EXCEPTION,
        self::ANNOTATION_EXPECTED_EXCEPTION_MESSAGE => self::ANNOTATION_EXPECTED_EXCEPTION_MESSAGE,
        self::ANNOTATION_DATA_PROVIDER              => self::ANNOTATION_DATA_PROVIDER,
        self::ANNOTATION_GROUP                      => self::ANNOTATION_GROUP,
        self::ANNOTATION_EXCLUSION_GROUP            => self::ANNOTATION_EXCLUSION_GROUP,
    );

    protected $annotationList   = array();

    protected $reflectionTestClass;

    protected $isTestMethod;

    /**
     * @var array   実行時パラメータ
     */
    protected $parameters   = array();

    public static function factory($reflectionTestClass, $method, $parameters = array())
    {
        $instance   = new static($reflectionTestClass->getName(), $method);

        $instance->parameters   = $parameters;

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

    public function annotationStopWithAssertionFailed()
    {
        return $this->useableByKey(self::ANNOTATION_STOP_WITH_ASSERTION_FAILED);
    }

    public function useGroup()
    {
        return $this->useableByKey(self::ANNOTATION_GROUP);
    }

    public function useExclusionGroup()
    {
        return $this->useableByKey(self::ANNOTATION_EXCLUSION_GROUP);
    }

    public function canTestByGroup()
    {
        if (isset($this->parameters['group'])) {
            if ($this->useExclusionGroup()) {
                return $this->parameters['group'] !== $this->annotationList[self::ANNOTATION_EXCLUSION_GROUP]['options'][0];
            }

            if ($this->useGroup()) {
                return $this->parameters['group'] === $this->annotationList[self::ANNOTATION_GROUP]['options'][0];
            }

            return false;
        }

        return true;
    }

    protected function useableByKey($key)
    {
        if (!isset($this->annotationList[$key])) {
            return false;
        }

        if (isset($this->annotationList[$key]['options'][0])) {
            if (is_bool($this->annotationList[$key]['options'][0])) {
                return $this->annotationList[$key]['options'][0] === true;
            }

            if (is_string($this->annotationList[$key]['options'][0])) {
                switch ($key) {
                    case static::ANNOTATION_GROUP:
                    case static::ANNOTATION_EXCLUSION_GROUP:
                        return true;
                }
            }
        }

        return true;
    }
}
