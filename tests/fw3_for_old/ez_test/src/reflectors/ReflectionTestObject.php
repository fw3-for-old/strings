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

    public function getIterator()
    {
        return new \ArrayIterator($this->testMethodList);
    }
}
