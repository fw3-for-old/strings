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

use fw3_for_old\ez_test\reflectors\annotations\DataProvider;
use fw3_for_old\ez_test\reflectors\annotations\ExclusionGroup;
use fw3_for_old\ez_test\reflectors\annotations\ExpectedException;
use fw3_for_old\ez_test\reflectors\annotations\ExpectedExceptionMessage;
use fw3_for_old\ez_test\reflectors\annotations\Group;
use fw3_for_old\ez_test\reflectors\annotations\InstanceFork;
use fw3_for_old\ez_test\reflectors\annotations\ProcessFork;
use fw3_for_old\ez_test\reflectors\annotations\StopWithAssertionFailed;
use fw3_for_old\ez_test\reflectors\annotations\Test;

/**
 * test case reflector
 */
class ReflectionTestMethod extends \ReflectionMethod
{
    /**
     * @var array   アノテーションクラスマップ
     */
    protected static $ANNOTATION_CLASS_MAP  = array(
        ProcessFork::ANNOTATION                 => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\ProcessFork",
        InstanceFork::ANNOTATION                => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\InstanceFork",
        StopWithAssertionFailed::ANNOTATION     => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\StopWithAssertionFailed",
        Test::ANNOTATION                        => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\Test",
        ExpectedException::ANNOTATION           => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\ExpectedException",
        ExpectedExceptionMessage::ANNOTATION    => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\ExpectedExceptionMessage",
        DataProvider::ANNOTATION                => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\DataProvider",
        Group::ANNOTATION                       => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\Group",
        ExclusionGroup::ANNOTATION              => "\\fw3_for_old\\ez_test\\reflectors\\annotations\\ExclusionGroup",
    );

    /**
     * @var array   有効なアノテーションリスト
     */
    protected $annotationList   = array();

    /**
     * @var ReflectionTestObject    このテストメソッドが属するテストクラス
     */
    protected $reflectionTestClass;

    /**
     * @var bool    このメソッドがテストメソッドかどうか
     */
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

        $before_annotation  = null;

        foreach ($matcheds[1] as $annotation) {
            if ($annotation === '/**') {
                continue;
            }

            if ($annotation === '*') {
                continue;
            }

            if ($annotation === '/') {
                continue;
            }

            if (\mb_substr($annotation, 0, 1) === '@') {
                if ($before_annotation !== null) {
                    $work_annotation    = \explode(' ', $before_annotation, 2);
                    $annotation_name    = $work_annotation[0];

                    $before_annotation  = null;

                    if (isset(static::$ANNOTATION_CLASS_MAP[$annotation_name])) {
                        $annotation_class_path              = static::$ANNOTATION_CLASS_MAP[$annotation_name];
                        $annotation_list[$annotation_name]  = $annotation_class_path::factory(
                            isset($work_annotation[1]) ? $work_annotation[1] : '',
                            $stack,
                            $this->parameters
                        );

                        $stack              = array();
                    } else {
                        continue;
                    }
                }

                $before_annotation  = $annotation;
            } else {
                $stack[]    = $annotation;
            }
        }

        if ($before_annotation !== null) {
            $work_annotation    = \explode(' ', $before_annotation, 2);
            $annotation_name    = $work_annotation[0];

            if (isset(static::$ANNOTATION_CLASS_MAP[$annotation_name])) {
                $annotation_class_path              = static::$ANNOTATION_CLASS_MAP[$annotation_name];
                $annotation_list[$annotation_name]  = $annotation_class_path::factory(
                    isset($work_annotation[1]) ? $work_annotation[1] : '',
                    $stack,
                    $this->parameters
                );
            }
        }

        return $annotation_list;
    }

    public function isTestMethod()
    {
        return isset($this->annotationList[Test::ANNOTATION]) ? $this->annotationList[Test::ANNOTATION]->useable() : \strpos($this->name, 'test') === 0;;
    }

    public function useProcessFork()
    {
        return isset($this->annotationList[ProcessFork::ANNOTATION]) ? $this->annotationList[ProcessFork::ANNOTATION]->useable() : false;
    }

    public function useInstanceFork()
    {
        return isset($this->annotationList[InstanceFork::ANNOTATION]) ? $this->annotationList[InstanceFork::ANNOTATION]->useable() : false;
    }

    public function useStopWithAssertionFailed()
    {
        return isset($this->annotationList[StopWithAssertionFailed::ANNOTATION]) ? $this->annotationList[StopWithAssertionFailed::ANNOTATION]->useable() : false;
    }

    public function useGroup()
    {
        return isset($this->annotationList[Group::ANNOTATION]) ? $this->annotationList[Group::ANNOTATION]->useable() : false;
    }

    public function useExclusionGroup()
    {
        return isset($this->annotationList[ExclusionGroup::ANNOTATION]) ? $this->annotationList[ExclusionGroup::ANNOTATION]->useable() : false;
    }

    public function canTestByGroup()
    {
        if (!$this->useGroup() && !$this->useExclusionGroup()) {
            return true;
        }

        if (isset($this->annotationList[ExclusionGroup::ANNOTATION])
         && $this->annotationList[ExclusionGroup::ANNOTATION]->useable()
         && !$this->annotationList[ExclusionGroup::ANNOTATION]->executable()) {
            return false;
        }

        return $this->annotationList[Group::ANNOTATION]->executable();
    }
}
